<?php
/**
 * Core: Torro_General_Settings class
 *
 * @package TorroForms
 * @subpackage CoreSettings
 * @version 1.0.0beta1
 * @since 1.0.0beta1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms general settings class
 *
 * Handles general settings.
 *
 * @since 1.0.0beta1
 */
final class Torro_General_Settings extends Torro_Settings {
	/**
	 * Instance
	 *
	 * @var null|Torro_General_Settings
	 * @since 1.0.0
	 */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Initializing
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->title = __( 'General', 'torro-forms' );
		$this->name = 'general';

		$this->settings = array(
			'disclaimer'	=> array(
				'title'			=> __( 'Welcome to Torro Forms!', 'torro-forms' ),
				'description'	=> __( 'You want to build forms in an easy way? Torro Forms will help you do it quickly, yet with tons of options.', 'torro-forms' ),
				'type'			=> 'disclaimer'
			),
			'modules_title'	=> array(
				'title'			=> __( 'Form Modules', 'torro-forms' ),
				'description'	=> __( 'Check the modules of Torro Forms which have to be activated.', 'torro-forms' ),
				'type'			=> 'title'
			)
		);

		add_action( 'init', array( $this, 'add_modules' ), 5 ); // Loading Modules dynamical after init because moules existing not yet
	}

	/**
	 * Adding Modules
	 *
	 * @since 1.0.0
	 */
	public function add_modules() {
		$components = array();
		$defaults = array();

		$all_components = torro()->components()->get_all_registered();

		foreach ( $all_components as $component_name => $component ) {
			$components[ $component_name ] = $component->title;
			$defaults[] = $component_name;
		}

		$settings_arr = array(
			'modules'		=> array(
				'title'			=> __( 'Modules', 'torro-forms' ),
				'description'	=> __( 'You don´t need some of these components? Switch them off!', 'torro-forms' ),
				'type'			=> 'checkbox',
				'values'		=> $components,
				'default'		=> $defaults
			),
		    'slug'			=> array(
			    'title'			=> __( 'Slug', 'torro-forms' ),
			    'description'	=> __( 'The Slug name for URL building. (e.g. for an URL like http://mydomain.com/<strong>forms</strong>/mycontactform)'),
			    'type'			=> 'text',
			    'default'		=> 'forms'
			),
		    'frontend_css'			=> array(
			    'title'			=> __( 'CSS', 'torro-forms' ),
			    'type'			=> 'checkbox',
			    'values'        => array(
				    'show_css'       => __( 'Include Torro Forms CSS on frontend?', 'torro-forms' )
			    ),
			    'default'       => array( 'show_css' )
			),
		    'hard_uninstall'=> array(
			    'title'			=> __( 'Hard Uninstall', 'torro-forms' ),
			    'description'	=> __( '<strong>Use this setting with extreme caution</strong> as, when it is enabled, removing the plugin will remove all form content from your site forever.', 'torro-forms' ),
			    'type'			=> 'checkbox',
			    'values'        => array(
				    '1'       		=> __( 'Perform a hard uninstall when the plugin is removed?', 'torro-forms' )
			    ),
			    'default'       => array( '0' )
			),
		);

		torro()->settings()->get_registered( 'general' )->add_settings_field_arr( $settings_arr );
	}
}

torro()->settings()->register( 'Torro_General_Settings' );
