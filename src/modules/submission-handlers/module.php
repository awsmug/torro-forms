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
	 * Registered submission handlers.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $submission_handlers = array();

	/**
	 * Default submission handlers definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $default_submission_handlers = array();

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
	 * Checks whether a specific submission handler is registered.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Submission handler slug.
	 * @return bool True if the submission handler is registered, false otherwise.
	 */
	public function has( $slug ) {
		return isset( $this->submission_handlers[ $slug ] );
	}

	/**
	 * Returns a specific registered submission handler.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Submission handler slug.
	 * @return Submission_Handler|Error Submission handler instance, or error object if submission handler is not registered.
	 */
	public function get( $slug ) {
		if ( ! $this->has( $slug ) ) {
			return new Error( $this->get_prefix() . 'submission_handler_not_exist', sprintf( __( 'A submission handler with the slug %s does not exist.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		return $this->submission_handlers[ $slug ];
	}

	/**
	 * Returns all registered submission handlers.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Associative array of `$slug => $submission_handler_instance` pairs.
	 */
	public function get_all() {
		return $this->submission_handlers;
	}

	/**
	 * Registers a new submission handler.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug                    Submission handler slug.
	 * @param string $submission_handler_class_name Submission handler class name.
	 * @return bool|Error True on success, error object on failure.
	 */
	public function register( $slug, $submission_handler_class_name ) {
		if ( ! did_action( 'init' ) ) {
			/* translators: 1: submission handler slug, 2: init hookname */
			return new Error( $this->get_prefix() . 'submission_handler_too_early', sprintf( __( 'The submission handler %1$s cannot be registered before the %2$s hook.', 'torro-forms' ), $slug, '<code>init</code>' ), __METHOD__, '1.0.0' );
		}

		if ( $this->has( $slug ) ) {
			/* translators: %s: submission handler slug */
			return new Error( $this->get_prefix() . 'submission_handler_already_exist', sprintf( __( 'A submission handler with the slug %s already exists.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		if ( ! class_exists( $submission_handler_class_name ) ) {
			/* translators: %s: submission handler class name */
			return new Error( $this->get_prefix() . 'submission_handler_class_not_exist', sprintf( __( 'The class %s does not exist.', 'torro-forms' ), $submission_handler_class_name ), __METHOD__, '1.0.0' );
		}

		if ( ! is_subclass_of( $submission_handler_class_name, Submission_Handler::class ) ) {
			/* translators: %s: submission handler class name */
			return new Error( $this->get_prefix() . 'submission_handler_class_not_allowed', sprintf( __( 'The class %s is not allowed for a submission handler.', 'torro-forms' ), $submission_handler_class_name ), __METHOD__, '1.0.0' );
		}

		$this->submission_handlers[ $slug ] = new $submission_handler_class_name( $this );

		return true;
	}

	/**
	 * Unregisters a new submission handler.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Submission handler slug.
	 * @return bool|Error True on success, error object on failure.
	 */
	public function unregister( $slug ) {
		if ( ! $this->has( $slug ) ) {
			/* translators: %s: submission handler slug */
			return new Error( $this->get_prefix() . 'submission_handler_not_exist', sprintf( __( 'A submission handler with the slug %s does not exist.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		if ( isset( $this->default_submission_handlers[ $slug ] ) ) {
			/* translators: %s: submission handler slug */
			return new Error( $this->get_prefix() . 'submission_handler_is_default', sprintf( __( 'The default submission handler %s cannot be unregistered.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		unset( $this->submission_handlers[ $slug ] );

		return true;
	}

	/**
	 * Registers the default submission handlers.
	 *
	 * The function also executes a hook that should be used by other developers to register their own submission handlers.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_defaults() {
		foreach ( $this->default_submission_handlers as $slug => $submission_handler_class_name ) {
			$this->register( $slug, $submission_handler_class_name );
		}

		/**
		 * Fires when the default submission handlers have been registered.
		 *
		 * This action should be used to register custom submission handlers.
		 *
		 * @since 1.0.0
		 *
		 * @param Module $submission_handlers Submission handler manager instance.
		 */
		do_action( "{$this->get_prefix()}register_submission_handlers", $this );
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
