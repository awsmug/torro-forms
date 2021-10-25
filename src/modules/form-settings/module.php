<?php
/**
 * Form Settings module class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Form_Settings;

use awsmug\Torro_Forms\Modules\Module as Module_Base;
use awsmug\Torro_Forms\Assets;

/**
 * Class for the Form Settings module.
 *
 * @since 1.0.0
 */
class Module extends Module_Base {

	/**
	 * Bootstraps the module by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug        = 'form_settings';
		$this->title       = __( 'Form Settings', 'torro-forms' );
		$this->description = __( 'Form settings control the general behavior of forms.', 'torro-forms' );
	}

	/**
	 * Returns the available meta box tabs for the module.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$tab_slug => $tab_args` pairs.
	 */
	protected function get_meta_tabs() {
		$prefix = $this->manager()->get_prefix();

		$tabs = array(
			'labels'   => array(
				'title' => _x( 'Labels', 'form settings tab', 'torro-forms' ),
			),
			'advanced' => array(
				'title' => _x( 'Advanced', 'form settings tab', 'torro-forms' ),
			),
		);

		/**
		 * Filters the meta tabs in the form settings metabox.
		 *
		 * @since 1.0.0
		 *
		 * @param array $tabs Array of `$tab_slug => $tab_data` pairs.
		 */
		return apply_filters( "{$prefix}form_settings_meta_tabs", $tabs );
	}

