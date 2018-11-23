<?php
/**
 * Trait for submodules with settings.
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules;

/**
 * Trait for a submodule that supports settings.
 *
 * @since 1.0.0
 */
trait Settings_Submodule_Trait {

	/**
	 * Retrieves the value of a specific submodule option.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option  Name of the option to retrieve.
	 * @param mixed  $default Optional. Value to return if the option doesn't exist. Default false.
	 * @return mixed Value set for the option.
	 */
	public function get_option( $option, $default = false ) {
		return $this->module->get_option( $this->get_settings_identifier() . '__' . $option, $default );
	}

	/**
	 * Retrieves the values for all submodule options.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$key => $value` pairs for every option that is set.
	 */
	public function get_options() {
		$settings = $this->module->get_options();

		$prefix = $this->get_settings_identifier() . '__';

		$options = array();
		foreach ( $settings as $key => $value ) {
			if ( 0 !== strpos( $key, $prefix ) ) {
				continue;
			}

			$key             = substr( $key, strlen( $prefix ) );
			$options[ $key ] = $value;
		}

		return $options;
	}

	/**
	 * Returns the settings identifier for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return string Submodule settings identifier.
	 */
	public function get_settings_identifier() {
		return $this->slug;
	}

	/**
	 * Returns the settings subtab title for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return string Submodule settings title.
	 */
	public function get_settings_title() {
		return $this->title;
	}

	/**
	 * Returns the available settings sections for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$section_slug => $section_args` pairs.
	 */
	public function get_settings_sections() {
		return array();
	}

	/**
	 * Returns the available settings fields for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_settings_fields() {
		return array();
	}
}
