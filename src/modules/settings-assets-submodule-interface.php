<?php
/**
 * Interface for submodules that enqueue settings assets.
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules;

use awsmug\Torro_Forms\Assets;

/**
 * Interface for a submodule that enqueues settings assets.
 *
 * @since 1.0.0
 */
interface Settings_Assets_Submodule_Interface {

	/**
	 * Enqueues scripts and stylesheets on the settings screen.
	 *
	 * @since 1.0.0
	 *
	 * @param Assets $assets            Assets API instance.
	 * @param string $current_tab_id    Identifier of the current tab.
	 * @param string $current_subtab_id Identifier of the current sub-tab.
	 */
	public function enqueue_settings_assets( $assets, $current_tab_id, $current_subtab_id );
}
