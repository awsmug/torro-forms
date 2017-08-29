<?php
/**
 * Submission edit page class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Submissions;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Edit_Page;
use Leaves_And_Love\Plugin_Lib\Components\Admin_Pages;

/**
 * Class representing the submission edit page in the admin.
 *
 * @since 1.0.0
 */
class Submission_Edit_Page extends Model_Edit_Page {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string             $slug          Page slug.
	 * @param Admin_Pages        $manager       Admin page manager instance.
	 * @param Submission_Manager $model_manager Model manager instance.
	 */
	public function __construct( $slug, $manager, $model_manager ) {
		$this->list_page_slug = $manager->get_prefix() . 'edit_submissions';

		parent::__construct( $slug, $manager, $model_manager );
	}

	/**
	 * Handles a request to the page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function handle_request() {
		global $parent_file, $submenu_file;

		$parent_file = 'edit.php?post_type=' . $this->manager->get_prefix() . 'form';
		$submenu_file = $this->manager->get_prefix() . 'list_submissions';

		parent::handle_request();
	}

	/**
	 * Renders the edit page header.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render_header() {
		$primary_property = $this->model_manager->get_primary_property();
		if ( ! empty( $this->model->$primary_property ) ) {
			/* translators: %s: submission ID prefixed with a # */
			$this->title = sprintf( __( 'Edit Submission %s', 'torro-forms' ), '#' . $this->model->$primary_property );
		}

		parent::render_header();
	}

	/**
	 * Validates custom model data that is not handled by the field manager.
	 *
	 * This method is called from within the 'edit' action.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array    $form_data Form POST data.
	 * @param WP_Error $error     Error object to add errors to.
	 */
	protected function validate_custom_data( $form_data, $error ) {
		parent::validate_custom_data( $form_data, $error );

		if ( isset( $form_data['status'] ) && $form_data['status'] !== $this->model->status ) {
			if ( ! in_array( $form_data['status'], array( 'completed', 'progressing' ), true ) ) {
				$error->add( 'action_edit_item_invalid_status', $this->model_manager->get_message( 'action_edit_item_invalid_status' ) );
			} else {
				$this->model->status = $form_data['status'];
			}
		}
	}

	/**
	 * Adds tabs, sections and fields to the submission edit page.
	 *
	 * This method should call the methods `add_tabs()`, `add_section()` and
	 * `add_field()` to populate the page.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function add_page_content() {
		$tabs = $this->get_tabs();

		foreach ( $tabs as $slug => $args ) {
			$this->add_tab( $slug, $args );
		}

		$sections = $this->get_sections();

		foreach ( $sections as $slug => $args ) {
			$this->add_section( $slug, $args );
		}

		$fields = $this->get_fields();

		foreach ( $fields as $slug => $args ) {
			$type = 'text';
			if ( isset( $args['type'] ) ) {
				$type = $args['type'];
				unset( $args['type'] );
			}

			$this->add_field( $slug, $type, $args );
		}
	}

	/**
	 * Returns the available edit tabs.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$tab_slug => $tab_args` pairs.
	 */
	protected function get_tabs() {
		$tabs = array(
			'general'    => array(
				'title' => _x( 'General', 'submission edit page tab', 'torro-forms' ),
			),
			'advanced'   => array(
				'title' => _x( 'Advanced', 'submission edit page tab', 'torro-forms' ),
				'description' => __( 'Here you can edit the individual input values the user provided for the submission. <strong>Please be careful: You should only be tweaking these if you know exactly what you are doing!</strong>', 'torro-forms' ),
			),
		);

		return $tabs;
	}

	/**
	 * Returns the available edit sections.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$section_slug => $section_args` pairs.
	 */
	protected function get_sections() {
		$sections = array(
			'associated_data'     => array(
				'tab'   => 'general',
				'title' => _x( 'Associated Data', 'submission edit page section', 'torro-forms' ),
			),
			'identification_data' => array(
				'tab'   => 'general',
				'title' => _x( 'Identification Data', 'submission edit page section', 'torro-forms' ),
			),
			'form_input'          => array(
				'tab'   => 'advanced',
				'title' => _x( 'Form Input', 'submission edit page section', 'torro-forms' ),
			),
		);

		return $sections;
	}

	/**
	 * Returns the available edit fields.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	protected function get_fields() {
		$form = null;
		if ( ! empty( $_REQUEST['id'] ) ) {
			$submission = $this->model_manager->get( (int) $_REQUEST['id'] );
			if ( $submission && ! empty( $submission->form_id ) ) {
				$form = $this->model_manager->get_parent_manager( 'forms' )->get( $submission->form_id );
			}
		}

		if ( $form ) {
			$elements = $this->model_manager->get_parent_manager( 'forms' )->get_child_manager( 'containers' )->get_child_manager( 'elements' )->query( array(
				'number'  => -1,
				'form_id' => $form->id,
			) );
			if ( count( $elements ) > 8 ) {
				$element_id_field = array(
					'type'          => 'autocomplete',
					'label'         => __( 'Element', 'torro-forms' ),
					'description'   => __( 'Specify the form element this value applies to.', 'torro-forms' ),
					'input_classes' => array( 'regular-text' ),
					'autocomplete'  => array(
						'rest_placeholder_search_route' => 'torro/v1/elements?form_id=' . $form->id . '&search=%search%',
						'rest_placeholder_label_route'  => 'torro/v1/elements/%value%',
						'value_generator'               => '%id%',
						'label_generator'               => '%label%',
					),
				);
			} else {
				$element_choices = array( '0' => _x( 'None', 'element choices', 'torro-forms' ) );
				foreach ( $elements as $element ) {
					$element_choices[ $element->id ] = $element->label;
				}

				$element_id_field = array(
					'type'         => 'select',
					'label'        => __( 'Element', 'torro-forms' ),
					'description'  => __( 'Specify the form element this value applies to.', 'torro-forms' ),
					'choices'      => $element_choices,
				);
			}
		} else {
			$element_id_field = array(
				'type'        => 'number',
				'label'       => __( 'Element ID', 'torro-forms' ),
				'description' => __( 'Specify the internal form element ID this value applies to.', 'torro-forms' ),
				'min'         => 1,
				'step'        => 1,
			);
		}

		$fields = array(
			'form_id'     => array(
				'section'      => 'associated_data',
				'type'         => 'autocomplete',
				'label'        => __( 'Form', 'torro-forms' ),
				'description'  => __( 'Specify the form this should be a submission for.', 'torro-forms' ),
				'autocomplete' => array(
					'rest_placeholder_search_route' => 'torro/v1/forms?search=%search%',
					'rest_placeholder_label_route'  => 'torro/v1/forms/%value%',
					'value_generator'               => '%id%',
					'label_generator'               => '%title%',
				),
				'required'     => true,
			),
			'user_id'     => array(
				'section'      => 'associated_data',
				'type'         => 'autocomplete',
				'label'        => __( 'User', 'torro-forms' ),
				'description'  => __( 'Specify the user who should be associated with this submission.', 'torro-forms' ),
				'autocomplete' => array(
					'rest_placeholder_search_route' => 'wp/v2/users?search=%search%',
					'rest_placeholder_label_route'  => 'wp/v2/users/%value%',
					'value_generator'               => '%id%',
					'label_generator'               => '%name%',
				),
			),
			'remote_addr' => array(
				'section'       => 'identification_data',
				'type'          => 'text',
				'label'         => __( 'IP Address', 'torro-forms' ),
				'description'   => __( 'Specify the IP address where this submission should be sent from.', 'torro-forms' ),
				'input_classes' => array( 'regular-text' ),
				'pattern'       => '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}',
			),
			'user_key'    => array(
				'section'       => 'identification_data',
				'type'          => 'text',
				'label'         => __( 'Key', 'torro-forms' ),
				'description'   => __( 'Specify the key identifying the submission creator.', 'torro-forms' ),
				'input_classes' => array( 'regular-text' ),
			),
			'values'      => array(
				'section'       => 'form_input',
				'type'          => 'group',
				'label'         => __( 'Submission Values', 'torro-forms' ),
				'repeatable'    => true,
				'fields'        => array(
					'id'         => array(
						'type'        => 'number',
						'label'       => __( 'Value ID', 'torro-forms' ),
						'description' => __( 'Specify the internal ID of this value.', 'torro-forms' ),
						'min'         => 1,
						'step'        => 1,
					),
					'element_id' => $element_id_field,
					'field'      => array(
						'type'          => 'text',
						'label'         => __( 'Field', 'torro-forms' ),
						'description'   => __( 'Specify the form element field identifier this value applies to. Must be empty for non multi-field elements.', 'torro-forms' ),
						'input_classes' => array( 'regular-text' ),
					),
					'value'      => array(
						'type'          => 'text',
						'label'         => __( 'Value', 'torro-forms' ),
						'description'   => __( 'Enter or modify the actual value given for the element and field.', 'torro-forms' ),
						'input_classes' => array( 'regular-text' ),
					),
				),
			),
		);

		return $fields;
	}
}
