<?php
/**
 * Trait for modules with submodules.
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules;

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
	 * @access protected
	 * @var array
	 */
	protected $submodules = array();

	/**
	 * Default submodules definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $default_submodules = array();

	/**
	 * Name of the base class that each submodule in this registry must inherit.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $submodule_base_class = Submodule::class;

	/**
	 * Checks whether a specific submodule is registered.
	 *
	 * @since 1.0.0
	 * @access public
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
	 * @access public
	 *
	 * @param string $slug Submodule slug.
	 * @return Submodule|Error Submodule instance, or error object if submodule is not registered.
	 */
	public function get( $slug ) {
		if ( ! $this->has( $slug ) ) {
			return new Error( $this->get_prefix() . 'submodule_not_exist', sprintf( __( 'An submodule with the slug %s does not exist.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		return $this->submodules[ $slug ];
	}

	/**
	 * Returns all registered submodules.
	 *
	 * @since 1.0.0
	 * @access public
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
	 * @access public
	 *
	 * @param string $slug                 Submodule slug.
	 * @param string $submodule_class_name Submodule class name.
	 * @return bool|Error True on success, error object on failure.
	 */
	public function register( $slug, $submodule_class_name ) {
		if ( ! did_submodule( 'init' ) ) {
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
	 * @access public
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
}
