<?php
/**
 * Plugin Name:  Torro Forms
 * Plugin URI:   http://www.awesome.ug
 * Description:  Drag & drop your Form with the Torro Forms Plugin.
 * Version:      1.0.0alpha1
 * Author:       awesome.ug
 * Author URI:   http://www.awesome.ug
 * Author Email: contact@awesome.ug
 * License:      GPLv3.0
 * License URI: ./assets/license.txt
 * Text Domain: torro-forms
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_Init {
	/**
	 * @var $admin_notices
	 * @since 1.0.0
	 */
	static $admin_notices = array();

	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		self::constants();
		self::load_textdomain();
		self::load_files();

		register_activation_hook( __FILE__, array( __CLASS__, 'activate' ) );
		register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivate' ) );
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );

		if ( ! self::is_installed() ) {
			self::setup();
		}

		if ( is_admin() ) {
			add_action( 'admin_notices', array( __CLASS__, 'show_admin_notices' ) );
		}
	}

	/**
	 * Defining Constants for Use in Plugin
	 *
	 * @since 1.0.0
	 */
	private static function constants() {
		define( 'TORRO_FOLDER', plugin_dir_path( __FILE__ ) );
		define( 'TORRO_RELATIVE_FOLDER', substr( TORRO_FOLDER, strlen( WP_PLUGIN_DIR ), strlen( TORRO_FOLDER ) ) );
		define( 'TORRO_URLPATH', plugin_dir_url( __FILE__ ) );
		define( 'TORRO_COMPONENTFOLDER', TORRO_FOLDER . 'components/' );
	}

	/**
	 * Loads the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	private static function load_textdomain() {
		$domain = 'torro-forms';

		$mofile_custom = sprintf( '%s-%s.mo', $domain, apply_filters( 'torro_locale', get_locale() ) );

		$locations = apply_filters( 'torro_locale_locations', array(
			trailingslashit( WP_LANG_DIR . '/' . $domain  ),
			trailingslashit( WP_LANG_DIR ),
			trailingslashit( TORRO_FOLDER ) . 'languages/',
		) );

		// Try custom locations in WP_LANG_DIR.
		foreach ( $locations as $location ) {
			if ( load_textdomain( $domain, $location . $mofile_custom ) ) {
				return true;
			}
		}
	}

	/**
	 * Getting include files
	 *
	 * @since 1.0.0
	 */
	private static function load_files() {
		// Loading Functions
		require_once( TORRO_FOLDER . 'includes/functions.php' );
		require_once( TORRO_FOLDER . 'includes/compat.php' );
		require_once( TORRO_FOLDER . 'includes/wp-editor.php' );

		// Loading Core
		require_once( TORRO_FOLDER . 'core/init.php' );

		// Loading Components
		require_once( TORRO_COMPONENTFOLDER . 'actions/component.php' );
		require_once( TORRO_COMPONENTFOLDER . 'restrictions/component.php' );
		require_once( TORRO_COMPONENTFOLDER . 'results/component.php' );
	}

	/**
	 * Checking Requirements and adding Error Messages.
	 *
	 * @since 1.0.0
	 */
	public static function check_requirements() {}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 * @since 1.0.0
	 */
	public static function activate( $network_wide ) {
		self::setup();

		flush_rewrite_rules();
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 * @since 1.0.0
	 */
	public static function deactivate( $network_wide ) {
		delete_option( 'questions_is_installed' );
	}

	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 * @since 1.0.0
	 */
	public static function uninstall( $network_wide ) {}


	/**
	 * Checking if the plugin already installed
	 *
	 * @return boolean $is_installed
	 * @since 1.0.0
	 */
	private static function is_installed() {
		global $wpdb, $torro_global;

		$tables = get_object_vars( $torro_global->tables );

		// Checking if all tables are existing
		foreach ( $tables AS $table ) {
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) != $table ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Setting up base plugin data
	 * @since 1.0.0
	 */
	private static function setup() {
		$script_db_version = '1.0.2';
		$current_db_version  = get_option( 'torro_db_version' );

		if ( false !== get_option( 'questions_db_version' ) ) {
			require_once( 'includes/updates/to-awesome-forms.php' );
			torro_questions_to_awesome_forms();
		}

		if ( false !== get_option( 'af_db_version' ) ) {
			require_once( 'includes/updates/to-torro-forms.php' );
			awesome_forms_to_torro_forms();
		}

		if ( false === get_option( 'torro_db_version' ) || false === self::is_installed() || true === version_compare( $current_db_version, $script_db_version, '<' )  ) {
			self::install_tables();
			update_option( 'torro_db_version', $script_db_version );
		}

		require_once( TORRO_FOLDER . 'core/init.php' );
	}

	/**
	 * Installing tables
	 */
	private static function install_tables() {
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$table_elements = $wpdb->prefix . 'torro_elements';
		$table_element_answers = $wpdb->prefix . 'torro_element_answers';
		$table_results = $wpdb->prefix . 'torro_results';
		$table_results_values = $wpdb->prefix . 'torro_result_values';
		$table_settings = $wpdb->prefix . 'torro_settings';
		$table_participiants = $wpdb->prefix . 'torro_participiants';
		$table_email_notifications = $wpdb->prefix . 'torro_email_notifications';

		$charset_collate = torro_get_charset_collate();

		$sql = "CREATE TABLE $table_elements (
		id int(11) NOT NULL AUTO_INCREMENT,
		form_id int(11) NOT NULL,
		label text NOT NULL,
		sort int(11) NOT NULL,
		type char(50) NOT NULL,
		UNIQUE KEY id (id)
		) ENGINE = INNODB " . $charset_collate . ";";

		dbDelta( $sql );

		$sql = "CREATE TABLE $table_element_answers (
		id int(11) NOT NULL AUTO_INCREMENT,
		element_id int(11) NOT NULL,
		section char(100) NOT NULL,
		answer text NOT NULL,
		sort int(11) NOT NULL,
		UNIQUE KEY id (id)
		) ENGINE = INNODB " . $charset_collate . ";";

		dbDelta( $sql );

		$sql = "CREATE TABLE $table_results (
		id int(11) NOT NULL AUTO_INCREMENT,
		form_id int(11) NOT NULL,
		user_id int(11) NOT NULL,
		timestamp int(11) NOT NULL,
		remote_addr char(15) NOT NULL,
		cookie_key char(50) NOT NULL,
		UNIQUE KEY id (id)
		) ENGINE = INNODB " . $charset_collate . ";";

		dbDelta( $sql );

		$sql = "CREATE TABLE $table_results_values (
		id int(11) NOT NULL AUTO_INCREMENT,
		result_id int(11) NOT NULL,
		element_id int(11) NOT NULL,
		value text NOT NULL,
		UNIQUE KEY id (id)
		) ENGINE = INNODB " . $charset_collate . ";";

		dbDelta( $sql );

		$sql = "CREATE TABLE $table_settings (
		id int(11) NOT NULL AUTO_INCREMENT,
		element_id int(11) NOT NULL,
		name text NOT NULL,
		value text NOT NULL,
		UNIQUE KEY id (id)
		) ENGINE = INNODB " . $charset_collate . ";";

		dbDelta( $sql );

		$sql = "CREATE TABLE $table_participiants (
		id int(11) NOT NULL AUTO_INCREMENT,
		form_id int(11) NOT NULL,
		user_id int(11) NOT NULL,
		UNIQUE KEY id (id)
		) ENGINE = INNODB " . $charset_collate . ";";

		dbDelta( $sql );

		$sql = "CREATE TABLE $table_email_notifications (
		id int(11) NOT NULL AUTO_INCREMENT,
		form_id int(11) NOT NULL,
		notification_name text NOT NULL,
		from_name text NOT NULL,
		from_email text NOT NULL,
		to_name text NOT NULL,
		to_email text NOT NULL,
		subject text NOT NULL,
		message text NOT NULL,
		UNIQUE KEY id (id)
		) ENGINE = INNODB " . $charset_collate . ";";

		dbDelta( $sql );
	}

	/**
	 * Adds a notice to
	 *
	 * @param        $message
	 * @param string $type
	 */
	public static function admin_notice( $message, $type = 'updated' ) {
		self::$admin_notices[] = array(
			'message' => '<b>Torro Forms</b>: ' . $message,
			'type'    => $type,
		);
	}

	/**
	 * Show Notices in Admin
	 * @since 1.0.0
	 */
	public static function show_admin_notices() {
		if ( is_array( self::$admin_notices ) && count( self::$admin_notices ) > 0 ) {
			$html = '';
			foreach ( self::$admin_notices as $notice ) {
				$html .= '<div class="' . esc_attr( $notice['type'] ) . '"><p>' . esc_html( $notice['message'] ) . '</p></div>';
			}
			echo $html;
		}
	}

	/**
	 * Logging function
	 *
	 * @param $message
	 * @since 1.0.0
	 */
	public static function log( $message ) {
		$wp_upload_dir = wp_upload_dir();
		$log_dir = trailingslashit( $wp_upload_dir[ 'path' ]  ) . '/torro-logs';

		if ( ! file_exists( $log_dir ) || ! is_dir( $log_dir ) ) {
			mkdir( $log_dir );
		}

		$file = fopen( $log_dir . '/main.log', 'a' );
		fputs( $file, $message . chr( 13 ) );
		fclose( $file );
	}
}

function torro_init() {
	Torro_Init::init();
}
add_action( 'plugins_loaded', 'torro_init' );
