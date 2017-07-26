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
use awsmug\Torro_Forms\Modules\Meta_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Assets_Submodule_Interface;
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
	 * Returns the available meta box tabs for the module.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$tab_slug => $tab_args` pairs.
	 */
	protected function get_meta_tabs() {
		$tabs = array();

		foreach ( $this->submodules as $slug => $form_setting ) {
			if ( ! is_a( $form_setting, Meta_Submodule_Interface::class ) ) {
				continue;
			}

			$form_setting_meta_identifier = $form_setting->get_meta_identifier();
			$form_setting_meta_fields = $form_setting->get_meta_fields();
			if ( empty( $form_setting_meta_fields ) ) {
				continue;
			}

			$tabs[ $form_setting_meta_identifier ] = array(
				'title'       => $form_setting->get_meta_title(),
				'description' => $form_setting->get_meta_description(),
			);
		}

		return $tabs;
	}

	/**
	 * Returns the available meta box fields for the module.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	protected function get_meta_fields() {
		$fields = array();

		foreach ( $this->submodules as $slug => $form_setting ) {
			if ( ! is_a( $form_setting, Meta_Submodule_Interface::class ) ) {
				continue;
			}

			$form_setting_meta_identifier = $form_setting->get_meta_identifier();

			$form_setting_meta_fields = $form_setting->get_meta_fields();
			foreach ( $form_setting_meta_fields as $field_slug => $field_data ) {
				$field_slug        = $form_setting_meta_identifier . '__' . $field_slug;
				$field_data['tab'] = $form_setting_meta_identifier;

				if ( isset( $field_data['dependencies'] ) ) {
					for ( $i = 0; $i < count( $field_data['dependencies'] ); $i++ ) {
						if ( isset( $field_data['dependencies'][ $i ]['fields'] ) ) {
							for ( $j = 0; $j < count( $field_data['dependencies'][ $i ]['fields'] ); $j++ ) {
								$field_data['dependencies'][ $i ]['fields'][ $j ] = $form_setting_meta_identifier . '__' . $field_data['dependencies'][ $i ]['fields'][ $j ];
							}
						}
					}
				}

				$fields[ $field_slug ] = $field_data;
			}
		}

		return $fields;
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
		foreach ( $this->submodules as $slug => $form_setting ) {
			if ( ! is_a( $form_setting, Assets_Submodule_Interface::class ) ) {
				continue;
			}

			$form_setting->register_assets( $assets );
		}
	}

	/**
	 * Enqueues the module's form builder scripts and stylesheets.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Assets $assets Assets API instance.
	 */
	protected function enqueue_form_builder_assets( $assets ) {
		foreach ( $this->submodules as $slug => $form_setting ) {
			if ( ! is_a( $form_setting, Assets_Submodule_Interface::class ) ) {
				continue;
			}

			$form_setting->enqueue_form_builder_assets( $assets );
		}
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
