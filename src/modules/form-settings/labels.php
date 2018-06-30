<?php
/**
 * Link Count protector class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Form_Settings;

/**
 * Class for a protector using a link count.
 *
 * @since 1.1.0
 */
class Labels extends Setting {

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.1.0
	 */
	protected function bootstrap() {
		$this->slug        = 'labels';
		$this->title       = __( 'Labels', 'torro-forms' );
		$this->description = __( 'Change text appearing in form frontend.', 'torro-forms' );
	}

	/**
	 * Returns the available meta box fields for the module.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$prefix = $this->module->get_prefix();

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
			)
		);

		/**
		 * Filters the meta fields in the form settings metabox.
		 *
		 * @since 1.1.0
		 *
		 * @param array $fields Array of `$field_slug => $field_data` pairs.
		 */
		return apply_filters( "{$prefix}form_settings_labels_meta_fields", $fields );
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
	 * Sets up all action and filter hooks for the service.
	 *
	 * @since 1.0.0
	 */
	protected function setup_hooks() {
		parent::setup_hooks();

		$prefix = $this->module->get_prefix();

		$this->filters[] = array(
			'name'     => "{$prefix}form_container_show_title",
			'callback' => array( $this, 'filter_show_container_title' ),
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
	}
}
