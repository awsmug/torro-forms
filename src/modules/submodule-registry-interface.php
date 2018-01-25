<?php
/**
 * Interface for modules with submodules.
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules;

/**
 * Interface for modules that act as a submodule registry.
 *
 * @since 1.0.0
 */
interface Submodule_Registry_Interface {

	/**
	 * Checks whether a specific submodule is registered.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Submodule slug.
	 * @return bool True if the submodule is registered, false otherwise.
	 */
	public function has( $slug );

	/**
	 * Returns a specific registered submodule.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Submodule slug.
	 * @return Submodule|Error Submodule instance, or error object if submodule is not registered.
	 */
	public function get( $slug );

	/**
	 * Returns all registered submodules.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$slug => $submodule_instance` pairs.
	 */
	public function get_all();

	/**
	 * Registers a new submodule.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug                 Submodule slug.
	 * @param string $submodule_class_name Submodule class name.
	 * @return bool|Error True on success, error object on failure.
	 */
	public function register( $slug, $submodule_class_name );

	/**
	 * Unregisters a new submodule.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Submodule slug.
	 * @return bool|Error True on success, error object on failure.
	 */
	public function unregister( $slug );
}
