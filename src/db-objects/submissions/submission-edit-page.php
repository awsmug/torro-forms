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
	 * Renders a status select field.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int|null           $id         Current submission ID, or null if new submission.
	 * @param Submission         $submission Current submission object.
	 * @param Submission_Manager $manager    Submission manager instance.
	 */
	public function status_select( $id, $submission, $manager ) {
		$current_status = $submission->status;

		$timestamp = ! empty( $submission->timestamp ) ? $submission->timestamp : current_time( 'timestamp', 'mysql' );

		?>
		<div class="misc-pub-section">
			<div id="date-information">
				<?php _e( 'Date:', 'torro-forms' ); ?>
				<?php echo date_i18n( get_option( 'date_format' ), $timestamp ); ?>
			</div>
		</div>
		<div class="misc-pub-section">
			<div id="post-status-select">
				<label for="post-status"><?php echo $manager->get_message( 'edit_page_status_label' ); ?></label>
				<select id="post-status" name="status">
					<option value="completed"<?php selected( $current_status, 'completed' ); ?>><?php _ex( 'Completed', 'submission status label', 'torro-forms' ); ?></option>
					<option value="progressing"<?php selected( $current_status, 'progressing' ); ?>><?php _ex( 'In Progress', 'submission status label', 'torro-forms' ); ?></option>
				</select>
			</div>
		</div>
		<?php
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
			'form_input' => array(
				'title' => _x( 'Form Input', 'submission edit page tab', 'torro-forms' ),
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
				'section'     => 'identification_data',
				'type'        => 'text',
				'label'       => __( 'IP Address', 'torro-forms' ),
				'description' => __( 'Specify the IP address where this submission should be sent from.', 'torro-forms' ),
				'pattern'     => '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}',
			),
			'cookie_key'  => array(
				'section'     => 'identification_data',
				'type'        => 'text',
				'label'       => __( 'Cookie Key', 'torro-forms' ),
				'description' => __( 'Specify the cookie key identifying the submission creator.', 'torro-forms' ),
			),
		);

		return $fields;
	}
}
