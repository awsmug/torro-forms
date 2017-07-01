<?php
/**
 * Access Controls module class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Access_Controls;

use awsmug\Torro_Forms\Modules\Module as Module_Base;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Interface;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Trait;
use awsmug\Torro_Forms\Assets;

/**
 * Class for the Access Controls module.
 *
 * @since 1.0.0
 */
class Module extends Module_Base implements Submodule_Registry_Interface {
	use Submodule_Registry_Trait;

	/**
	 * Bootstraps the module by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function bootstrap() {
		$this->slug        = 'access_controls';
		$this->title       = __( 'Access Controls', 'torro-forms' );
		$this->description = __( 'Access controls allow to limit who has permissions to view and submit a form.', 'torro-forms' );

		$this->submodule_base_class = Access_Control::class;
		//TODO: Setup $default_submodules.
	}

	/**
	 * Registers the default access controls.
	 *
	 * The function also executes a hook that should be used by other developers to register their own access controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_defaults() {
		foreach ( $this->default_submodules as $slug => $access_control_class_name ) {
			$this->register( $slug, $access_control_class_name );
		}

		/**
		 * Fires when the default access controls have been registered.
		 *
		 * This action should be used to register custom access controls.
		 *
		 * @since 1.0.0
		 *
		 * @param Module $access_controls Form setting manager instance.
		 */
		do_action( "{$this->get_prefix()}register_access_controls", $this );
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
		$subtabs = array();

		foreach ( $this->submodules as $slug => $access_control ) {
			if ( ! is_a( $access_control, Settings_Submodule_Interface::class ) ) {
				continue;
			}

			$access_control_settings_identifier = $access_control->get_settings_identifier();
			$access_control_settings_sections = $access_control->get_settings_sections();
			if ( empty( $access_control_settings_sections ) ) {
				continue;
			}

			$subtabs[ $access_control_settings_identifier ] = array(
				'title' => $access_control->get_settings_title(),
			);
		}

		return $subtabs;
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
		$sections = array();

		foreach ( $this->submodules as $slug => $access_control ) {
			if ( ! is_a( $access_control, Settings_Submodule_Interface::class ) ) {
				continue;
			}

			$access_control_settings_identifier = $access_control->get_settings_identifier();

			$access_control_settings_sections = $access_control->get_settings_sections();
			foreach ( $access_control_settings_sections as $section_slug => $section_data ) {
				$section_data['subtab'] = $access_control_settings_identifier;

				$sections[ $section_slug ] = $section_data;
			}
		}

		return $sections;
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
		$fields = array();

		foreach ( $this->submodules as $slug => $access_control ) {
			if ( ! is_a( $access_control, Settings_Submodule_Interface::class ) ) {
				continue;
			}

			$fields = array_merge( $fields, $access_control->get_settings_fields() );
		}

		return $fields;
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

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function setup_hooks() {
		parent::setup_hooks();

		$this->actions[] = array(
			'name'     => 'init',
			'callback' => array( $this, 'register_defaults' ),
			'priority' => 100,
			'num_args' => 1,
		);
	}
}
