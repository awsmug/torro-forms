<?php
/**
 * Plugin Name:  Awesome Forms
 * Plugin URI:   http://www.awesome.ug
 * Description:  Drag & drop your Form with the Awesome Forms Plugin.
 * Version:      1.0.0 beta 20
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
	 * @var Notices for screening in Admin
	 */
	static $notices = array();

	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0.0
	 */
	public static function init()
	{
		// Loading variables
		self::constants();
		self::load_textdomain();

		// Loading other stuff
		self::includes();

		// Install & Uninstall Scripts
		register_activation_hook( __FILE__, array( __CLASS__, 'activate' ) );
		register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivate' ) );
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );

		if( !self::is_installed() )
		{
			add_action( 'init', array( __CLASS__, 'install_plugin' ) );
		}

		// Functions on Frontend
		if( is_admin() ):
			// Register admin styles and scripts
			add_action( 'plugins_loaded', array( __CLASS__, 'check_requirements' ) );
			add_action( 'admin_notices', array( __CLASS__, 'show_admin_notices' ) );
		else:
			// Register plugin styles and scripts
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_plugin_styles' ) );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_plugin_scripts' ) );
		endif;
	}

	/**
	 * Defining Constants for Use in Plugin
	 *
	 * @since 1.0.0
	 */
	public static function constants()
	{
		define( 'AF_FOLDER', self::get_folder() );
		define( 'AF_RELATIVE_FOLDER', substr( AF_FOLDER, strlen( WP_PLUGIN_DIR ), strlen( AF_FOLDER ) ) );
		define( 'AF_URLPATH', self::get_url_path() );

		define( 'AF_COMPONENTFOLDER', AF_FOLDER . 'components/' );
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
	 * Getting URL
	 *
	 * @since 1.0.0
	 */
	private static function get_url_path()
	{
		return plugin_dir_url( __FILE__ );
	}

	/**
	 * Loads the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public static function load_textdomain()
	{
		load_plugin_textdomain( 'af-locale', FALSE, AF_RELATIVE_FOLDER . '/languages' );
	}

	/**
	 * Getting include files
	 *
	 * @since 1.0.0
	 */
	public static function includes()
	{
		// Loading Functions
		include( AF_FOLDER . 'functions.php' );
		include( AF_FOLDER . 'includes/wp-editor.php' );

		// Loading Core
		include( AF_FOLDER . 'core/init.php' );

		include( AF_COMPONENTFOLDER . 'actions/component.php' );
		include( AF_COMPONENTFOLDER . 'restrictions/component.php' );
		include( AF_COMPONENTFOLDER . 'results/component.php' );
	}

	/**
	 * Is plugin already installed?
	 */
	public static function is_installed()
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
		foreach( $tables AS $table ):
			if( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) != $table ):
				$not_found = TRUE;
			endif;
		endforeach;

		$is_installed_option = (boolean) get_option( 'questions_is_installed', FALSE );

		if( $not_found || FALSE == $is_installed_option )
		{
			return FALSE;
		}

		return TRUE;
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
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is
	 *                                 disabled or plugin is activated on an individual blog
	 *
	 * @since 1.0.0
	 */
	public static function activate( $network_wide )
	{
		self::install_tables();
	}

	/**
	 * Creating / Updating tables
	 */
	public static function install_tables()
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

		$sql = "UPDATE {$wpdb->prefix}posts SET post_type='af-forms' WHERE post_type='questions'";
		$wpdb->query( $sql );

		$sql = "UPDATE {$wpdb->prefix}term_taxonomy SET taxonomy='af-forms-categories' WHERE taxonomy='questions-categories'";
		$wpdb->query( $sql );

		if( '1.1.1' == get_option( 'questions_db_version' ) )
		{
			self::update_from_questions_to_af();
		}
		elseif( ! get_option( 'af_db_version' ) || !self::is_installed() )
		{
			$charset_collate = self::get_charset_collate();

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

			update_option( 'af_db_version', '1.0.0' );
		}
	}

	private static function get_charset_collate() {
		global $wpdb;

		$charset_collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET " . $wpdb->charset;
			}
			if ( ! empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE " . $wpdb->collate;
			}
		}

		return $charset_collate;
	}

	public static function update_from_questions_to_af()
	{
		global $wpdb;

		$table_elements_old = $wpdb->prefix . 'questions_questions';
		$table_answers_old = $wpdb->prefix . 'questions_answers';
		$table_settings_old = $wpdb->prefix . 'questions_settings';
		$table_responds_old = $wpdb->prefix . 'questions_responds';
		$table_respond_answers_old = $wpdb->prefix . 'questions_respond_answers';
		$table_participiants_old = $wpdb->prefix . 'questions_participiants';
		$table_email_notifications_old = $wpdb->prefix . 'questions_email_notifications';

		$table_elements_new = $wpdb->prefix . 'af_elements';
		$table_answers_new = $wpdb->prefix . 'af_element_answers';
		$table_settings_new = $wpdb->prefix . 'af_settings';
		$table_responds_new = $wpdb->prefix . 'af_results';
		$table_respond_answers_new = $wpdb->prefix . 'af_result_values';
		$table_participiants_new = $wpdb->prefix . 'af_participiants';
		$table_email_notifications_new = $wpdb->prefix . 'af_email_notifications';


		$sql = "RENAME TABLE {$table_elements_old} TO {$table_elements_new}";
		$wpdb->query( $sql );

		$sql = "RENAME TABLE {$table_answers_old} TO {$table_answers_new}";
		$wpdb->query( $sql );

		$sql = "RENAME TABLE {$table_settings_old} TO {$table_settings_new}";
		$wpdb->query( $sql );

		$sql = "RENAME TABLE {$table_responds_old} TO {$table_responds_new}";
		$wpdb->query( $sql );

		$sql = "RENAME TABLE {$table_respond_answers_old} TO {$table_respond_answers_new}";
		$wpdb->query( $sql );

		$sql = "RENAME TABLE {$table_participiants_old} TO {$table_participiants_new}";
		$wpdb->query( $sql );

		$sql = "RENAME TABLE {$table_email_notifications_old} TO {$table_email_notifications_new}";
		$wpdb->query( $sql );

		$sql = "ALTER TABLE {$table_elements_new} CHANGE questions_id form_id int(11)";
		$wpdb->query( $sql );

		$sql = "ALTER TABLE {$table_elements_new} CHANGE question label text";
		$wpdb->query( $sql );

		$sql = "ALTER TABLE {$table_answers_new} CHANGE question_id element_id int(11)";
		$wpdb->query( $sql );

		$sql = "ALTER TABLE {$table_responds_new} CHANGE questions_id form_id int(11)";
		$wpdb->query( $sql );

		$sql = "ALTER TABLE {$table_respond_answers_new} CHANGE respond_id result_id int(11)";
		$wpdb->query( $sql );

		$sql = "ALTER TABLE {$table_respond_answers_new} CHANGE question_id element_id int(11)";
		$wpdb->query( $sql );

		$sql = "ALTER TABLE {$table_settings_new} CHANGE question_id element_id int(11)";
		$wpdb->query( $sql );

		$sql = "ALTER TABLE {$table_participiants_new} CHANGE survey_id form_id int(11)";
		$wpdb->query( $sql );

		$sql = "UPDATE {$wpdb->prefix}posts SET post_type='af-forms' WHERE post_type='questions'";
		$wpdb->query( $sql );

		$sql = "UPDATE {$wpdb->prefix}term_taxonomy SET taxonomy='af-forms-categories' WHERE taxonomy='questions-categories'";
		$wpdb->query( $sql );

		update_option( 'af_db_version', '1.0.0' );
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
	 * Fired when the plugin is deactivated.
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is
	 *                                 disabled or plugin is activated on an individual blog
	 */
	public static function deactivate( $network_wide )
	{
		delete_option( 'questions_is_installed' );
	}

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
	}

	/**
	 * Registers and enqueues plugin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public static function register_plugin_styles()
	{
		wp_enqueue_style( 'af-plugin-styles', AF_URLPATH . 'includes/css/display.css' );
	}

	/**
	 * Registers and enqueues plugin-specific scripts.
	 *
	 * @since 1.0.0
	 */
	public static function register_plugin_scripts()
	{
	}

	/**
	 * Adds a notice to
	 *
	 * @param        $message
	 * @param string $type
	 */
	public static function admin_notice( $message, $type = 'updated' )
	{
		self::$notices[] = array(
				'message' => '<b>Awesome Forms</b>: ' . $message,
				'type'    => $type
		);
	}
	/**
	 * Show Notices in Admin
	 */
	public static function show_admin_notices()
	{
		if( is_array( self::$notices ) && count( self::$notices ) > 0 )
		{
			$html = '';
			foreach( self::$notices AS $notice )
			{
				$message = $notice[ 'message' ];
				$html .= '<div class="' . $notice[ 'type' ] . '"><p>' .$message . '</p></div>';
			}
			echo $html;
		}
	}
}

AF_Init::init(); // Starting immediately!
