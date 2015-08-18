<?php
/**
 * Plugin Name:  Questions
 * Plugin URI:   http://www.awesome.ug
 * Description:  Drag & drop your survey/poll with the WordPress Questions plugin.
 * Version:      1.0.0 beta 20
 * Author:       awesome.ug
 * Author URI:   http://www.awesome.ug
 * Author Email: contact@awesome.ug
 * License:      GPLv3.0
 * License URI: ./assets/license.txt
 * Text Domain: questions-locale
 * Domain Path: /languages
 */

if( !defined( 'ABSPATH' ) ){
	exit;
}

class Questions_Init
{

	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0.0
	 */
	public static function init()
	{

		global $qu_plugin_errors;

		$qu_plugin_errors = array();

		self::constants();
		self::includes();
		self::load_components();
		self::load_textdomain();

		// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
		register_activation_hook( __FILE__, array( __CLASS__, 'activate' ) );
		register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivate' ) );
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );

		// If plugin isn't installed, install it now
		if( !self::is_installed() ){
			add_action( 'init', array( __CLASS__, 'install_plugin' ), 100 );
		}

		// Functions on Frontend
		if( is_admin() ):
			// Register admin styles and scripts
			add_action( 'plugins_loaded', array( __CLASS__, 'check_requirements' ) );
			add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		else:
			// Register plugin styles and scripts
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_plugin_styles' ) );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_plugin_scripts' ) );
		endif;
	} // end constructor

	/**
	 * Checking Requirements and adding Error Messages.
	 *
	 * @since 1.0.0
	 */
	public static function check_requirements()
	{

		global $qu_plugin_errors;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is
	 *                                 disabled or plugin is activated on an individual blog
	 *
	 * @since 1.0.0
	 */
	public static function activate( $network_wide )
	{
		global $wpdb;

		self::install_tables();
	} // end activate

	/**
	 * Is plugin already installed?
	 */
	public static function is_installed()
	{
		global $wpdb;

		$tables = array( $wpdb->prefix . 'questions_questions',
			$wpdb->prefix . 'questions_answers',
			$wpdb->prefix . 'questions_responds',
			$wpdb->prefix . 'questions_respond_answers',
			$wpdb->prefix . 'questions_settings',
			$wpdb->prefix . 'questions_participiants' );

		// Checking if all tables are existing
		$not_found = FALSE;
		foreach( $tables AS $table ):
			if( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) != $table ):
				$not_found = TRUE;
			endif;
		endforeach;

		$is_installed_option = (boolean) get_option( 'questions_is_installed', FALSE );

		if( $not_found || FALSE == $is_installed_option ){
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Installing plugin
	 */
	public static function install_plugin()
	{
		self::install_tables();
		flush_rewrite_rules();
		update_option( 'questions_is_installed', TRUE );
	}

	/**
	 * Creating / Updating tables
	 */
	public static function install_tables()
	{
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$table_questions = $wpdb->prefix . 'questions_questions';
		$table_answers = $wpdb->prefix . 'questions_answers';
		$table_responds = $wpdb->prefix . 'questions_responds';
		$table_respond_answers = $wpdb->prefix . 'questions_respond_answers';
		$table_settings = $wpdb->prefix . 'questions_settings';
		$table_participiants = $wpdb->prefix . 'questions_participiants';

		$sql = "CREATE TABLE $table_questions (
			id int(11) NOT NULL AUTO_INCREMENT,
			questions_id int(11) NOT NULL,
			question text NOT NULL,
			sort int(11) NOT NULL,
			type char(50) NOT NULL,
			UNIQUE KEY id (id)
			) ENGINE = INNODB DEFAULT CHARSET = utf8;";

		dbDelta( $sql );

		$sql = "CREATE TABLE $table_answers (
			id int(11) NOT NULL AUTO_INCREMENT,
			question_id int(11) NOT NULL,
			section char(100) NOT NULL,
			answer text NOT NULL,
			sort int(11) NOT NULL,
			UNIQUE KEY id (id)
			) ENGINE = INNODB DEFAULT CHARSET = utf8;";

		dbDelta( $sql );

		$sql = "CREATE TABLE $table_responds (
			id int(11) NOT NULL AUTO_INCREMENT,
			questions_id int(11) NOT NULL,
			user_id int(11) NOT NULL,
			timestamp int(11) NOT NULL,
			remote_addr char(15) NOT NULL,
			cookie_key char(50) NOT NULL,
			UNIQUE KEY id (id)
			) ENGINE = INNODB DEFAULT CHARSET = utf8;";

		dbDelta( $sql );

		$sql = "CREATE TABLE $table_respond_answers (
			id int(11) NOT NULL AUTO_INCREMENT,
			respond_id int(11) NOT NULL,
			question_id int(11) NOT NULL,
			value text NOT NULL,
			UNIQUE KEY id (id)
			) ENGINE = INNODB DEFAULT CHARSET = utf8;";

		dbDelta( $sql );

		$sql = "CREATE TABLE $table_settings (
			id int(11) NOT NULL AUTO_INCREMENT,
			question_id int(11) NOT NULL,
			name text NOT NULL,
			value text NOT NULL,
			UNIQUE KEY id (id)
			) ENGINE = INNODB DEFAULT CHARSET = utf8;";

		dbDelta( $sql );

		$sql = "CREATE TABLE $table_participiants (
			id int(11) NOT NULL AUTO_INCREMENT,
			survey_id int(11) NOT NULL,
			user_id int(11) NOT NULL,
			UNIQUE KEY id (id)
			) ENGINE = INNODB DEFAULT CHARSET = utf8;";

		dbDelta( $sql );

		$sql = "ALTER TABLE $table_questions CONVERT TO CHARACTER SET utf8 collate utf8_general_ci;";
		$wpdb->query( $sql );

		$sql = "ALTER TABLE $table_answers CONVERT TO CHARACTER SET utf8 collate utf8_general_ci;";
		$wpdb->query( $sql );

		$sql = "ALTER TABLE $table_responds CONVERT TO CHARACTER SET utf8 collate utf8_general_ci;";
		$wpdb->query( $sql );

		$sql = "ALTER TABLE $table_respond_answers CONVERT TO CHARACTER SET utf8 collate utf8_general_ci;";
		$wpdb->query( $sql );

		$sql = "ALTER TABLE $table_participiants CONVERT TO CHARACTER SET utf8 collate utf8_general_ci;";
		$wpdb->query( $sql );

		$sql = "ALTER TABLE $table_settings CONVERT TO CHARACTER SET utf8 collate utf8_general_ci;";
		$wpdb->query( $sql );

		update_option( 'questions_db_version', '1.1.0' );
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is
	 *                                 disabled or plugin is activated on an individual blog
	 */
	public static function deactivate( $network_wide )
	{

		delete_option( 'questions_is_installed' );
	} // end deactivate

	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is
	 *                                 disabled or plugin is activated on an individual blog
	 *
	 * @since 1.0.0
	 */
	public static function uninstall( $network_wide )
	{
	} // end uninstall

	/**
	 * Loads the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public static function load_textdomain()
	{

		load_plugin_textdomain( 'questions-locale', FALSE, QUESTIONS_RELATIVE_FOLDER . '/languages' );
	} // end plugin_textdomain

	/**
	 * Registers and enqueues plugin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public static function register_plugin_styles()
	{

		wp_enqueue_style( 'questions-plugin-styles', QUESTIONS_URLPATH . '/includes/css/display.css' );
	} // end register_plugin_styles

	/**
	 * Registers and enqueues plugin-specific scripts.
	 *
	 * @since 1.0.0
	 */
	public static function register_plugin_scripts()
	{
	} // end register_plugin_scripts

	/**
	 * Defining Constants for Use in Plugin
	 *
	 * @since 1.0.0
	 */
	public static function constants()
	{

		define( 'QUESTIONS_FOLDER', self::get_folder() );
		define( 'QUESTIONS_RELATIVE_FOLDER', substr( QUESTIONS_FOLDER, strlen( WP_PLUGIN_DIR ), strlen( QUESTIONS_FOLDER ) ) );
		define( 'QUESTIONS_URLPATH', self::get_url_path() );
		define( 'QUESTIONS_COMPONENTFOLDER', QUESTIONS_FOLDER . '/components' );
	}

	/**
	 * Getting include files
	 *
	 * @since 1.0.0
	 */
	public static function includes()
	{

		// Loading functions
		include( QUESTIONS_FOLDER . '/functions.php' );
	}

	/**
	 * Loading components
	 *
	 * @since 1.0.0
	 */
	public static function load_components()
	{

		// Loading base functions
		include( QUESTIONS_COMPONENTFOLDER . '/class-component.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/core/component.php' );

		// Loading components
		include( QUESTIONS_COMPONENTFOLDER . '/admin/component.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/charts/component.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/elements/component.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/restrictions/component.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/notifications/component.php' );
	}

	/**
	 * Getting URL
	 *
	 * @since 1.0.0
	 */
	private static function get_url_path()
	{

		$slashed_folder = str_replace( '\\', '/', QUESTIONS_FOLDER ); // Replacing backslashes width slashes vor windows installations
		$sub_path = substr( $slashed_folder, strlen( ABSPATH ), ( strlen( $slashed_folder ) - 11 ) );
		$script_url = get_bloginfo( 'wpurl' ) . '/' . $sub_path;

		return $script_url;
	}

	/**
	 * Getting Folder
	 *
	 * @since 1.0.0
	 */
	private static function get_folder()
	{

		return plugin_dir_path( __FILE__ );
	}

	/**
	 * Showing Errors
	 *
	 * @since 1.0.0
	 */
	public static function admin_notices()
	{

		global $qu_plugin_errors, $qu_plugin_errors;

		if( count( $qu_plugin_errors ) > 0 ):
			foreach( $qu_plugin_errors AS $error ){
				echo '<div class="error"><p>' . $error . '</p></div>';
			}
		endif;

		if( count( $qu_plugin_errors ) > 0 ):
			foreach( $qu_plugin_errors AS $notice ){
				echo '<div class="updated"><p>' . $notice . '</p></div>';
			}
		endif;
	}

}
Questions_Init::init();
