<?php
/*
Plugin Name: Questions
Plugin URI: http://www.awesome.ug
Description: Drag & drop your survey/poll with the WordPress Questions plugin.
Version: 1.0.0 beta 3
Author: awesome.ug
Author URI: http://www.awesome.ug
Author Email: contact@awesome.ug
License:

  Copyright 2015 (contact@awesome.ug)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  
*/

if ( !defined( 'ABSPATH' ) ) exit;
 
class Questions_Init{
	/**
	 * Initializes the plugin.
	 * @since 1.0.0
	 */
	public static function init() {
		global $qu_plugin_errors, $qu_plugin_errors;
		
		$qu_plugin_errors = array();
		
		self::constants();
		self::includes();
		self::load_components();
		self::load_textdomain();
		
		// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
		register_activation_hook( __FILE__, array( __CLASS__, 'activate' ) );
		register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivate' ) );
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );

	    // Functions on Frontend
	    if( is_admin() ):
			// Register admin styles and scripts
			add_action( 'plugins_loaded', array( __CLASS__, 'check_requirements' ) );
			add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
			
			add_action( 'admin_print_styles', array( __CLASS__, 'register_admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_admin_scripts' ) );
			
		else:
			// Register plugin styles and scripts
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_plugin_styles' ) );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_plugin_scripts' ) );
		endif;
	} // end constructor
	
	/**
	 * Checking Requirements and adding Error Messages.
	 * @since 1.0.0 
	 */
	public static function check_requirements(){
		global $qu_plugin_errors;
	}
	
	/**
	 * Fired when the plugin is activated.
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 * @since 1.0.0 
	 */
	public static function activate( $network_wide ) {
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
			)";
			
		dbDelta( $sql );
		
		$sql = "CREATE TABLE $table_answers (
			id int(11) NOT NULL AUTO_INCREMENT,
			question_id int(11) NOT NULL,
			section char(100) NOT NULL,
			answer text NOT NULL,
			sort int(11) NOT NULL,
			UNIQUE KEY id (id)
			)";
			
		dbDelta( $sql );
		
		$sql = "CREATE TABLE $table_responds (
			id int(11) NOT NULL AUTO_INCREMENT,
			questions_id int(11) NOT NULL,
			user_id int(11) NOT NULL,
			timestamp int(11) NOT NULL,
			remote_addr char(15) NOT NULL,
			cookie_key char(30) NOT NULL,
			UNIQUE KEY id (id)
			)";
			
		dbDelta( $sql );
		
		$sql = "CREATE TABLE $table_respond_answers (
			id int(11) NOT NULL AUTO_INCREMENT,
			respond_id int(11) NOT NULL,
			question_id int(11) NOT NULL,
			value text NOT NULL,
			UNIQUE KEY id (id)
			)";
			
		dbDelta( $sql );
		
		$sql = "CREATE TABLE $table_settings (
			id int(11) NOT NULL AUTO_INCREMENT,
			question_id int(11) NOT NULL,
			name text NOT NULL,
			value text NOT NULL,
			UNIQUE KEY id (id)
			)";
			
		dbDelta( $sql );
		
		$sql = "CREATE TABLE $table_participiants (
			id int(11) NOT NULL AUTO_INCREMENT,
			survey_id int(11) NOT NULL,
			user_id int(11) NOT NULL,
			UNIQUE KEY id (id)
			)";
			
		dbDelta( $sql );
		
		update_option( 'questions_db_version', '1.1.0' );
		
	} // end activate
	
	/**
	 * Fired when the plugin is deactivated.
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	public static function deactivate( $network_wide ) {
	} // end deactivate
	
	/**
	 * Fired when the plugin is uninstalled.
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 * @since 1.0.0 
	 */
	public static function uninstall( $network_wide ) {
	} // end uninstall

	/**
	 * Loads the plugin text domain for translation.
	 * @since 1.0.0
	 */
	public static function load_textdomain() {
		load_plugin_textdomain( 'questions-locale', FALSE, QUESTIONS_RELATIVE_FOLDER . '/languages' );
		
	} // end plugin_textdomain

	/**
	 * Registers and enqueues admin-specific styles.
	 * @since 1.0.0
	 */
	public static function register_admin_styles() {
		wp_enqueue_style( 'questions-admin-styles', QUESTIONS_URLPATH . '/includes/css/admin.css' );
		wp_enqueue_style( 'questions-admin-fonts', QUESTIONS_URLPATH . '/includes/css/fonts.css' );
	} // end register_admin_styles

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 * @since 1.0.0
	 */	
	public static function register_admin_scripts() {
	} // end register_admin_scripts
	
	/**
	 * Registers and enqueues plugin-specific styles.
	 * @since 1.0.0
	 */
	public static function register_plugin_styles() {
		wp_enqueue_style( 'questions-plugin-styles', QUESTIONS_URLPATH . '/includes/css/display.css' );
		
	} // end register_plugin_styles
	
	/**
	 * Registers and enqueues plugin-specific scripts.
	 * @since 1.0.0
	 */
	public static function register_plugin_scripts() {
	} // end register_plugin_scripts
	
	/**
	 * Defining Constants for Use in Plugin
	 * @since 1.0.0
	 */
	public static function constants(){
		define( 'QUESTIONS_FOLDER', 		self::get_folder() );
		define( 'QUESTIONS_RELATIVE_FOLDER', 	substr( QUESTIONS_FOLDER, strlen( WP_PLUGIN_DIR ), strlen( QUESTIONS_FOLDER ) ) );  
		define( 'QUESTIONS_URLPATH', 		self::get_url_path() );
		define( 'QUESTIONS_COMPONENTFOLDER', QUESTIONS_FOLDER . '/components' );
	}
	
	/**
	 * Getting include files
	 * @since 1.0.0
	 */
	public static function includes(){
		// Loading functions
		include( QUESTIONS_FOLDER . '/functions.php' );
	}

	/**
	 * Loading components dynamical
	 * @since 1.0.0
	 */
	public static function load_components(){
		// Loading Components
		include( QUESTIONS_FOLDER . '/components/component.php' );
		include( QUESTIONS_FOLDER . '/components/survey.php' );
		include( QUESTIONS_FOLDER . '/components/element.php' );
		include( QUESTIONS_FOLDER . '/components/admin/admin.php' );
		include( QUESTIONS_FOLDER . '/components/core/core.php' );
		include( QUESTIONS_FOLDER . '/components/elements/elements.php' );
		include( QUESTIONS_FOLDER . '/components/charts/charts.php' );
	}
	
	/**
	* Getting URL
	* @since 1.0.0
	*/
	private static function get_url_path(){
		$slashed_folder = str_replace( '\\', '/', QUESTIONS_FOLDER ); // Replacing backslashes width slashes vor windows installations
		$sub_path = substr( $slashed_folder, strlen( ABSPATH ), ( strlen( $slashed_folder ) - 11 ) );
		$script_url = get_bloginfo( 'wpurl' ) . '/' . $sub_path;
		return $script_url;
	}
	
	/**
	* Getting Folder
	* @since 1.0.0
	*/
	private static function get_folder(){
		$sub_folder = substr( dirname(__FILE__), strlen( ABSPATH ), ( strlen( dirname(__FILE__) ) - strlen( ABSPATH ) ) );
		$script_folder = ABSPATH . $sub_folder;
		return $script_folder;
	}
	
	/**
	* Showing Errors
	* @since 1.0.0
	*/
	public static function admin_notices(){
		global $qu_plugin_errors, $qu_plugin_errors; 
		
		if( count( $qu_plugin_errors ) > 0 ):
				foreach( $qu_plugin_errors AS $error )
					echo '<div class="error"><p>' . $error . '</p></div>';
		endif;
		
		if( count( $qu_plugin_errors ) > 0 ):
				foreach( $qu_plugin_errors AS $notice )
					echo '<div class="updated"><p>' . $notice . '</p></div>';
		endif;	
	} 
	
} // end class

Questions_Init::init();


