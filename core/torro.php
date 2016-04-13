<?php
/**
 * Torro Class as general access point for all class instances and basic functionality.
 *
 * This class instance is returned by the `torro()` function.
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 1.0.0alpha1
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Torro {
	/**
	 * Instance
	 *
	 * @var null|Torro
	 * @since 1.0.0
	 */
	private static $instance = null;

	/**
	 * Singleton
	 *
	 * @since 1.0.0
	 * @return Torro
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Plugin Filename
	 *
	 * @var string
	 * @since 1.0.0
	 */
	private $plugin_file = '';

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->plugin_file = dirname( dirname( __FILE__ ) ) . '/torro-forms.php';

		// load manager classes
		require_once( $this->get_path( 'core/managers/class-manager.php' ) );
		require_once( $this->get_path( 'core/managers/class-components-manager.php' ) );
		require_once( $this->get_path( 'core/managers/class-form-settings-manager.php' ) );
		require_once( $this->get_path( 'core/managers/class-settings-manager.php' ) );
		require_once( $this->get_path( 'core/managers/class-templatetags-manager.php' ) );
		require_once( $this->get_path( 'core/managers/class-actions-manager.php' ) );
		require_once( $this->get_path( 'core/managers/class-access-controls-manager.php' ) );
		require_once( $this->get_path( 'core/managers/class-result-handlers-manager.php' ) );
		require_once( $this->get_path( 'core/managers/class-extensions-manager.php' ) );

		// load instance manager classes
		require_once( $this->get_path( 'core/managers/class-instance-manager.php' ) );
		require_once( $this->get_path( 'core/managers/class-forms-manager.php' ) );
		require_once( $this->get_path( 'core/managers/class-containers-manager.php' ) );
		require_once( $this->get_path( 'core/managers/class-elements-manager.php' ) );
		require_once( $this->get_path( 'core/managers/class-element-answer-manager.php' ) );
		require_once( $this->get_path( 'core/managers/class-element-setting-manager.php' ) );
		require_once( $this->get_path( 'core/managers/class-results-manager.php' ) );
		require_once( $this->get_path( 'core/managers/class-result-values-manager.php' ) );
		require_once( $this->get_path( 'core/managers/class-participants-manager.php' ) );

		// load additional manager classes
		require_once( $this->get_path( 'core/managers/class-admin-notices-manager.php' ) );

		add_action( 'admin_init', array( $this, 'admin_notices' ) );
	}

	/**
	 * Forms keychain function
	 *
	 * @return null|Torro_Forms_Manager
	 * @since 1.0.0
	 */
	public function forms() {
		return Torro_Forms_Manager::instance();
	}

	/**
	 * Containers keychain function
	 *
	 * @return null|Torro_Containers_Manager
	 * @since 1.0.0
	 */
	public function containers(){
		return Torro_Containers_Manager::instance();
	}

	/**
	 * Elements keychain function
	 *
	 * @return null|Torro_Form_Elements_Manager
	 * @since 1.0.0
	 */
	public function elements() {
		return Torro_Form_Elements_Manager::instance();
	}

	/**
	 * Element setting keychain function
	 *
	 * @return null|Torro_Element_Answer_Manager
	 * @since 1.0.0
	 */
	public function element_answers() {
		return Torro_Element_Answer_Manager::instance();
	}

	/**
	 * Element setting keychain function
	 *
	 * @return null|Torro_Element_Setting_Manager
	 * @since 1.0.0
	 */
	public function element_settings() {
		return Torro_Element_Setting_Manager::instance();
	}

	public function results() {
		return Torro_Results_Manager::instance();
	}

	public function result_values() {
		return Torro_Result_Values_Manager::instance();
	}

	/**
	 * Participants keychain function
	 *
	 * @return null|Torro_Participants_Manager
	 * @since 1.0.0
	 */
	public function participants(){
		return Torro_Participants_Manager::instance();
	}

	/**
	 * Components keychain function
	 *
	 * @return null|Torro_Components_Manager
	 * @since 1.0.0
	 */
	public function components() {
		return Torro_Components_Manager::instance();
	}

	/**
	 * Form settings keychain function
	 *
	 * @return null|Torro_Form_Settings_Manager
	 * @since 1.0.0
	 */
	public function form_settings(){
		return Torro_Form_Settings_Manager::instance();
	}

	/**
	 * Settings keychain function
	 *
	 * @return null|Torro_Settings_Manager
	 * @since 1.0.0
	 */
	public function settings() {
		return Torro_Settings_Manager::instance();
	}

	/**
	 * Template Tags keychain function
	 *
	 * @return null|Torro_TemplateTags_Manager
	 * @since 1.0.0
	 */
	public function templatetags() {
		return Torro_TemplateTags_Manager::instance();
	}

	/**
	 * Actions keychain function
	 *
	 * @return null|Torro_Actions_Manager
	 * @since 1.0.0
	 */
	public function actions() {
		return Torro_Actions_Manager::instance();
	}

	/**
	 * Restrictions keychain function
	 *
	 * @return null|Torro_Access_Controls_Manager
	 * @since 1.0.0
	 */
	public function access_controls() {
		return Torro_Access_Controls_Manager::instance();
	}

	/**
	 * Result handler keychain function
	 *
	 * @return null|Torro_Result_Handlers_Manager
	 * @since 1.0.0
	 */
	public function resulthandlers() {
		return Torro_Result_Handlers_Manager::instance();
	}

	/**
	 * Extensions keychain function
	 *
	 * @return null|Torro_Extensions_Manager
	 * @since 1.0.0
	 */
	public function extensions() {
		return Torro_Extensions_Manager::instance();
	}

	/**
	 * Admin notices keychain function
	 *
	 * @return null|Torro_Admin_Notices_Manager
	 * @since 1.0.0
	 */
	public function admin_notices() {
		return Torro_Admin_Notices_Manager::instance();
	}

	/**
	 * AJAX keychain function
	 *
	 * @return null|Torro_AJAX
	 * @since 1.0.0
	 */
	public function ajax() {
		return Torro_AJAX::instance();
	}

	/**
	 * Returns path to plugin
	 *
	 * @param string $path adds sub path
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_path( $path = '' ) {
		return plugin_dir_path( $this->plugin_file ) . ltrim( $path, '/' );
	}

	/**
	 * Returns url to plugin
	 *
	 * @param string $path adds sub path
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_url( $path = '' ) {
		return plugin_dir_url( $this->plugin_file ) . ltrim( $path, '/' );
	}

	/**
	 * Returns asset url path
	 *
	 * @param string $name Name of asset
	 * @param string $mode css/js/png/gif/svg/vendor-css/vendor-js
	 * @param boolean $force whether to force to load the provided version of the file (not using .min conditionally)
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_asset_url( $name, $mode = '', $force = false ) {
		$urlpath = 'assets/';

		$can_min = true;

		switch ( $mode ) {
			case 'css':
				$urlpath .= 'dist/css/' . $name . '.css';
				break;
			case 'js':
				$urlpath .= 'dist/js/' . $name . '.js';
				break;
			case 'png':
			case 'gif':
			case 'svg':
				$urlpath .= 'dist/img/' . $name . '.' . $mode;
				$can_min = false;
				break;
			case 'vendor-css':
				$urlpath .= 'vendor/' . $name . '.css';
				break;
			case 'vendor-js':
				$urlpath .= 'vendor/' . $name . '.js';
				break;
			default:
				return '';
		}

		if ( $can_min && ! $force ) {
			if ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) {
				$urlpath = explode( '.', $urlpath );
				array_splice( $urlpath, count( $urlpath ) - 1, 0, 'min' );
				$urlpath = implode( '.', $urlpath );
			}
		}

		return $this->get_url( $urlpath );
	}

	/**
	 * Logging function
	 *
	 * @param $message
	 * @since 1.0.0
	 */
	public function log( $message ) {
		$wp_upload_dir = wp_upload_dir();
		$log_dir = trailingslashit( $wp_upload_dir['basedir'] ) . 'torro-logs';

		if ( ! file_exists( $log_dir ) || ! is_dir( $log_dir ) ) {
			mkdir( $log_dir );
		}

		$file = fopen( $log_dir . '/main.log', 'a' );
		fputs( $file, $message . chr( 13 ) );
		fclose( $file );
	}
}

/**
 * Torro super function
 *
 * @return Torro
 * @since 1.0.0
 */
function torro() {
	return Torro::instance();
}
