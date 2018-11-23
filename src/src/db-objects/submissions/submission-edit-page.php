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
use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Non_Input_Element_Type_Interface;

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
	 *
	 * @param string             $slug               Page slug.
	 * @param Admin_Pages        $manager            Admin page manager instance.
	 * @param Submission_Manager $model_manager      Model manager instance.
	 * @param array              $field_manager_args Optional. Arguments to pass to the field manager used.
	 *                                               Default empty array.
	 */
	public function __construct( $slug, $manager, $model_manager, $field_manager_args = array() ) {
		$this->list_page_slug = $manager->get_prefix() . 'list_submissions';

		parent::__construct( $slug, $manager, $model_manager, $field_manager_args );
	}

	/**
	 * Handles a request to the page.
	 *
	 * @since 1.0.0
	 */
	public function handle_request() {
		global $parent_file, $submenu_file;

		$parent_file  = 'edit.php?post_type=' . $this->manager->get_prefix() . 'form'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
		$submenu_file = $this->manager->get_prefix() . 'list_submissions'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited

		parent::handle_request();
	}

	/**
	 * Renders the edit page header.
	 *
	 * @since 1.0.0
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
	 *
	 * @return array Associative array of `$tab_slug => $tab_args` pairs.
	 */
	protected function get_tabs() {
		$tabs = array(
			'general' => array(
				'title' => _x( 'General', 'submission edit page tab', 'torro-forms' ),
			),
		);

		$primary_property = $this->model_manager->get_primary_property();

		$id = isset( $_REQUEST[ $primary_property ] ) ? absint( $_REQUEST[ $primary_property ] ) : null; // WPCS: CSRF OK.

		if ( $id ) {
			$submission = $this->model_manager->get( $id );

			if ( $submission ) {
				$form = $this->model_manager->get_parent_manager( 'forms' )->get( $submission->form_id );

				if ( $form ) {
					$tabs['form_input'] = array(
						'title'       => _x( 'Form Input', 'submission edit page tab', 'torro-forms' ),
						'description' => __( 'Here you can edit the individual input values the user provided for the submission.', 'torro-forms' ),
					);
				}
			}
		}

		return $tabs;
	}

	/**
	 * Returns the available edit sections.
	 *
	 * @since 1.0.0
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
		);

		$primary_property = $this->model_manager->get_primary_property();

		$id = isset( $_REQUEST[ $primary_property ] ) ? absint( $_REQUEST[ $primary_property ] ) : null; // WPCS: CSRF OK.

		if ( $id ) {
			$submission = $this->model_manager->get( $id );

			if ( $submission ) {
				$form = $this->model_manager->get_parent_manager( 'forms' )->get( $submission->form_id );

				if ( $form ) {
					foreach ( $form->get_containers() as $container ) {
						$sections[ 'container_' . $container->id ] = array(
							'tab'   => 'form_input',
							'title' => $container->label,
						);
					}
				}
			}
		}

		return $sections;
	}

	/**
	 * Returns the available edit fields.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	protected function get_fields() {
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
		);

		$primary_property = $this->model_manager->get_primary_property();

		$id = isset( $_REQUEST[ $primary_property ] ) ? absint( $_REQUEST[ $primary_property ] ) : null; // WPCS: CSRF OK.

		if ( $id ) {
			$submission = $this->model_manager->get( $id );

			if ( $submission ) {
				$form = $this->model_manager->get_parent_manager( 'forms' )->get( $submission->form_id );

				if ( $form ) {
					foreach ( $form->get_containers() as $container ) {
						foreach ( $container->get_elements() as $element ) {
							$element_type = $element->get_element_type();

							if ( $element_type ) {
								if ( is_a( $element_type, Non_Input_Element_Type_Interface::class ) ) {
									continue;
								}

								$element_fields = $element_type->get_edit_submission_fields_args( $element );

								foreach ( $element_fields as $slug => $args ) {
									$element_fields[ $slug ]['section'] = 'container_' . $container->id;
								}

								$fields = array_merge( $fields, $element_fields );
							}
						}
					}
				}
			}
		}

		return $fields;
	}
}
