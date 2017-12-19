<?php
/**
 * Interface for submodules with meta.
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules;

/**
 * Interface for a submodule that supports meta.
 *
 * @since 1.0.0
 */
interface Meta_Submodule_Interface {

	/**
	 * Retrieves the value of a specific submodule form option.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $form_id Form ID.
	 * @param string $option  Name of the form option to retrieve.
	 * @param mixed  $default Optional. Value to return if the form option doesn't exist. Default false.
	 * @return mixed Value set for the form option.
	 */
	public function get_form_option( $form_id, $option, $default = false );

	/**
	 * Retrieves the values for all submodule form options.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id Form ID.
	 * @return array Associative array of `$key => $value` pairs for every form option that is set.
	 */
	public function get_form_options( $form_id );

	/**
	 * Returns the meta identifier for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return string Submodule meta identifier.
	 */
	public function get_meta_identifier();

	/**
	 * Returns the meta subtab title for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return string Submodule meta title.
	 */
	public function get_meta_title();

	/**
	 * Returns the meta subtab description for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return string Submodule meta description.
	 */
	public function get_meta_description();

	/**
	 * Returns the available meta fields for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields();
}
