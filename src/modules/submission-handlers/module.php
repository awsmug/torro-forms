<?php
/**
 * Submission Handlers module class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Submission_Handlers;

use awsmug\Torro_Forms\Modules\Module as Module_Base;
use awsmug\Torro_Forms\Assets;

/**
 * Class for the Submission Handlers module.
 *
 * @since 1.0.0
 */
class Module extends Module_Base {

	/**
	 * Bootstraps the module by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function bootstrap() {
		$this->slug        = 'submission_handlers';
		$this->title       = __( 'Submission Handlers', 'torro-forms' );
		$this->description = __( 'Submission handlers handle form submissions, for example by providing stats and charts.', 'torro-forms' );
	}

	/**
	 * Returns the available settings sub-tabs for the module.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$subtab_slug => $subtab_args` pairs.
	 */
	protected function get_settings_subtabs() {
		return array();
	}

	/**
	 * Returns the available settings sections for the module.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$section_slug => $section_args` pairs.
	 */
	protected function get_settings_sections() {
		return array();
	}

	/**
	 * Returns the available settings fields for the module.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	protected function get_settings_fields() {
		return array();
	}

	/**
	 * Registers the available module scripts and stylesheets.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Assets $assets Assets API instance.
	 */
	protected function register_assets( $assets ) {
		// Empty method body.
	}
}
