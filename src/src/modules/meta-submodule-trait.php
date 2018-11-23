<?php
/**
 * Trait for submodules with meta.
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules;

/**
 * Trait for a submodule that supports meta.
 *
 * @since 1.0.0
 */
trait Meta_Submodule_Trait {

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
	public function get_form_option( $form_id, $option, $default = false ) {
		return $this->module->get_form_option( $form_id, $this->get_meta_identifier() . '__' . $option, $default );
	}

	/**
	 * Retrieves the values for all submodule form options.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id Form ID.
	 * @return array Associative array of `$key => $value` pairs for every form option that is set.
	 */
	public function get_form_options( $form_id ) {
		$metadata = $this->module->get_form_options( $form_id );

		$prefix = $this->get_meta_identifier() . '__';

		$options = array();
		foreach ( $metadata as $key => $value ) {
			if ( 0 !== strpos( $key, $prefix ) ) {
				continue;
			}

			$key             = substr( $key, strlen( $prefix ) );
			$options[ $key ] = $value;
		}

		return $options;
	}

	/**
	 * Returns the meta identifier for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return string Submodule meta identifier.
	 */
	public function get_meta_identifier() {
		return $this->slug;
	}

	/**
	 * Returns the meta subtab title for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return string Submodule meta title.
	 */
	public function get_meta_title() {
		return $this->title;
	}

	/**
	 * Returns the meta subtab description for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return string Submodule meta description.
	 */
	public function get_meta_description() {
		return $this->description;
	}

	/**
	 * Returns the available meta fields for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		return array();
	}
}
