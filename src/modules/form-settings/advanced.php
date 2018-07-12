<?php
/**
 * Advanced form setting class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Form_Settings;

/**
 * Class for advanced form settings.
 *
 * @since 1.1.0
 */
class Advanced extends Form_Setting {

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.1.0
	 */
	protected function bootstrap() {
		$this->slug        = 'advanced';
		$this->title       = __( 'Advanced', 'torro-forms' );
		$this->description = __( 'Advanced form settings.', 'torro-forms' );
	}

	/**
	 * Returns the available meta box fields for the module.
	 *
	 * @since 1.1.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$meta_fields = $this->_get_meta_fields();

		$prefix = $this->module->get_prefix();

		$meta_fields['allow_get_params'] = array(
			'tab'         => 'advanced',
			'type'        => 'checkbox',
			'label'       => __( 'Allow GET parameters?', 'torro-forms' ),
			/* translators: %s: GET parameter example */
			'description' => sprintf( __( 'Click the checkbox to allow initial field values to be set through GET parameters (such as %s).', 'torro-forms' ), '<code>?torro_input_value_ELEMENT_ID=VALUE</code>' ),
		);

		/**
		 * Filters the meta fields in the form settings metabox.
		 *
		 * @since 1.1.0
		 *
		 * @param array $fields Array of `$field_slug => $field_data` pairs.
		 */
		return apply_filters( "{$prefix}form_settings_advanced_meta_fields", $meta_fields );
	}

	/**
	 * Filters whether to allow GET parameters to pre-populate form element values.
	 *
	 * @since 1.1.0
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
	 * @since 1.1.0
	 */
	protected function setup_hooks() {
		parent::setup_hooks();

		$prefix = $this->module->get_prefix();

		$this->filters[] = array(
			'name'     => "{$prefix}allow_get_params",
			'callback' => array( $this, 'filter_allow_get_params' ),
			'priority' => 10,
			'num_args' => 3,
		);
	}
}
