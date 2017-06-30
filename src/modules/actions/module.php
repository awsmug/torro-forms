<?php
/**
 * Actions module class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Actions;

use awsmug\Torro_Forms\Modules\Module as Module_Base;
use awsmug\Torro_Forms\Assets;
use awsmug\Torro_Forms\Error;

/**
 * Class for the Actions module.
 *
 * @since 1.0.0
 */
class Module extends Module_Base {

	/**
	 * Registered actions.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $actions = array();

	/**
	 * Default actions definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $default_actions = array();

	/**
	 * Bootstraps the module by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function bootstrap() {
		$this->slug        = 'actions';
		$this->title       = __( 'Actions', 'torro-forms' );
		$this->description = __( 'Actions are executed in the moment users submit their form data.', 'torro-forms' );
	}

	/**
	 * Checks whether a specific action is registered.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Action slug.
	 * @return bool True if the action is registered, false otherwise.
	 */
	public function has( $slug ) {
		return isset( $this->actions[ $slug ] );
	}

	/**
	 * Returns a specific registered action.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Action slug.
	 * @return Action|Error Action instance, or error object if action is not registered.
	 */
	public function get( $slug ) {
		if ( ! $this->has( $slug ) ) {
			return new Error( $this->get_prefix() . 'action_not_exist', sprintf( __( 'An action with the slug %s does not exist.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		return $this->actions[ $slug ];
	}

	/**
	 * Returns all registered actions.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Associative array of `$slug => $action_instance` pairs.
	 */
	public function get_all() {
		return $this->actions;
	}

	/**
	 * Registers a new action.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug                    Action slug.
	 * @param string $action_class_name Action class name.
	 * @return bool|Error True on success, error object on failure.
	 */
	public function register( $slug, $action_class_name ) {
		if ( ! did_action( 'init' ) ) {
			/* translators: 1: action slug, 2: init hookname */
			return new Error( $this->get_prefix() . 'action_too_early', sprintf( __( 'The action %1$s cannot be registered before the %2$s hook.', 'torro-forms' ), $slug, '<code>init</code>' ), __METHOD__, '1.0.0' );
		}

		if ( $this->has( $slug ) ) {
			/* translators: %s: action slug */
			return new Error( $this->get_prefix() . 'action_already_exist', sprintf( __( 'An action with the slug %s already exists.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		if ( ! class_exists( $action_class_name ) ) {
			/* translators: %s: action class name */
			return new Error( $this->get_prefix() . 'action_class_not_exist', sprintf( __( 'The class %s does not exist.', 'torro-forms' ), $action_class_name ), __METHOD__, '1.0.0' );
		}

		if ( ! is_subclass_of( $action_class_name, Action::class ) ) {
			/* translators: %s: action class name */
			return new Error( $this->get_prefix() . 'action_class_not_allowed', sprintf( __( 'The class %s is not allowed for a action.', 'torro-forms' ), $action_class_name ), __METHOD__, '1.0.0' );
		}

		$this->actions[ $slug ] = new $action_class_name( $this );

		return true;
	}

	/**
	 * Unregisters a new action.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Action slug.
	 * @return bool|Error True on success, error object on failure.
	 */
	public function unregister( $slug ) {
		if ( ! $this->has( $slug ) ) {
			/* translators: %s: action slug */
			return new Error( $this->get_prefix() . 'action_not_exist', sprintf( __( 'An action with the slug %s does not exist.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		if ( isset( $this->default_actions[ $slug ] ) ) {
			/* translators: %s: action slug */
			return new Error( $this->get_prefix() . 'action_is_default', sprintf( __( 'The default action %s cannot be unregistered.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		unset( $this->actions[ $slug ] );

		return true;
	}

	/**
	 * Registers the default actions.
	 *
	 * The function also executes a hook that should be used by other developers to register their own actions.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_defaults() {
		foreach ( $this->default_actions as $slug => $action_class_name ) {
			$this->register( $slug, $action_class_name );
		}

		/**
		 * Fires when the default actions have been registered.
		 *
		 * This action should be used to register custom actions.
		 *
		 * @since 1.0.0
		 *
		 * @param Module $actions Action manager instance.
		 */
		do_action( "{$this->get_prefix()}register_actions", $this );
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
