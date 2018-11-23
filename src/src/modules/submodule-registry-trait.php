<?php
/**
 * Trait for modules with submodules.
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules;

use awsmug\Torro_Forms\Assets;
use awsmug\Torro_Forms\Error;

/**
 * Trait for modules that act as a submodule registry.
 *
 * @since 1.0.0
 */
trait Submodule_Registry_Trait {

	/**
	 * Registered submodules.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $submodules = array();

	/**
	 * Default submodules definition.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $default_submodules = array();

	/**
	 * Name of the base class that each submodule in this registry must inherit.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $submodule_base_class = Submodule::class;

	/**
	 * Checks whether a specific submodule is registered.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Submodule slug.
	 * @return bool True if the submodule is registered, false otherwise.
	 */
	public function has( $slug ) {
		return isset( $this->submodules[ $slug ] );
	}

	/**
	 * Returns a specific registered submodule.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Submodule slug.
	 * @return Submodule|Error Submodule instance, or error object if submodule is not registered.
	 */
	public function get( $slug ) {
		if ( ! $this->has( $slug ) ) {
			/* translators: %s: submodule slug */
			return new Error( $this->get_prefix() . 'submodule_not_exist', sprintf( __( 'An submodule with the slug %s does not exist.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		return $this->submodules[ $slug ];
	}

	/**
	 * Returns all registered submodules.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$slug => $submodule_instance` pairs.
	 */
	public function get_all() {
		return $this->submodules;
	}

	/**
	 * Registers a new submodule.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug                 Submodule slug.
	 * @param string $submodule_class_name Submodule class name.
	 * @return bool|Error True on success, error object on failure.
	 */
	public function register( $slug, $submodule_class_name ) {
		if ( ! did_action( 'init' ) ) {
			/* translators: 1: submodule slug, 2: init hookname */
			return new Error( $this->get_prefix() . 'submodule_too_early', sprintf( __( 'The submodule %1$s cannot be registered before the %2$s hook.', 'torro-forms' ), $slug, '<code>init</code>' ), __METHOD__, '1.0.0' );
		}

		if ( $this->has( $slug ) ) {
			/* translators: %s: submodule slug */
			return new Error( $this->get_prefix() . 'submodule_already_exist', sprintf( __( 'An submodule with the slug %s already exists.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		if ( ! class_exists( $submodule_class_name ) ) {
			/* translators: %s: submodule class name */
			return new Error( $this->get_prefix() . 'submodule_class_not_exist', sprintf( __( 'The class %s does not exist.', 'torro-forms' ), $submodule_class_name ), __METHOD__, '1.0.0' );
		}

		if ( ! is_subclass_of( $submodule_class_name, $this->submodule_base_class ) ) {
			/* translators: %s: submodule class name */
			return new Error( $this->get_prefix() . 'submodule_class_not_allowed', sprintf( __( 'The class %s is not allowed for a submodule.', 'torro-forms' ), $submodule_class_name ), __METHOD__, '1.0.0' );
		}

		$this->submodules[ $slug ] = new $submodule_class_name( $this );

		return true;
	}

	/**
	 * Unregisters a new submodule.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Submodule slug.
	 * @return bool|Error True on success, error object on failure.
	 */
	public function unregister( $slug ) {
		if ( ! $this->has( $slug ) ) {
			/* translators: %s: submodule slug */
			return new Error( $this->get_prefix() . 'submodule_not_exist', sprintf( __( 'An submodule with the slug %s does not exist.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		if ( isset( $this->default_submodules[ $slug ] ) ) {
			/* translators: %s: submodule slug */
			return new Error( $this->get_prefix() . 'submodule_is_default', sprintf( __( 'The default submodule %s cannot be unregistered.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		unset( $this->submodules[ $slug ] );

		return true;
	}

	/**
	 * Returns the available meta box tabs for the module.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$tab_slug => $tab_args` pairs.
	 */
	protected function get_meta_tabs() {
		$tabs = array();

		foreach ( $this->submodules as $slug => $submodule ) {
			if ( ! is_a( $submodule, Meta_Submodule_Interface::class ) ) {
				continue;
			}

			$submodule_meta_identifier = $submodule->get_meta_identifier();
			$submodule_meta_fields     = $submodule->get_meta_fields();
			if ( empty( $submodule_meta_fields ) ) {
				continue;
			}

			$tabs[ $submodule_meta_identifier ] = array(
				'title'       => $submodule->get_meta_title(),
				'description' => $submodule->get_meta_description(),
			);
		}

		return $tabs;
	}

	/**
	 * Returns the available meta box fields for the module.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	protected function get_meta_fields() {
		$fields = array();

		foreach ( $this->submodules as $slug => $submodule ) {
			if ( ! is_a( $submodule, Meta_Submodule_Interface::class ) ) {
				continue;
			}

			$submodule_meta_identifier = $submodule->get_meta_identifier();

			$submodule_meta_fields = $submodule->get_meta_fields();
			foreach ( $submodule_meta_fields as $field_slug => $field_data ) {
				$field_slug        = $submodule_meta_identifier . '__' . $field_slug;
				$field_data['tab'] = $submodule_meta_identifier;

				if ( isset( $field_data['dependencies'] ) ) {
					$dependency_count = count( $field_data['dependencies'] );

					for ( $i = 0; $i < $dependency_count; $i++ ) {
						if ( isset( $field_data['dependencies'][ $i ]['fields'] ) ) {
							$field_count = count( $field_data['dependencies'][ $i ]['fields'] );

							for ( $j = 0; $j < $field_count; $j++ ) {
								$field_data['dependencies'][ $i ]['fields'][ $j ] = $submodule_meta_identifier . '__' . $field_data['dependencies'][ $i ]['fields'][ $j ];
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
	 *
	 * @return array Associative array of `$subtab_slug => $subtab_args` pairs.
	 */
	protected function get_settings_subtabs() {
		$subtabs = array();

		foreach ( $this->submodules as $slug => $submodule ) {
			if ( ! is_a( $submodule, Settings_Submodule_Interface::class ) ) {
				continue;
			}

			$submodule_settings_identifier = $submodule->get_settings_identifier();
			$submodule_settings_sections   = $submodule->get_settings_sections();
			if ( empty( $submodule_settings_sections ) ) {
				continue;
			}

			$subtabs[ $submodule_settings_identifier ] = array(
				'title' => $submodule->get_settings_title(),
			);
		}

		return $subtabs;
	}

	/**
	 * Returns the available settings sections for the module.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$section_slug => $section_args` pairs.
	 */
	protected function get_settings_sections() {
		$sections = array();

		foreach ( $this->submodules as $slug => $submodule ) {
			if ( ! is_a( $submodule, Settings_Submodule_Interface::class ) ) {
				continue;
			}

			$submodule_settings_identifier = $submodule->get_settings_identifier();

			$submodule_settings_sections = $submodule->get_settings_sections();
			foreach ( $submodule_settings_sections as $section_slug => $section_data ) {
				$section_slug           = $submodule_settings_identifier . '__' . $section_slug;
				$section_data['subtab'] = $submodule_settings_identifier;

				$sections[ $section_slug ] = $section_data;
			}
		}

		return $sections;
	}

	/**
	 * Returns the available settings fields for the module.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	protected function get_settings_fields() {
		$fields = array();

		foreach ( $this->submodules as $slug => $submodule ) {
			if ( ! is_a( $submodule, Settings_Submodule_Interface::class ) ) {
				continue;
			}

			$submodule_settings_identifier = $submodule->get_settings_identifier();

			$submodule_settings_fields = $submodule->get_settings_fields();
			foreach ( $submodule_settings_fields as $field_slug => $field_data ) {
				$field_slug = $submodule_settings_identifier . '__' . $field_slug;

				if ( ! empty( $field_data['section'] ) ) {
					$field_data['section'] = $submodule_settings_identifier . '__' . $field_data['section'];
				}

				if ( isset( $field_data['dependencies'] ) ) {
					$dependency_count = count( $field_data['dependencies'] );

					for ( $i = 0; $i < $dependency_count; $i++ ) {
						if ( isset( $field_data['dependencies'][ $i ]['fields'] ) ) {
							$field_count = count( $field_data['dependencies'][ $i ]['fields'] );

							for ( $j = 0; $j < $field_count; $j++ ) {
								$field_data['dependencies'][ $i ]['fields'][ $j ] = $submodule_settings_identifier . '__' . $field_data['dependencies'][ $i ]['fields'][ $j ];
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
	 * Registers the available module scripts and stylesheets.
	 *
	 * @since 1.0.0
	 *
	 * @param Assets $assets Assets API instance.
	 */
	protected function register_assets( $assets ) {
		foreach ( $this->submodules as $slug => $submodule ) {
			if ( ! is_a( $submodule, Assets_Submodule_Interface::class ) ) {
				continue;
			}

			$submodule->register_assets( $assets );
		}
	}

	/**
	 * Enqueues the module's form builder scripts and stylesheets.
	 *
	 * @since 1.0.0
	 *
	 * @param Assets $assets Assets API instance.
	 */
	protected function enqueue_form_builder_assets( $assets ) {
		foreach ( $this->submodules as $slug => $submodule ) {
			if ( ! is_a( $submodule, Assets_Submodule_Interface::class ) ) {
				continue;
			}

			$submodule->enqueue_form_builder_assets( $assets );
		}
	}
}
