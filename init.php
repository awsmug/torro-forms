<?php
/*
Plugin Name: SurveyVal
Plugin URI: http://www.rheinschmiede.de
Description: Create your surveys or polls for WordPress Users.
Version: 1.0
Author: Rheinschmiede
Author URI: http://www.rheonschmiede.de
Author Email: kontakt@rheinschmiede.de
License:

  Copyright 2013 (kontakt@rheinschmiede.de)

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

/*
 * PluginName Core Class for Surveys
 *
 * This class initializes the Plugin.
 *
 * @author rheinschmiede.de, Author <kontakt@rheinschmiede.de>
 * @package PluginName
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2

  Copyright 2013 (kontakt@rheinschmiede.de)

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
 
class SurveVal_Init{
	 
	/**
	 * Initializes the plugin.
	 * @since 1.0.0
	 */
	public static function init() {
		global $sv_plugin_errors, $sv_plugin_errors;
		
		$sv_plugin_errors = array();
		
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
			add_action( 'admin_print_styles', array( __CLASS__, 'register_admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_admin_scripts' ) );
			add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
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
		global $sv_plugin_errors;
		
		//if( !class_exists( 'ImportantClass' ) )
		//	$sv_plugin_errors[] = __( 'Import Class not existing.', 'surveyval-locale' );
	}
	
	/**
	 * Fired when the plugin is activated.
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 * @since 1.0.0 
	 */
	public static function activate( $network_wide ) {
		global $wpdb;
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		$table_questions = $wpdb->prefix . 'surveyval_questions';
		$table_answers = $wpdb->prefix . 'surveyval_answers';
		$table_responds = $wpdb->prefix . 'surveyval_responds';
		$table_respond_answers = $wpdb->prefix . 'surveyval_respond_answers';
		$table_settings = $wpdb->prefix . 'surveyval_settings';
		
		$sql = "CREATE TABLE $table_questions (
			id int(11) NOT NULL AUTO_INCREMENT,
			surveyval_id int(11) NOT NULL,
			question text NOT NULL,
			sort int(11) NOT NULL,
			type char(50) NOT NULL,
			UNIQUE KEY id (id)
			)";
			
		dbDelta( $sql );
		
		$sql = "CREATE TABLE $table_answers (
			id int(11) NOT NULL AUTO_INCREMENT,
			question_id int(11) NOT NULL,
			answer text NOT NULL,
			sort int(11) NOT NULL,
			UNIQUE KEY id (id)
			)";
			
		dbDelta( $sql );
		
		$sql = "CREATE TABLE $table_responds (
			id int(11) NOT NULL AUTO_INCREMENT,
			surveyval_id int(11) NOT NULL,
			user_id int(11) NOT NULL,
			timestamp int(11) NOT NULL,
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
		
		update_option( 'surveyval_db_version', '1.0.0' );
		
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
		load_plugin_textdomain( 'surveyval-locale', false, SURVEYVAL_RELATIVE_FOLDER . '/languages' );
		
	} // end plugin_textdomain

	/**
	 * Registers and enqueues admin-specific styles.
	 * @since 1.0.0
	 */
	public static function register_admin_styles() {
		wp_enqueue_style( 'surveyval-admin-styles', SURVEYVAL_URLPATH . '/includes/css/admin.css' );
	
	} // end register_admin_styles

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 * @since 1.0.0
	 */	
	public static function register_admin_scripts() {
		wp_enqueue_script( 'surveyval-admin-script', SURVEYVAL_URLPATH . '/includes/js/admin.js' );
	
	} // end register_admin_scripts
	
	/**
	 * Registers and enqueues plugin-specific styles.
	 * @since 1.0.0
	 */
	public static function register_plugin_styles() {
		wp_enqueue_style( 'surveyval-plugin-styles', SURVEYVAL_URLPATH . '/includes/css/display.css' );
	
	} // end register_plugin_styles
	
	/**
	 * Registers and enqueues plugin-specific scripts.
	 * @since 1.0.0
	 */
	public static function register_plugin_scripts() {
		wp_enqueue_script( 'surveyval-plugin-script',  SURVEYVAL_URLPATH . '/includes/js/display.js' );
	
	} // end register_plugin_scripts
	
	/**
	 * Defining Constants for Use in Plugin
	 * @since 1.0.0
	 */
	public static function constants(){
		define( 'SURVEYVAL_FOLDER', 		self::get_folder() );
		define( 'SURVEYVAL_RELATIVE_FOLDER', 	substr( SURVEYVAL_FOLDER, strlen( WP_PLUGIN_DIR ), strlen( SURVEYVAL_FOLDER ) ) );  
		define( 'SURVEYVAL_URLPATH', 		self::get_url_path() );
		define( 'SURVEYVAL_COMPONENTFOLDER', SURVEYVAL_FOLDER . '/components' );
	}
	
	/**
	 * Getting include files
	 * @since 1.0.0
	 */
	public static function includes(){
		// Loading functions
		include( SURVEYVAL_FOLDER . '/functions.php' );
		
		// Loading Skip
		//include( SURVEYVAL_FOLDER . '/includes/skip/loader.php' ); 
		//skip_start();
	}

	/**
	 * Loading components dynamical
	 * @since 1.0.0
	 */
	public static function load_components(){
		// Loading Components
		include( SURVEYVAL_FOLDER . '/components/component.php' );
		include( SURVEYVAL_FOLDER . '/components/admin/admin.php' );
		include( SURVEYVAL_FOLDER . '/components/core/core.php' );
	}
	
	/**
	* Getting URL
	* @since 1.0.0
	*/
	private static function get_url_path(){
		$sub_path = substr( SURVEYVAL_FOLDER, strlen( ABSPATH ), ( strlen( SURVEYVAL_FOLDER ) - 11 ) );
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
		global $sv_plugin_errors, $sv_plugin_errors; 
		
		if( count( $sv_plugin_errors ) > 0 ):
				foreach( $sv_plugin_errors AS $error )
					echo '<div class="error"><p>' . $error . '</p></div>';
		endif;
		
		if( count( $sv_plugin_errors ) > 0 ):
				foreach( $sv_plugin_errors AS $notice )
					echo '<div class="updated"><p>' . $notice . '</p></div>';
		endif;	
	} 
	
} // end class

SurveVal_Init::init();


