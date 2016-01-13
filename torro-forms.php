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
	private static $admin_notices = array();

	private static $tables_registered = false;

	private static $post_types_registered = false;

	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		self::load_textdomain();
		self::register_tables();
		self::includes();

		add_action( 'init', array( __CLASS__, 'custom_post_types' ), 11 );
		add_filter( 'body_class', array( __CLASS__, 'add_body_class' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_styles' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend_styles' ) );

		if ( is_admin() ) {
			add_action( 'admin_notices', array( __CLASS__, 'show_admin_notices' ) );
		}
	}

	/**
	 * Loads the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	private static function load_textdomain() {
		// check custom languages directory to allow overriding language files
		$locale = apply_filters( 'plugin_locale', get_locale(), 'torro-forms' );
		$mofile = WP_LANG_DIR . '/plugins/torro-forms/torro-forms-' . $locale . '.mo';
		if ( file_exists( $mofile ) ) {
			return load_textdomain( 'torro-forms', $mofile );
		}

		return load_plugin_textdomain( 'torro-forms', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}

	private static function register_tables() {
		global $wpdb;

		if ( self::$tables_registered ) {
			return;
		}

		self::$tables_registered = true;

		$tables = self::get_tables();

		foreach ( $tables as $table ) {
			$table_name = 'torro_' . $table;

			$wpdb->tables[] = $table_name;
			$wpdb->$table_name = $wpdb->prefix . $table_name;
		}
	}

	private static function get_tables() {
		$tables = $orig_tables = array(
			'elements',
			'element_answers',
			'results',
			'result_values',
			'settings',
			'participants',
			'email_notifications',
		);

		// this filter can only be used to add additional tables
		$tables = apply_filters( 'torro_forms_tables', $tables );

		// this ensures that no tables are removed
		return array_merge( $orig_tables, $tables );
	}

	/**
	 * Including files of component
	 */
	private static function includes() {
		require_once( plugin_dir_path( __FILE__ ) . 'core/error.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'core/torro.php' );

		$includes_folder = torro()->path( 'includes/' );
		$core_folder = torro()->path( 'core/' );
		$components_folder = torro()->path( 'components/' );

		// Functions
		require_once( $includes_folder . 'functions.php' );
		require_once( $includes_folder . 'compat.php' );
		require_once( $includes_folder . 'wp-editor.php' );

		// Base classes
		require_once( $core_folder . 'class-post.php' );
		require_once( $core_folder . 'class-form.php' );

		// Abstract instance
		require_once( $core_folder . 'abstract/class-instance.php' );

		// Abstract
		require_once( $core_folder . 'abstract/class-component.php' );
		require_once( $core_folder . 'abstract/class-element.php' );
		require_once( $core_folder . 'abstract/class-settings.php' );
		require_once( $core_folder . 'abstract/class-templatetags.php' );

		// Admin
		require_once( $core_folder . 'menu.php' );
		require_once( $core_folder . 'form-builder.php' );
		require_once( $core_folder . 'settings-page.php' );

		// Settings
		require_once( $core_folder . 'settings/class-settingshandler.php' );
		require_once( $core_folder . 'settings/base-settings/general.php' );

		// Form functions
		require_once( $core_folder . 'form-loader.php' );
		require_once( $core_folder . 'form-process.php' );

		// Base elements
		require_once( $core_folder . 'elements/base-elements/content.php' );
		require_once( $core_folder . 'elements/base-elements/textfield.php' );
		require_once( $core_folder . 'elements/base-elements/textarea.php' );
		require_once( $core_folder . 'elements/base-elements/onechoice.php' );
		require_once( $core_folder . 'elements/base-elements/multiplechoice.php' );
		require_once( $core_folder . 'elements/base-elements/dropdown.php' );
		require_once( $core_folder . 'elements/base-elements/separator.php' );
		require_once( $core_folder . 'elements/base-elements/splitter.php' );

		// Template tags
		require_once( $core_folder . 'templatetags/base-templatetags/global.php' );
		require_once( $core_folder . 'templatetags/base-templatetags/form.php' );

		// Shortcodes
		require_once( $core_folder . 'shortcodes.php' );

		// Components
		require_once( $components_folder . 'actions/component.php' );
		require_once( $components_folder . 'restrictions/component.php' );
		require_once( $components_folder . 'results/component.php' );
	}

	/**
	 * Creates Custom Post Types for Torro Forms
	 *
	 * @since 1.0.0
	 */
	public static function custom_post_types() {
		if ( self::$post_types_registered ) {
			return;
		}

		$settings = torro_get_settings( 'general' );

		$slug = 'forms';
		if ( array_key_exists( 'slug', $settings  ) ) {
			$slug = $settings[ 'slug' ];
		}

		/**
		 * Post Types
		 */
		$args_post_type = array(
			'labels'				=> array(
				'name'					=> __( 'Forms', 'torro-forms' ),
				'singular_name'			=> __( 'Form', 'torro-forms' ),
				'add_new'				=> __( 'Add New', 'torro-forms' ),
				'add_new_item'			=> __( 'Add New Form', 'torro-forms' ),
				'edit_item'				=> __( 'Edit Form', 'torro-forms' ),
				'new_item'				=> __( 'New Form', 'torro-forms' ),
				'view_item'				=> __( 'View Form', 'torro-forms' ),
				'search_items'			=> __( 'Search Forms', 'torro-forms' ),
				'not_found'				=> __( 'No forms found.', 'torro-forms' ),
				'not_found_in_trash'	=> __( 'No forms found in Trash.', 'torro-forms' ),
				'all_items'				=> __( 'All Forms', 'torro-forms' ),
				'archives'				=> __( 'Form Archives', 'torro-forms' ),
				'insert_into_item'		=> __( 'Insert into form', 'torro-forms' ),
				'uploaded_to_this_item'	=> __( 'Uploaded to this form', 'torro-forms' ),
				'filter_items_list'		=> __( 'Filter forms list', 'torro-forms' ),
				'items_list_navigation'	=> __( 'Forms list navigation', 'torro-forms' ),
				'items_list'			=> __( 'Forms list', 'torro-forms' ),
				'menu_name'				=> __( 'Forms', 'torro-forms' ),
			),
			'public'				=> true,
			'has_archive'			=> true,
			'supports'				=> array( 'title' ),
			'show_in_menu'			=> true,
			'show_in_nav_menus'		=> false,
			'rewrite'				=> array( 'slug' => $slug, 'with_front' => true ),
			'menu_position'			=> 50,
		);

		register_post_type( 'torro-forms', $args_post_type );

		/**
		 * Categories
		 */
		$args_taxonomy = array(
			'show_in_nav_menus'		=> true,
			'hierarchical'			=> true,
			'labels'				=> array(
				'name'					=> __( 'Categories', 'torro-forms' ),
				'singular_name'			=> __( 'Category', 'torro-forms' ),
				'search_items'			=> __( 'Search Categories', 'torro-forms' ),
				'popular_items'			=> __( 'Popular Categories', 'torro-forms' ),
				'all_items'				=> __( 'All Categories', 'torro-forms' ),
				'parent_item'			=> __( 'Parent Category', 'torro-forms' ),
				'parent_item_colon'		=> __( 'Parent Category:', 'torro-forms' ),
				'edit_item'				=> __( 'Edit Category', 'torro-forms' ),
				'view_item'				=> __( 'View Category', 'torro-forms' ),
				'update_item'			=> __( 'Update Category', 'torro-forms' ),
				'add_new_item'			=> __( 'Add New Category', 'torro-forms' ),
				'new_item_name'			=> __( 'New Category', 'torro-forms' ),
				'not_found'				=> __( 'No categories found.', 'torro-forms' ),
				'no_terms'				=> __( 'No categories', 'torro-forms' ),
				'items_list_navigation'	=> __( 'Categories list navigation', 'torro-forms' ),
				'items_list'			=> __( 'Categories list', 'torro-forms' ),
				'menu_name'				=> __( 'Categories', 'torro-forms' ),
			),
			'show_ui'				=> true,
			'query_var'				=> true,
			'rewrite'				=> true,
		);

		register_taxonomy( 'torro-forms-categories', array( 'torro-forms' ), $args_taxonomy );

		self::$post_types_registered = true;
	}

	/**
	 * Adding CSS Classes to body
	 *
	 * @param array $classes Classes for body
	 *
	 * @return array $classes Classes for body
	 */
	public static function add_body_class( $classes ) {
		global $post;

		// Check if we are on the right place
		if ( ! is_a( $post, 'WP_Post' ) || 'torro-forms' !== $post->post_type ) {
			return $classes;
		}

		$classes[] = 'torro-form';
		$classes[] = 'torro-form-' . $post->ID;

		return $classes;
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public static function enqueue_admin_styles() {
		wp_enqueue_style( 'torro-icons', torro()->asset_url( 'icons', 'css' ) );
	}

	/**
	 * Registers and enqueues plugin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public static function enqueue_frontend_styles() {
		wp_enqueue_style( 'torro-frontend', torro()->asset_url( 'frontend', 'css' ) );
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 * @since 1.0.0
	 */
	public static function activate( $network_wide ) {
		self::log( 'Activated Plugin' );

		self::register_tables();
		self::setup();

		self::custom_post_types();

		add_action( 'shutdown', 'flush_rewrite_rules' );
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 * @since 1.0.0
	 */
	public static function deactivate( $network_wide ) {
		delete_option( 'torro_is_installed' );

		add_action( 'shutdown', 'flush_rewrite_rules' );

		self::log( 'Deactivated plugin' );
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
		global $wpdb;

		$tables = self::get_tables();

		// Checking if all tables are existing
		foreach ( $tables AS $table ) {
			$table_name = 'torro_' . $table;
			if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) !== $table_name ) {
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
		$script_db_version = '1.0.3';
		$current_db_version  = get_option( 'torro_db_version' );

		self::admin_notice( 'Current DB version: ' . $current_db_version );

		if ( false !== get_option( 'questions_db_version' ) ) {
			require_once( 'includes/updates/to-awesome-forms.php' );
			torro_questions_to_awesome_forms();

			self::log( 'Updated to DB version  1.0.1' );
		}

		if ( false !== get_option( 'af_db_version' ) ) {
			require_once( 'includes/updates/to-torro-forms.php' );
			awesome_forms_to_torro_forms();

			self::admin_notice( 'Updated to Torro forms 1.0.2' );
			self::log( 'Updated to DB version  1.0.2' );
		}

		if ( false === get_option( 'torro_db_version' ) || false === self::is_installed() || true === version_compare( $current_db_version, $script_db_version, '<' )  ) {
			self::install_tables();
			update_option( 'torro_db_version', $script_db_version );

			self::admin_notice( 'Updated to DB version  1.0.3' );
			self::log( 'Updated to DB version  1.0.3' );
		}

		if( true === version_compare( $current_db_version, '1.0.3', '<' ) ){
			require_once( 'includes/updates/to_1.0.3.php' );
			awesome_forms_to_1_0_3();
			update_option( 'torro_db_version', $script_db_version );

			self::admin_notice( 'Updated to DB version  1.0.3' );
			self::log( 'Updated to DB version  1.0.3' );
		}
	}

	/**
	 * Installing tables
	 */
	private static function install_tables() {
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$charset_collate = self::get_charset_collate();

		$sql = "CREATE TABLE $wpdb->torro_elements (
		id int(11) NOT NULL AUTO_INCREMENT,
		form_id int(11) NOT NULL,
		label text NOT NULL,
		sort int(11) NOT NULL,
		type char(50) NOT NULL,
		UNIQUE KEY id (id)
		) ENGINE = INNODB " . $charset_collate . ";";

		dbDelta( $sql );

		$sql = "CREATE TABLE $wpdb->torro_element_answers (
		id int(11) NOT NULL AUTO_INCREMENT,
		element_id int(11) NOT NULL,
		section char(100) NOT NULL,
		answer text NOT NULL,
		sort int(11) NOT NULL,
		UNIQUE KEY id (id)
		) ENGINE = INNODB " . $charset_collate . ";";

		dbDelta( $sql );

		$sql = "CREATE TABLE $wpdb->torro_results (
		id int(11) NOT NULL AUTO_INCREMENT,
		form_id int(11) NOT NULL,
		user_id int(11) NOT NULL,
		timestamp int(11) NOT NULL,
		remote_addr char(15) NOT NULL,
		cookie_key char(50) NOT NULL,
		UNIQUE KEY id (id)
		) ENGINE = INNODB " . $charset_collate . ";";

		dbDelta( $sql );

		$sql = "CREATE TABLE $wpdb->torro_result_values (
		id int(11) NOT NULL AUTO_INCREMENT,
		result_id int(11) NOT NULL,
		element_id int(11) NOT NULL,
		value text NOT NULL,
		UNIQUE KEY id (id)
		) ENGINE = INNODB " . $charset_collate . ";";

		dbDelta( $sql );

		$sql = "CREATE TABLE $wpdb->torro_settings (
		id int(11) NOT NULL AUTO_INCREMENT,
		element_id int(11) NOT NULL,
		name text NOT NULL,
		value text NOT NULL,
		UNIQUE KEY id (id)
		) ENGINE = INNODB " . $charset_collate . ";";

		dbDelta( $sql );

		$sql = "CREATE TABLE $wpdb->torro_participants (
		id int(11) NOT NULL AUTO_INCREMENT,
		form_id int(11) NOT NULL,
		user_id int(11) NOT NULL,
		UNIQUE KEY id (id)
		) ENGINE = INNODB " . $charset_collate . ";";

		dbDelta( $sql );

		$sql = "CREATE TABLE $wpdb->torro_email_notifications (
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
	 * Getting charset from DB
	 * @return string
	 */
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
		$wp_upload_dir = WP_CONTENT_DIR . '/uploads';
		$log_dir = trailingslashit( $wp_upload_dir  ) . '/torro-logs';

		if ( ! file_exists( $log_dir ) || ! is_dir( $log_dir ) ) {
			mkdir( $log_dir );
		}

		$file = fopen( $log_dir . '/main.log', 'a' );
		fputs( $file, $message . chr( 13 ) );
		fclose( $file );
	}
}

register_activation_hook( __FILE__, array( 'Torro_Init', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Torro_Init', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( 'Torro_Init', 'uninstall' ) );

function torro_init() {
	Torro_Init::init();
}
add_action( 'plugins_loaded', 'torro_init' );
