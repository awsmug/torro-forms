<?php
/**
 * Interface for submodules with hooks.
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules;

/**
 * Interface for a submodule that supports hooks.
 *
 * @since 1.1.0
 */
interface Hooks_Submodule_Interface {

	/**
	 * Adds the submodule hooks.
	 *
	 * @since 1.1.0
	 */
	public function add_hooks();

	/**
	 * Removes the submodule hooks.
	 *
	 * @since 1.1.0
	 */
	public function remove_hooks();
}
