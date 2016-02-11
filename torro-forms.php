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
	 * @var bool $tables_registered
	 * @since 1.0.0
	 */
	private static $tables_registered = false;

	/**
	 * @var bool $post_types_registered
	 * @since 1.0.0
	 */
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

	/**
	 * Registering Tables to $wpdb
	 *
	 * @since 1.0.0
	 */
	private static function register_tables() {
		global $wpdb;

		if ( self::$tables_registered ) {
			return;
		}

		self::$tables_registered = true;

		$tables = self::get_tables();

		foreach ( $tables as $table ) {
			$table_name = 'torro_' . $table;

			$wpdb->tables[]    = $table_name;
			$wpdb->$table_name = $wpdb->prefix . $table_name;
		}
	}

	/**
	 * Getting all DB tables form plugin
	 *
	 * @return array
	 */
	private static function get_tables() {
		$tables = $orig_tables = array(
			'containers',
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
	 *
	 * @since 1.0.0
	 */
	private static function includes() {
		require_once( plugin_dir_path( __FILE__ ) . 'core/models/class-error.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'core/ajax.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'core/torro.php' );

		$main_folder   = torro()->get_path();
		$includes_folder   = torro()->get_path( 'includes/' );
		$core_folder       = torro()->get_path( 'core/' );
		$components_folder = torro()->get_path( 'components/' );

		// Functions
		require_once( $includes_folder . 'functions.php' );
		require_once( $includes_folder . 'compat.php' );
		require_once( $includes_folder . 'wp-editor.php' );

		// Models
		require_once( $core_folder . 'models/class-base.php' );
		require_once( $core_folder . 'models/class-post.php' );
		require_once( $core_folder . 'models/class-form.php' );
		require_once( $core_folder . 'models/class-container.php' );
		require_once( $core_folder . 'models/class-element-answer.php' );
		require_once( $core_folder . 'models/class-element-setting.php' );
		require_once( $core_folder . 'models/class-participant.php' );
		require_once( $core_folder . 'models/class-component.php' );
		require_once( $core_folder . 'models/class-element.php' );
		require_once( $core_folder . 'models/class-settings.php' );
		require_once( $core_folder . 'models/class-extension.php' );
		require_once( $core_folder . 'models/class-templatetags.php' );

		// Admin
		require_once( $core_folder . 'menu.php' );
		require_once( $core_folder . 'form-builder.php' );
		require_once( $core_folder . 'settings-page.php' );

		// Settings
		require_once( $core_folder . 'settings/class-settingshandler.php' );
		require_once( $core_folder . 'settings/base-settings/general.php' );
		require_once( $core_folder . 'settings/base-settings/extensions.php' );

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

		// Vendor
		if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
			require_once( $main_folder . 'vendor/EDD_SL_Plugin_Updater.php' );
		}

		// Components
		require_once( $components_folder . 'actions/component.php' );
		require_once( $components_folder . 'restrictions/component.php' );
		require_once( $components_folder . 'results/component.php' );

		// Form functions
		require_once( $core_folder . 'form-controller.php' );
	}

	/**
	 * Adding CSS Classes to body
	 *
	 * @param array $classes Classes for body
	 *
	 * @return array $classes Classes for body
	 * @since 1.0.0
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
		wp_enqueue_style( 'torro-icons', torro()->get_asset_url( 'icons', 'css' ) );
	}

	/**
	 * Registers and enqueues plugin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public static function enqueue_frontend_styles() {
		$settings = torro_get_settings( 'general' );

		if( isset( $settings[ 'frontend_css' ] ) && ! is_array( $settings[ 'frontend_css' ] ) ){
			return;
		}

		wp_enqueue_style( 'torro-frontend', torro()->get_asset_url( 'frontend', 'css' ) );
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled
	 *                              or plugin is activated on an individual blog
	 *
	 * @since 1.0.0
	 */
	public static function activate( $network_wide ) {
		self::register_tables();
		self::setup();

		self::custom_post_types();

		add_action( 'shutdown', 'flush_rewrite_rules' );
	}

	/**
	 * Setting up base plugin data
	 *
	 * @since 1.0.0
	 */
	private static function setup() {
		$script_db_version  = '1.0.4';
		$current_db_version = get_option( 'torro_db_version' );

		// Upgrading from Questions to Awesome Forms
		if ( false !== get_option( 'questions_db_version' ) ) {
			require_once( 'includes/updates/to-awesome-forms.php' );
			torro_questions_to_awesome_forms();

			update_option( 'af_db_version', '1.0.1' );
			delete_option( 'questions_db_version' );
		}

		// Upgrading form Awesome Forms to Torro Forms
		if ( false !== get_option( 'af_db_version' ) ) {
			require_once( 'includes/updates/to-torro-forms.php' );
			awesome_forms_to_torro_forms();

			update_option( 'torro_db_version', '1.0.2' );
			delete_option( 'af_db_version' );
		}

		// Upgrading from Torro DB version 1.0.2 to 1.0.3
		if ( true === version_compare( $current_db_version, '1.0.3', '<' ) ) {
			require_once( 'includes/updates/to_1.0.3.php' );
			torro_forms_to_1_0_3();
			update_option( 'torro_db_version', '1.0.3' );
		}

		// Upgrading from Torro DB version 1.0.3 to 1.0.4
		if ( true === version_compare( $current_db_version, '1.0.4', '<' ) ) {
			require_once( 'includes/updates/to_1.0.4.php' );
			torro_forms_to_1_0_4();
			update_option( 'torro_db_version', '1.0.4' );
		}

		// Fresh Torro DB install
		if ( false === $current_db_version || false === self::is_installed() ) {
			self::install_tables();
			update_option( 'torro_db_version', $script_db_version );
		}
	}

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
	 * Installing tables
	 * @since 1.0.0
	 */
	private static function install_tables() {
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$charset_collate = self::get_charset_collate();

		$sql = "CREATE TABLE $wpdb->torro_containers (
		id int(11) NOT NULL AUTO_INCREMENT,
		form_id int(11) NOT NULL,
		label text NOT NULL,
		sort int(11) NOT NULL,
		UNIQUE KEY id (id)
		) ENGINE = INNODB " . $charset_collate . ";";

		dbDelta( $sql );

		$sql = "CREATE TABLE $wpdb->torro_elements (
		id int(11) NOT NULL AUTO_INCREMENT,
		form_id int(11) NOT NULL,
		container_id int(11) NOT NULL,
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
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public static function get_charset_collate() {
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
	 * Creates Custom Post Types for Torro Forms
	 *
	 * @since 1.0.0
	 */
	public static function custom_post_types() {
		if ( self::$post_types_registered ) {
			return;
		}

		// torro_get_settings() might not be available here, so do it plain WordPress
		$slug = get_option( 'torro_settings_general_slug', 'forms' );

		/**
		 * Post Types
		 */
		$args_post_type = array(
			'labels'            => array(
				'name'                  => __( 'Forms', 'torro-forms' ),
				'singular_name'         => __( 'Form', 'torro-forms' ),
				'add_new'               => __( 'Add New', 'torro-forms' ),
				'add_new_item'          => __( 'Add New Form', 'torro-forms' ),
				'edit_item'             => __( 'Edit Form', 'torro-forms' ),
				'new_item'              => __( 'New Form', 'torro-forms' ),
				'view_item'             => __( 'View Form', 'torro-forms' ),
				'search_items'          => __( 'Search Forms', 'torro-forms' ),
				'not_found'             => __( 'No forms found.', 'torro-forms' ),
				'not_found_in_trash'    => __( 'No forms found in Trash.', 'torro-forms' ),
				'all_items'             => __( 'All Forms', 'torro-forms' ),
				'archives'              => __( 'Form Archives', 'torro-forms' ),
				'insert_into_item'      => __( 'Insert into form', 'torro-forms' ),
				'uploaded_to_this_item' => __( 'Uploaded to this form', 'torro-forms' ),
				'filter_items_list'     => __( 'Filter forms list', 'torro-forms' ),
				'items_list_navigation' => __( 'Forms list navigation', 'torro-forms' ),
				'items_list'            => __( 'Forms list', 'torro-forms' ),
				'menu_name'             => __( 'Forms', 'torro-forms' ),
			),
			'public'            => true,
			'has_archive'       => true,
			'supports'          => array( 'title' ),
			'show_in_menu'      => true,
			'show_in_nav_menus' => false,
			'rewrite'           => array( 'slug' => $slug, 'with_front' => true ),
			'menu_position'     => 50,
		);

		register_post_type( 'torro-forms', $args_post_type );

		/**
		 * Categories
		 */
		$args_taxonomy = array(
			'show_in_nav_menus' => true,
			'hierarchical'      => true,
			'labels'            => array(
				'name'                  => __( 'Categories', 'torro-forms' ),
				'singular_name'         => __( 'Category', 'torro-forms' ),
				'search_items'          => __( 'Search Categories', 'torro-forms' ),
				'popular_items'         => __( 'Popular Categories', 'torro-forms' ),
				'all_items'             => __( 'All Categories', 'torro-forms' ),
				'parent_item'           => __( 'Parent Category', 'torro-forms' ),
				'parent_item_colon'     => __( 'Parent Category:', 'torro-forms' ),
				'edit_item'             => __( 'Edit Category', 'torro-forms' ),
				'view_item'             => __( 'View Category', 'torro-forms' ),
				'update_item'           => __( 'Update Category', 'torro-forms' ),
				'add_new_item'          => __( 'Add New Category', 'torro-forms' ),
				'new_item_name'         => __( 'New Category', 'torro-forms' ),
				'not_found'             => __( 'No categories found.', 'torro-forms' ),
				'no_terms'              => __( 'No categories', 'torro-forms' ),
				'items_list_navigation' => __( 'Categories list navigation', 'torro-forms' ),
				'items_list'            => __( 'Categories list', 'torro-forms' ),
				'menu_name'             => __( 'Categories', 'torro-forms' ),
			),
			'show_ui'           => true,
			'query_var'         => true,
			'rewrite'           => true,
		);

		register_taxonomy( 'torro-forms-categories', array( 'torro-forms' ), $args_taxonomy );

		self::$post_types_registered = true;
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled
	 *                              or plugin is activated on an individual blog
	 *
	 * @since 1.0.0
	 */
	public static function deactivate( $network_wide ) {
		delete_option( 'torro_is_installed' );

		add_action( 'shutdown', 'flush_rewrite_rules' );
	}

	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled
	 *                              or plugin is activated on an individual blog
	 *
	 * @since 1.0.0
	 */
	public static function uninstall( $network_wide ) {
	}
}

register_activation_hook( __FILE__, array( 'Torro_Init', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Torro_Init', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( 'Torro_Init', 'uninstall' ) );

function torro_init() {
	Torro_Init::init();
}

add_action( 'plugins_loaded', 'torro_init' );