	/**
	 * Returns the available meta box fields for the module.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	protected function get_meta_fields() {
		$prefix = $this->manager()->get_prefix();

		$fields = array(
			'show_container_title'  => array(
				'tab'          => 'labels',
				'type'         => 'checkbox',
				'label'        => __( 'Show page title?', 'torro-forms' ),
				'description'  => __( 'Click the checkbox to display the title of the current page in the frontend.', 'torro-forms' ),
				'default'      => true,
				'wrap_classes' => array( 'has-torro-tooltip-description' ),
				'visual_label' => __( 'Page Title', 'torro-forms' ),
			),
			'required_fields_text'  => array(
				'tab'          => 'labels',
				'type'         => 'text',
				'label'        => __( 'Required fields text', 'torro-forms' ),
				'description'  => __( 'This text appears on top of a form and shows the user which fields are required. The value has to contain a %s to output the correct indicator.', 'torro-forms' ), //phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
				'default'      => $this->get_default_required_fields_text(),
				'wrap_classes' => array( 'has-torro-tooltip-description' ),
			),
			'previous_button_label' => array(
				'tab'          => 'labels',
				'type'         => 'text',
				'label'        => __( 'Previous Button Label', 'torro-forms' ),
				'description'  => __( 'Enter the label for the button that leads to the previous form page.', 'torro-forms' ),
				'default'      => $this->get_default_previous_button_label(),
				'wrap_classes' => array( 'has-torro-tooltip-description' ),
			),
			'next_button_label'     => array(
				'tab'          => 'labels',
				'type'         => 'text',
				'label'        => __( 'Next Button Label', 'torro-forms' ),
				'description'  => __( 'Enter the label for the button that leads to the next form page.', 'torro-forms' ),
				'default'      => $this->get_default_next_button_label(),
				'wrap_classes' => array( 'has-torro-tooltip-description' ),
			),
			'submit_button_label'   => array(
				'tab'          => 'labels',
				'type'         => 'text',
				'label'        => __( 'Submit Button Label', 'torro-forms' ),
				'description'  => __( 'Enter the label for the button that submits the form.', 'torro-forms' ),
				'default'      => $this->get_default_submit_button_label(),
				'wrap_classes' => array( 'has-torro-tooltip-description' ),
			),
			'success_message'       => array(
				'tab'           => 'labels',
				'type'          => 'text',
				'label'         => __( 'Success Message', 'torro-forms' ),
				'description'   => __( 'Enter a message to display when a form submission has successfully been completed.', 'torro-forms' ),
				'input_classes' => array( 'regular-text' ),
				'default'       => $this->get_default_success_message(),
				'wrap_classes'  => array( 'has-torro-tooltip-description' ),
			),
			'allow_get_params'      => array(
				'tab'         => 'advanced',
				'type'        => 'checkbox',
				'label'       => __( 'Allow GET parameters?', 'torro-forms' ),
				/* translators: %s: GET parameter example */
				'description' => sprintf( __( 'Click the checkbox to allow initial field values to be set through GET parameters (such as %s).', 'torro-forms' ), '<code>?torro_input_value_ELEMENT_ID=VALUE</code>' ),
			),
		);

		/**
		 * Filters the meta fields in the form settings metabox.
		 *
		 * @since 1.0.0
		 *
		 * @param array $fields Array of `$field_slug => $field_data` pairs.
		 */
		return apply_filters( "{$prefix}form_settings_meta_fields", $fields );
	}

	/**
	 * Returns the available settings sub-tabs for the module.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$subtab_slug => $subtab_args` pairs.
	 */
	protected function get_settings_subtabs() {
		$prefix = $this->manager()->get_prefix();

		$subtabs = array();

		/**
		 * Filters the settings subtabs in the form settings tab.
		 *
		 * @since 1.0.0
		 *
		 * @param array $fields Array of `$subtab_slug => $subtab_data` pairs.
		 */
		return apply_filters( "{$prefix}form_settings_settings_subtabs", $subtabs );
	}

	/**
	 * Returns the available settings sections for the module.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$section_slug => $section_args` pairs.
	 */
	protected function get_settings_sections() {
		$prefix = $this->manager()->get_prefix();

		$sections = array();

		/**
		 * Filters the settings sections in the form settings tab.
		 *
		 * @since 1.0.0
		 *
		 * @param array $fields Array of `$section_slug => $section_data` pairs.
		 */
		return apply_filters( "{$prefix}form_settings_settings_sections", $sections );
	}

	/**
	 * Returns the available settings fields for the module.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	protected function get_settings_fields() {
		$prefix = $this->manager()->get_prefix();

		$fields = array();

		/**
		 * Filters the settings fields in the form settings tab.
		 *
		 * @since 1.0.0
		 *
		 * @param array $fields Array of `$field_slug => $field_data` pairs.
		 */
		return apply_filters( "{$prefix}form_settings_settings_fields", $fields );
	}

	/**
	 * Registers the available module scripts and stylesheets.
	 *
	 * @since 1.0.0
	 *
	 * @param Assets $assets Assets API instance.
	 */
	protected function register_assets( $assets ) {
		$prefix = $this->manager()->get_prefix();

		/**
		 * Fires when form settings assets should be registered.
		 *
		 * @since 1.0.0
		 *
		 * @param Assets $assets The plugin assets instance.
		 */
		do_action( "{$prefix}form_settings_register_assets", $assets );
	}

	/**
	 * Enqueues the module's form builder scripts and stylesheets.
	 *
	 * @since 1.0.0
	 *
	 * @param Assets $assets Assets API instance.
	 */
	protected function enqueue_form_builder_assets( $assets ) {
		$prefix = $this->manager()->get_prefix();

		/**
		 * Fires when form settings assets for the form builder should be enqueued.
		 *
		 * @since 1.0.0
		 *
		 * @param Assets $assets The plugin assets instance.
		 */
		do_action( "{$prefix}form_settings_enqueue_form_builder_assets", $assets );
	}

	/**
	 * Returns the default text for required fields hint
	 *
	 * @since 1.0.4
	 *
	 * @return string Message to display.
	 */
	protected function get_default_required_fields_text() {
		return _x( 'Required fields are marked %s.', 'required fields text on top of form', 'torro-forms' ); //phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
	}

	/**
	 * Returns the default label for the Previous button.
	 *
	 * @since 1.0.0
	 *
	 * @return string Message to display.
	 */
	protected function get_default_previous_button_label() {
		return _x( 'Previous Step', 'button label', 'torro-forms' );
	}

	/**
	 * Returns the default label for the Next button.
	 *
	 * @since 1.0.0
	 *
	 * @return string Message to display.
	 */
	protected function get_default_next_button_label() {
		return _x( 'Next Step', 'button label', 'torro-forms' );
	}

	/**
	 * Returns the default label for the Submit button.
	 *
	 * @since 1.0.0
	 *
	 * @return string Message to display.
	 */
	protected function get_default_submit_button_label() {
		return _x( 'Submit', 'button label', 'torro-forms' );
	}

	/**
	 * Returns the default message to display when a form submission has been completed.
	 *
	 * @since 1.0.0
	 *
	 * @return string Message to display.
	 */
	protected function get_default_success_message() {
		return __( 'Thank you for submitting!', 'torro-forms' );
	}

	/**
	 * Filters whether to show the title of the current container in the frontend.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $show_container_title Whether to show the title.
	 * @param int  $form_id              Form ID.
	 * @return bool True or false depending on the form setting.
	 */
	protected function filter_show_container_title( $show_container_title, $form_id ) {
		return (bool) $this->get_form_option( $form_id, 'show_container_title', true );
	}

	/**
	 * Filters the label for the required fields text in the frontend.
	 *
	 * @since 1.0.4
	 *
	 * @param string $required_fields_text The required fields text.
	 * @param int    $form_id           Form ID.
	 * @return string The required fields text depending on the form setting.
	 */
	protected function filter_required_fields_text( $required_fields_text, $form_id ) {
		$required_fields_text = $this->get_form_option( $form_id, 'required_fields_text', '' );
		if ( empty( $required_fields_text ) ) {
			$required_fields_text = $this->get_default_required_fields_text();
		}

		$required_fields_text = '<span aria-hidden="true">' . sprintf( $required_fields_text, '<span class="torro-required-indicator">*</span>' ) . '</span>';

		return $required_fields_text;
	}

	/**
	 * Filters the label for the Previous button in the frontend.
	 *
	 * @since 1.0.0
	 *
	 * @param string $prev_button_label The Previous button label.
	 * @param int    $form_id           Form ID.
	 * @return string The Previous button label depending on the form setting.
	 */
	protected function filter_previous_button_label( $prev_button_label, $form_id ) {
		$prev_button_label = $this->get_form_option( $form_id, 'previous_button_label', '' );
		if ( empty( $prev_button_label ) ) {
			$prev_button_label = $this->get_default_previous_button_label();
		}

		return $prev_button_label;
	}

	/**
	 * Filters the label for the Next button in the frontend.
	 *
	 * @since 1.0.0
	 *
	 * @param string $next_button_label The Next button label.
	 * @param int    $form_id           Form ID.
	 * @return string The Next button label depending on the form setting.
	 */
	protected function filter_next_button_label( $next_button_label, $form_id ) {
		$next_button_label = $this->get_form_option( $form_id, 'next_button_label', '' );
		if ( empty( $next_button_label ) ) {
			$next_button_label = $this->get_default_next_button_label();
		}

		return $next_button_label;
	}

	/**
	 * Filters the label for the Submit button in the frontend.
	 *
	 * @since 1.0.0
	 *
	 * @param string $submit_button_label The Submit button label.
	 * @param int    $form_id             Form ID.
	 * @return string The Submit button label depending on the form setting.
	 */
	protected function filter_submit_button_label( $submit_button_label, $form_id ) {
		$submit_button_label = $this->get_form_option( $form_id, 'submit_button_label', '' );
		if ( empty( $submit_button_label ) ) {
			$submit_button_label = $this->get_default_submit_button_label();
		}

		return $submit_button_label;
	}

	/**
	 * Filters the success message for a completed form submission in the frontend.
	 *
	 * @since 1.0.0
	 *
	 * @param string $success_message The success message.
	 * @param int    $form_id         Form ID.
	 * @return string The success message depending on the form setting.
	 */
	protected function filter_success_message( $success_message, $form_id ) {
		$success_message = $this->get_form_option( $form_id, 'success_message', '' );
		if ( empty( $success_message ) ) {
			$success_message = $this->get_default_success_message();
		}

		return $success_message;
	}

	/**
	 * Filters whether to allow GET parameters to pre-populate form element values.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $allow_get_params Whether to allow GET parameters.
	 * @param int  $element_id       Element ID.
	 * @param int  $form_id          Form ID.
	 * @return bool True or false depending on the form setting.
	 */
	protected function filter_allow_get_params( $allow_get_params, $element_id, $form_id ) {
		return (bool) $this->get_form_option( $form_id, 'allow_get_params' );
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * @since 1.0.0
	 */
	protected function setup_hooks() {
		parent::setup_hooks();

		$prefix = $this->get_prefix();

		$this->filters[] = array(
			'name'     => "{$prefix}form_container_show_title",
			'callback' => array( $this, 'filter_show_container_title' ),
			'priority' => 10,
			'num_args' => 2,
		);
		$this->filters[] = array(
			'name'     => "{$prefix}required_indicator_description",
			'callback' => array( $this, 'filter_required_fields_text' ),
			'priority' => 10,
			'num_args' => 2,
		);
		$this->filters[] = array(
			'name'     => "{$prefix}form_button_prev_step_label",
			'callback' => array( $this, 'filter_previous_button_label' ),
			'priority' => 10,
			'num_args' => 2,
		);
		$this->filters[] = array(
			'name'     => "{$prefix}form_button_next_step_label",
			'callback' => array( $this, 'filter_next_button_label' ),
			'priority' => 10,
			'num_args' => 2,
		);
		$this->filters[] = array(
			'name'     => "{$prefix}form_button_submit_label",
			'callback' => array( $this, 'filter_submit_button_label' ),
			'priority' => 10,
			'num_args' => 2,
		);
		$this->filters[] = array(
			'name'     => "{$prefix}form_submission_success_message",
			'callback' => array( $this, 'filter_success_message' ),
			'priority' => 10,
			'num_args' => 2,
		);
		$this->filters[] = array(
			'name'     => "{$prefix}allow_get_params",
			'callback' => array( $this, 'filter_allow_get_params' ),
			'priority' => 10,
			'num_args' => 3,
		);
	}
}
