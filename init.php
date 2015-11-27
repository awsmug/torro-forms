<?php
/**
 * Plugin Name:  Awesome Forms
 * Plugin URI:   http://www.awesome.ug
 * Description:  Drag & drop your Form with the Awesome Forms Plugin.
 * Version:      1.0.0 alpha 1
 * Author:       awesome.ug
 * Author URI:   http://www.awesome.ug
 * Author Email: contact@awesome.ug
 * License:      GPLv3.0
 * License URI: ./assets/license.txt
 * Text Domain: af-locale
 * Domain Path: /languages
 */

if( !defined( 'ABSPATH' ) )
{
	exit;
}

class AF_Init
{
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
	public static function init()
	{
		self::constants();
		self::load_textdomain();
		self::load_files();

		register_activation_hook( __FILE__, array( __CLASS__, 'activate' ) );
		register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivate' ) );
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );

		if( is_admin() )
		{
			add_action( 'admin_notices', array( __CLASS__, 'show_admin_notices' ) );
		}
	}

	/**
	 * Defining Constants for Use in Plugin
	 *
	 * @since 1.0.0
	 */
	private static function constants()
	{
		define( 'AF_FOLDER', plugin_dir_path( __FILE__ ) );
		define( 'AF_RELATIVE_FOLDER', substr( AF_FOLDER, strlen( WP_PLUGIN_DIR ), strlen( AF_FOLDER ) ) );
		define( 'AF_URLPATH', plugin_dir_url( __FILE__ ) );
		define( 'AF_COMPONENTFOLDER', AF_FOLDER . 'components/' );
	}

	/**
	 * Loads the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	private static function load_textdomain()
	{
		$domain = 'af-locale';

		$mofile_custom = sprintf( '%s-%s.mo', $domain, apply_filters( 'af_locale', get_locale() ) );

		$locations = apply_filters( 'af_locale_locations', array(
				trailingslashit( WP_LANG_DIR . '/' . $domain  ),
				trailingslashit( WP_LANG_DIR ),
				trailingslashit( AF_FOLDER ) . 'languages/',
		) );

		// Try custom locations in WP_LANG_DIR.
		foreach ( $locations as $location )
		{
			if ( load_textdomain( $domain, $location . $mofile_custom ) )
			{
				return true;
			}
		}
	}

	/**
	 * Getting include files
	 *
	 * @since 1.0.0
	 */
	private static function load_files()
	{
		// Loading Functions
		require_once( AF_FOLDER . 'functions.php' );
		require_once( AF_FOLDER . 'conflicts.php' );
		require_once( AF_FOLDER . 'includes/wp-editor.php' );

		// Loading Core
		require_once( AF_FOLDER . 'core/init.php' );

		// Loading Components
		require_once( AF_COMPONENTFOLDER . 'actions/component.php' );
		require_once( AF_COMPONENTFOLDER . 'restrictions/component.php' );
		require_once( AF_COMPONENTFOLDER . 'results/component.php' );
	}

	/**
	 * Checking Requirements and adding Error Messages.
	 *
	 * @since 1.0.0
	 */
	public static function check_requirements()
	{
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 * @since 1.0.0
	 */
	public static function activate( $network_wide )
	{
		self::setup();
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 * @since 1.0.0
	 */
	public static function deactivate( $network_wide )
	{
		delete_option( 'questions_is_installed' );
	}

	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 * @since 1.0.0
	 */
	public static function uninstall( $network_wide )
	{
	}


	/**
	 * Checking if the plugin already installed
	 *
	 * @return boolean $is_installed
	 * @since 1.0.0
	 */
	private static function is_installed()
	{
		global $wpdb;

		$tables = array(
				$wpdb->prefix . 'af_elements',
				$wpdb->prefix . 'af_element_answers',
				$wpdb->prefix . 'af_results',
				$wpdb->prefix . 'af_result_values',
				$wpdb->prefix . 'af_settings',
				$wpdb->prefix . 'af_participiants',
				$wpdb->prefix . 'af_email_notifications'
		);

		// Checking if all tables are existing
		$not_found = FALSE;
		foreach( $tables AS $table )
		{
			if( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) != $table )
			{
				$not_found = TRUE;
			}
		}

		$is_installed_option = (boolean) get_option( 'questions_is_installed', FALSE );

		if( $not_found || FALSE == $is_installed_option )
		{
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Setting up base plugin data
	 * @since 1.0.0
	 */
	private static function setup()
	{
		$script_db_version = '1.0.1';
		$current_db_version  = get_option( 'af_db_version' );

		if( FALSE !== get_option( 'questions_db_version' ) )
		{
			require_once( 'updates/to-awesome-forms.php' );
			af_questions_to_awesome_forms();
		}

		if( ! get_option( 'af_db_version' ) || !self::is_installed() || version_compare( $current_db_version, $script_db_version, '<' )  )
		{
			self::install_tables();
		}

		require_once( AF_FOLDER . 'core/init.php' );

		flush_rewrite_rules();
	}

	/**
	 * Installing tables
	 */
	private static function install_tables()
	{
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$table_elements = $wpdb->prefix . 'af_elements';
		$table_element_answers = $wpdb->prefix . 'af_element_answers';
		$table_results = $wpdb->prefix . 'af_results';
		$table_results_values = $wpdb->prefix . 'af_result_values';
		$table_settings = $wpdb->prefix . 'af_settings';
		$table_participiants = $wpdb->prefix . 'af_participiants';
		$table_email_notifications = $wpdb->prefix . 'af_email_notifications';

		$charset_collate = af_get_charset_collate();

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

		$sql = "UPDATE {$table_elements} SET type='textfield' WHERE type='Text'";
		$wpdb->query( $sql );

		$sql = "UPDATE {$table_elements} SET type='textarea' WHERE type='Textarea'";
		$wpdb->query( $sql );

		$sql = "UPDATE {$table_elements} SET type='dropdown' WHERE type='Dropdown'";
		$wpdb->query( $sql );

		$sql = "UPDATE {$table_elements} SET type='onechoice' WHERE type='OneChoice'";
		$wpdb->query( $sql );

		$sql = "UPDATE {$table_elements} SET type='multiplechoice' WHERE type='MultipleChoice'";
		$wpdb->query( $sql );

		$sql = "UPDATE {$table_elements} SET type='text' WHERE type='Description'";
		$wpdb->query( $sql );

		$sql = "UPDATE {$table_elements} SET type='splitter' WHERE type='Splitter'";
		$wpdb->query( $sql );

		$sql = "UPDATE {$table_elements} SET type='separator' WHERE type='Separator'";
		$wpdb->query( $sql );

		$sql = "UPDATE {$wpdb->prefix}term_taxonomy SET type='af-forms-categories' WHERE taxonomy='questions-categories'";
		$wpdb->query( $sql );

		$sql = "UPDATE {$wpdb->prefix}term_taxonomy SET type='af-forms-categories' WHERE taxonomy='questions-categories'";
		$wpdb->query( $sql );

		dbDelta( $sql );

		update_option( 'af_db_version', $script_db_version );
	}

	/**
	 * Adds a notice to
	 *
	 * @param        $message
	 * @param string $type
	 */
	public static function admin_notice( $message, $type = 'updated' )
	{
		self::$admin_notices[] = array(
				'message' => '<b>Awesome Forms</b>: ' . $message,
				'type'    => $type
		);
	}
	/**
	 * Show Notices in Admin
	 * @since 1.0.0
	 */
	public static function show_admin_notices()
	{
		if( is_array( self::$admin_notices ) && count( self::$admin_notices ) > 0 )
		{
			$html = '';
			foreach( self::$admin_notices AS $notice )
			{
				$message = $notice[ 'message' ];
				$html .= '<div class="' . $notice[ 'type' ] . '"><p>' .$message . '</p></div>';
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
	public static function log( $message )
	{
		$wp_upload_dir = wp_upload_dir();
		$log_dir = trailingslashit( $wp_upload_dir[ 'path' ]  ) . '/af-logs';

		if( !file_exists( $log_dir ) || !is_dir( $log_dir ) )
		{
			mkdir( $log_dir );
		}

		$file = fopen(  $log_dir . '/main.log', 'a' );
		fputs( $file, $message . chr(13) );
		fclose( $file );
	}
}
AF_Init::init();
