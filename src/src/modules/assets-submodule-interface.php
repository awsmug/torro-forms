<?php
/**
 * Interface for submodules that register or enqueue assets.
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules;

use awsmug\Torro_Forms\Assets;

/**
 * Interface for a submodule that registers or enqueues assets.
 *
 * @since 1.0.0
 */
interface Assets_Submodule_Interface {

	/**
	 * Registers all assets the submodule provides.
	 *
	 * @since 1.0.0
	 *
	 * @param Assets $assets The plugin assets instance.
	 */
	public function register_assets( $assets );

	/**
	 * Enqueues scripts and stylesheets on the form editing screen.
	 *
	 * @since 1.0.0
	 *
	 * @param Assets $assets The plugin assets instance.
	 */
	public function enqueue_form_builder_assets( $assets );
}
