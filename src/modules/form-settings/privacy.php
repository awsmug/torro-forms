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
 * @since 1.0.0
 */
class Privacy extends Setting {

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug        = 'privacy';
		$this->title       = __( 'Privacy', 'torro-forms' );
		$this->description = __( 'Form privacy settings.', 'torro-forms' );
	}

	/**
	 * Returns the available meta box fields for the module.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$meta_fields = $this->_get_meta_fields();

		$prefix = $this->module->get_prefix();

		$meta_fields['double_opt_in'] = array(
			'tab'            => 'privacy',
			'type'           => 'checkbox',
			'label'          => __( 'Enable', 'torro-forms' ),
			'visual_label'   => __( 'Double Opt-In', 'torro-forms' ),
			'description'    => sprintf( __( 'Click to activate the double opt-in. After activation a double opt-in template variable {double-opt-in-link} will be available for email notifications and submissions will have an "checked" or "unchecked" status.', 'torro-forms' ), '<code>?torro_input_value_ELEMENT_ID=VALUE</code>' ),
		);

		/**
		 * Filters the meta fields in the form settings metabox.
		 *
		 * @since 1.0.0
		 *
		 * @param array $fields Array of `$field_slug => $field_data` pairs.
		 */
		return apply_filters( "{$prefix}form_settings_privacy_meta_fields", $meta_fields );
	}
}
