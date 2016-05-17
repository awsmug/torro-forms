<?php
/*
Plugin Name: Torro Forms
Plugin URI:  http://torro-forms.com
Description: Torro Forms is an extendable WordPress form builder with Drag & Drop functionality, chart evaluation and more - with native WordPress look and feel.
Version:     1.0.0-beta.1
Author:      Awesome UG
Author URI:  http://www.awesome.ug
License:     GNU General Public License v3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: torro-forms
Domain Path: /languages/
Tags:        forms, form builder, surveys, polls, votes, charts, api
*/
/**
 * Init: Torro_Init class
 *
 * @package TorroForms
 * @subpackage Init
 * @version 1.0.0-beta.1
 * @since 1.0.0-beta.1
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
		add_action( 'admin_notices', array( __CLASS__, 'import_forms' ) );
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
			'element_settings',
			'results',
			'result_values',
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
		require_once( plugin_dir_path( __FILE__ ) . 'core/error.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'core/ajax.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'core/torro.php' );

		$main_folder   = torro()->get_path();
		$includes_folder   = torro()->get_path( 'includes/' );
		$core_folder       = torro()->get_path( 'core/' );
		$components_folder = torro()->get_path( 'components/' );

		// Functions
		require_once( $includes_folder . 'functions.php' );
		require_once( $includes_folder . 'compat.php' );
		require_once( $includes_folder . 'form-media.php' );
		require_once( $includes_folder . 'wp-editor.php' );

		// General Models
		require_once( $core_folder . 'models/class-base.php' );
		require_once( $core_folder . 'models/class-component.php' );
		require_once( $core_folder . 'models/class-settings.php' );
		require_once( $core_folder . 'models/class-extension.php' );
		require_once( $core_folder . 'models/class-templatetags.php' );

		// Instance Models
		require_once( $core_folder . 'models/class-instance-base.php' );
		require_once( $core_folder . 'models/class-form.php' );
		require_once( $core_folder . 'models/class-container.php' );
		require_once( $core_folder . 'models/class-element.php' );
		require_once( $core_folder . 'models/class-element-answer.php' );
		require_once( $core_folder . 'models/class-element-setting.php' );
		require_once( $core_folder . 'models/class-result.php' );
		require_once( $core_folder . 'models/class-result-value.php' );
		require_once( $core_folder . 'models/class-participant.php' );
		require_once( $core_folder . 'models/class-email-notification.php' );

		// Admin
		require_once( $core_folder . 'menu.php' );
		require_once( $core_folder . 'form-builder.php' );
		require_once( $core_folder . 'settings-page.php' );
		require_once( $core_folder . 'admin-notices.php' );

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
		require_once( $core_folder . 'elements/base-elements/media.php' );
		require_once( $core_folder . 'elements/base-elements/separator.php' );

		// Template tags
		require_once( $core_folder . 'templatetags/base-templatetags/global.php' );
		require_once( $core_folder . 'templatetags/base-templatetags/form.php' );

		// Shortcodes
		require_once( $core_folder . 'shortcodes.php' );

		// Vendor
		if( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
			require_once( $main_folder . 'vendor/easydigitaldownloads/EDD-License-handler/EDD_SL_Plugin_Updater.php' );
		}

		// Components
		require_once( $components_folder . 'actions/component.php' );
		require_once( $components_folder . 'form-settings/component.php' );
		require_once( $components_folder . 'results/component.php' );

		// Form functions
		require_once( $core_folder . 'form-controller.php' );
		require_once( $core_folder . 'form-controller-cache.php' );

		do_action( '_torro_loaded' );
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
		if ( ! is_a( $post, 'WP_Post' ) || 'torro_form' !== $post->post_type ) {
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
	 * Setting up base plugin data
	 *
	 * @since 1.0.0
	 */
	private static function setup() {
		$script_db_version  = '1.0.7';
		$current_db_version = get_option( 'torro_db_version' );


		// Oh no, somebody has used the beta... ;)
		if ( false !== $current_db_version ) {
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

			// Upgrading from Torro DB version 1.0.4 to 1.0.5
			if ( true === version_compare( $current_db_version, '1.0.5', '<' ) ) {
				require_once( 'includes/updates/to_1.0.5.php' );
				torro_forms_to_1_0_5();
				update_option( 'torro_db_version', '1.0.5' );
			}

			// Upgrading from Torro DB version 1.0.5 to 1.0.6
			if ( true === version_compare( $current_db_version, '1.0.6', '<' ) ) {
				require_once( 'includes/updates/to_1.0.6.php' );
				torro_forms_to_1_0_6();
				update_option( 'torro_db_version', '1.0.6' );
			}

			// Upgrading from Torro DB version 1.0.6 to 1.0.7
			if ( true === version_compare( $current_db_version, '1.0.7', '<' ) ) {
				require_once( 'includes/updates/to_1.0.7.php' );
				torro_forms_to_1_0_7();
				update_option( 'torro_db_version', '1.0.7' );
			}
		} elseif ( false === self::is_installed() ) {
			// Fresh Torro DB install
			self::install_tables();
			update_option( 'torro_db_version', $script_db_version );
		}
	}

	/**
	 * Importing other forms
	 *
	 * @since 1.0.0
	 */
	public static function import_forms(){
		if( ! is_admin() ) {
			return;
		}

		// Importing from Questions to Torro Forms
		if ( false !== get_option( 'questions_db_version' ) && false === get_option( 'torro_copied_from_questions' ) && false === get_option( 'torro_denied_copy_from_questions' ) && ! isset( $_REQUEST[ 'import_from_questions' ] ) ) {
			$allow_copy_url = add_query_arg( 'import_from_questions', 'yes', admin_url() );
			$deny_copy_url = add_query_arg( 'import_from_questions', 'no', admin_url() );
			torro()->admin_notices()->add( 'questions_detected', sprintf( __( 'Questions survey data have been detected, do you want to import surveys to Torro Forms? <a href="%s">Yes</a> | <a href="%s">No</a>', 'torro-forms' ), $allow_copy_url, $deny_copy_url ) );
		}

		if( isset( $_REQUEST[ 'import_from_questions' ] ) ) {
			if ( 'yes' === $_REQUEST[ 'import_from_questions' ] ) {
				require_once( 'includes/updates/import-from-questions.php' );
				torro_import_from_questions();
				update_option( 'torro_copied_from_questions', true );
			} elseif( 'no' === $_REQUEST[ 'import_from_questions' ] ) {
				update_option( 'torro_denied_copy_from_questions', true );
			}
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
	id int(11) unsigned NOT NULL auto_increment,
	form_id bigint(20) unsigned NOT NULL,
	label text NOT NULL,
	sort int(11) NOT NULL,
	PRIMARY KEY (id)
) $charset_collate;
CREATE TABLE $wpdb->torro_elements (
	id int(11) unsigned NOT NULL auto_increment,
	container_id int(11) unsigned NOT NULL,
	label text NOT NULL,
	sort int(11) NOT NULL,
	type char(50) NOT NULL,
	PRIMARY KEY (id)
) $charset_collate;
CREATE TABLE $wpdb->torro_element_answers (
	id int(11) unsigned NOT NULL auto_increment,
	element_id int(11) unsigned NOT NULL,
	section char(100) NOT NULL,
	answer text NOT NULL,
	sort int(11) NOT NULL,
	PRIMARY KEY (id)
) $charset_collate;
CREATE TABLE $wpdb->torro_element_settings (
	id int(11) unsigned NOT NULL auto_increment,
	element_id int(11) unsigned NOT NULL,
	name text NOT NULL,
	value text NOT NULL,
	PRIMARY KEY (id)
) $charset_collate;
CREATE TABLE $wpdb->torro_results (
	id int(11) unsigned NOT NULL auto_increment,
	form_id bigint(20) unsigned NOT NULL,
	user_id bigint(20) unsigned NOT NULL,
	timestamp int(11) unsigned NOT NULL,
	remote_addr char(15) NOT NULL,
	cookie_key char(50) NOT NULL,
	PRIMARY KEY (id)
) $charset_collate;
CREATE TABLE $wpdb->torro_result_values (
	id int(11) unsigned NOT NULL auto_increment,
	result_id int(11) unsigned NOT NULL,
	element_id int(11) unsigned NOT NULL,
	value text NOT NULL,
	PRIMARY KEY (id)
) $charset_collate;
CREATE TABLE $wpdb->torro_participants (
	id int(11) unsigned NOT NULL auto_increment,
	form_id bigint(20) unsigned NOT NULL,
	user_id bigint(20) unsigned NOT NULL,
	PRIMARY KEY (id)
) $charset_collate;
CREATE TABLE $wpdb->torro_email_notifications (
	id int(11) unsigned NOT NULL auto_increment,
	form_id bigint(20) unsigned NOT NULL,
	notification_name text NOT NULL,
	from_name text NOT NULL,
	from_email text NOT NULL,
	to_name text NOT NULL,
	to_email text NOT NULL,
	subject text NOT NULL,
	message text NOT NULL,
	PRIMARY KEY (id)
) $charset_collate;";

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
			'show_ui'				=> true,
			'show_in_menu'			=> true,
			'show_in_menu'			=> true,
			'show_in_nav_menus'		=> false,
			'hierarchical'			=> false,
			'has_archive'			=> false,
			'supports'				=> array( 'title' ),
			'rewrite'				=> array(
				'slug'					=> $slug,
				'with_front'			=> false,
				'ep_mask'				=> EP_PERMALINK,
			),
			'menu_position'			=> 50,
		);

		register_post_type( 'torro_form', $args_post_type );

		/**
		 * Categories
		 */
		$args_taxonomy = array(
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
			'public'				=> false,
			'show_ui'				=> true,
			'show_in_menu'			=> true,
			'show_in_nav_menus'		=> false,
			'show_tagcloud'			=> false,
			'show_admin_column'		=> true,
			'hierarchical'			=> true,
			'rewrite'				=> array(
				'slug'					=> 'form-categories',
				'with_front'			=> false,
				'ep_mask'				=> EP_NONE,
			),
		);

		register_taxonomy( 'torro_form_category', array( 'torro_form' ), $args_taxonomy );

		self::$post_types_registered = true;
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

		if ( $network_wide ) {
			foreach ( wp_get_sites() as $site ) {
				switch_to_blog( $site['blog_id'] );
				self::setup_single_site();
				restore_current_blog();
			}
			add_action( 'shutdown', array( __CLASS__, 'flush_network_rewrite_rules' ) );
		} else {
			self::setup_single_site();
			add_action( 'shutdown', 'flush_rewrite_rules' );
		}
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
		if ( $network_wide ) {
			add_action( 'shutdown', array( __CLASS__, 'flush_network_rewrite_rules' ) );
		} else {
			add_action( 'shutdown', 'flush_rewrite_rules' );
		}
	}

	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * Since the uninstallation process is unaware whether the plugin was active network-wide,
	 * we simply check if we're on a multisite.
	 *
	 * If so, we (maybe) uninstall the plugin from all sites in the entire multisite (not only the current network). The `uninstall_single_site()` function will do the necessary checks whether the plugin
	 * actually needs to be uninstalled on that site.
	 *
	 * @since 1.0.0
	 */
	public static function uninstall() {
		$globally = is_multisite();

		self::register_tables();

		if ( $globally ) {
			foreach ( wp_get_sites( array( 'network_id' => false ) ) as $site ) {
				switch_to_blog( $site['blog_id'] );
				self::uninstall_single_site();
				restore_current_blog();
			}
		} else {
			self::uninstall_single_site();
		}
	}

	private static function setup_single_site() {
		self::setup();
		self::custom_post_types();
	}

	private static function uninstall_single_site() {
		global $wpdb;

		// do not uninstall if version option is not set (meaning plugin was not active here)
		$current_db_version = get_option( 'torro_db_version' );
		if ( false === $current_db_version ) {
			// return;
		}

		// do not uninstall if hard uninstall option is not enabled
		$do_hard_uninstall = get_option( 'torro_settings_general_hard_uninstall' );
		if ( '1' !== $do_hard_uninstall && ( ! is_array( $do_hard_uninstall ) || '1' !== $do_hard_uninstall[0] ) ) {
			// return;
		}

		// delete custom tables
		$tables = self::get_tables();
		foreach ( $tables as $table ) {
			$table_name = 'torro_' . $table;
			$wpdb->query( "DROP TABLE {$wpdb->$table_name}" );
		}

		// delete form posts
		$form_ids = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type = 'torro_form'" );
		foreach ( $form_ids as $form_id ) {
			wp_delete_post( $form_id, true );
		}

		// delete form posts
		$form_ids = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type = 'af-forms'" );
		foreach ( $form_ids as $form_id ) {
			wp_delete_post( $form_id, true );
		}

		// delete form posts
		$form_ids = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type = 'awesome-forms'" );
		foreach ( $form_ids as $form_id ) {
			wp_delete_post( $form_id, true );
		}

		// delete form posts
		$form_ids = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type = 'torro-forms'" );
		foreach ( $form_ids as $form_id ) {
			wp_delete_post( $form_id, true );
		}

		// delete form category terms
		$form_category_ids = $wpdb->get_col( "SELECT term_id FROM $wpdb->term_taxonomy WHERE taxonomy = 'torro_form_category'" );
		foreach ( $form_category_ids as $form_category_id ) {
			wp_delete_term( $form_category_id, 'torro_form_category' );
		}

		// delete options
		$option_names = $wpdb->get_col( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'torro_settings_%'" );
		foreach ( $option_names as $option_name ) {
			delete_option( $option_name );
		}

		delete_option( 'torro_db_version' );
		delete_option( 'torro_copied_from_questions' );
		delete_option( 'torro_denied_copy_from_questions' );
	}

	public static function flush_network_rewrite_rules() {
		foreach ( wp_get_sites() as $site ) {
			switch_to_blog( $site['blog_id'] );
			flush_rewrite_rules();
			restore_current_blog();
		}
	}
}

function torro_load( $callback ) {
	if ( did_action( '_torro_loaded' ) || doing_action( '_torro_loaded' ) ) {
		call_user_func( $callback );
		return;
	}

	add_action( '_torro_loaded', $callback, 10, 0 );
}

add_action( 'plugins_loaded', array( 'Torro_Init', 'init' ) );

register_activation_hook( __FILE__, array( 'Torro_Init', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Torro_Init', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( 'Torro_Init', 'uninstall' ) );
