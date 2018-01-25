<?php
/**
 * Interface for submodules with settings.
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules;

/**
 * Interface for a submodule that supports settings.
 *
 * @since 1.0.0
 */
interface Settings_Submodule_Interface {

	/**
	 * Retrieves the value of a specific submodule option.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option  Name of the option to retrieve.
	 * @param mixed  $default Optional. Value to return if the option doesn't exist. Default false.
	 * @return mixed Value set for the option.
	 */
	public function get_option( $option, $default = false );

	/**
	 * Retrieves the values for all submodule options.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$key => $value` pairs for every option that is set.
	 */
	public function get_options();

	/**
	 * Returns the settings identifier for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return string Submodule settings identifier.
	 */
	public function get_settings_identifier();

	/**
	 * Returns the settings subtab title for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return string Submodule settings title.
	 */
	public function get_settings_title();

	/**
	 * Returns the available settings sections for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$section_slug => $section_args` pairs.
	 */
	public function get_settings_sections();

	/**
	 * Returns the available settings fields for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_settings_fields();
}
