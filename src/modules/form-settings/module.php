<?php
/**
 * Form Settings module class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Form_Settings;

use awsmug\Torro_Forms\Modules\Module as Module_Base;
use awsmug\Torro_Forms\Assets;

/**
 * Class for the Form Settings module.
 *
 * @since 1.0.0
 */
class Module extends Module_Base {

	/**
	 * Registered form settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $form_settings = array();

	/**
	 * Default form settings definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $default_form_settings = array();

	/**
	 * Bootstraps the module by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function bootstrap() {
		$this->slug        = 'form_settings';
		$this->title       = __( 'Form Settings', 'torro-forms' );
		$this->description = __( 'Form settings control the general behavior of forms, such as access control or security.', 'torro-forms' );
	}

	/**
	 * Checks whether a specific form setting is registered.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Form setting slug.
	 * @return bool True if the form setting is registered, false otherwise.
	 */
	public function has( $slug ) {
		return isset( $this->form_settings[ $slug ] );
	}

	/**
	 * Returns a specific registered form setting.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Form setting slug.
	 * @return Form_Setting|Error Form setting instance, or error object if form setting is not registered.
	 */
	public function get( $slug ) {
		if ( ! $this->has( $slug ) ) {
			return new Error( $this->get_prefix() . 'form_setting_not_exist', sprintf( __( 'A form setting with the slug %s does not exist.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		return $this->form_settings[ $slug ];
	}

	/**
	 * Returns all registered form settings.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Associative array of `$slug => $form_setting_instance` pairs.
	 */
	public function get_all() {
		return $this->form_settings;
	}

	/**
	 * Registers a new form setting.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug                    Form setting slug.
	 * @param string $form_setting_class_name Form setting class name.
	 * @return bool|Error True on success, error object on failure.
	 */
	public function register( $slug, $form_setting_class_name ) {
		if ( ! did_action( 'init' ) ) {
			/* translators: 1: form setting slug, 2: init hookname */
			return new Error( $this->get_prefix() . 'form_setting_too_early', sprintf( __( 'The form setting %1$s cannot be registered before the %2$s hook.', 'torro-forms' ), $slug, '<code>init</code>' ), __METHOD__, '1.0.0' );
		}

		if ( $this->has( $slug ) ) {
			/* translators: %s: form setting slug */
			return new Error( $this->get_prefix() . 'form_setting_already_exist', sprintf( __( 'A form setting with the slug %s already exists.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		if ( ! class_exists( $form_setting_class_name ) ) {
			/* translators: %s: form setting class name */
			return new Error( $this->get_prefix() . 'form_setting_class_not_exist', sprintf( __( 'The class %s does not exist.', 'torro-forms' ), $form_setting_class_name ), __METHOD__, '1.0.0' );
		}

		if ( ! is_subclass_of( $form_setting_class_name, Form_Setting::class ) ) {
			/* translators: %s: form setting class name */
			return new Error( $this->get_prefix() . 'form_setting_class_not_allowed', sprintf( __( 'The class %s is not allowed for a form setting.', 'torro-forms' ), $form_setting_class_name ), __METHOD__, '1.0.0' );
		}

		$this->form_settings[ $slug ] = new $form_setting_class_name( $this );

		return true;
	}

	/**
	 * Unregisters a new form setting.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Form setting slug.
	 * @return bool|Error True on success, error object on failure.
	 */
	public function unregister( $slug ) {
		if ( ! $this->has( $slug ) ) {
			/* translators: %s: form setting slug */
			return new Error( $this->get_prefix() . 'form_setting_not_exist', sprintf( __( 'A form setting with the slug %s does not exist.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		if ( isset( $this->default_form_settings[ $slug ] ) ) {
			/* translators: %s: form setting slug */
			return new Error( $this->get_prefix() . 'form_setting_is_default', sprintf( __( 'The default form setting %s cannot be unregistered.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		unset( $this->form_settings[ $slug ] );

		return true;
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
		foreach ( $this->default_form_settings as $slug => $form_setting_class_name ) {
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
