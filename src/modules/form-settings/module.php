<?php
/**
 * Form Settings module class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Form_Settings;

use awsmug\Torro_Forms\Modules\Module as Module_Base;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Interface;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Trait;
use awsmug\Torro_Forms\Assets;

/**
 * Class for the Form Settings module.
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
		$this->slug        = 'form_settings';
		$this->title       = __( 'Form Settings', 'torro-forms' );
		$this->description = __( 'Form settings control the general behavior of forms.', 'torro-forms' );

		$this->submodule_base_class = Form_Setting::class;
		//TODO: Setup $default_submodules.
	}

	/**
	 * Registers the default form settings.
	 *
	 * The function also executes a hook that should be used by other developers to register their own form settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_defaults() {
		foreach ( $this->default_submodules as $slug => $form_setting_class_name ) {
			$this->register( $slug, $form_setting_class_name );
		}

		/**
		 * Fires when the default form settings have been registered.
		 *
		 * This action should be used to register custom form settings.
		 *
		 * @since 1.0.0
		 *
		 * @param Module $form_settings Form setting manager instance.
		 */
		do_action( "{$this->get_prefix()}register_form_settings", $this );
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

		foreach ( $this->submodules as $slug => $form_setting ) {
			if ( ! is_a( $form_setting, Settings_Submodule_Interface::class ) ) {
				continue;
			}

			$form_setting_settings_identifier = $form_setting->get_settings_identifier();
			$form_setting_settings_sections = $form_setting->get_settings_sections();
			if ( empty( $form_setting_settings_sections ) ) {
				continue;
			}

			$subtabs[ $form_setting_settings_identifier ] = array(
				'title' => $form_setting->get_settings_title(),
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

		foreach ( $this->submodules as $slug => $form_setting ) {
			if ( ! is_a( $form_setting, Settings_Submodule_Interface::class ) ) {
				continue;
			}

			$form_setting_settings_identifier = $form_setting->get_settings_identifier();

			$form_setting_settings_sections = $form_setting->get_settings_sections();
			foreach ( $form_setting_settings_sections as $section_slug => $section_data ) {
				$section_data['subtab'] = $form_setting_settings_identifier;

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

		foreach ( $this->submodules as $slug => $form_setting ) {
			if ( ! is_a( $form_setting, Settings_Submodule_Interface::class ) ) {
				continue;
			}

			$fields = array_merge( $fields, $form_setting->get_settings_fields() );
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
