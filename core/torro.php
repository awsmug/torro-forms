<?php
/**
 * Torro Class as general access point for all class instances and basic functionality.
 *
 * This class instance is returned by the `torro()` function.
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 1.0.0
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
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private $plugin_file = '';

	private function __construct() {
		$this->plugin_file = dirname( dirname( __FILE__ ) ) . '/torro-forms.php';

		// load instance manager classes
		require_once( $this->path( 'core/managers/class-manager.php' ) );
		require_once( $this->path( 'core/managers/class-components-manager.php' ) );
		require_once( $this->path( 'core/managers/class-elements-manager.php' ) );
		require_once( $this->path( 'core/managers/class-settings-manager.php' ) );
		require_once( $this->path( 'core/managers/class-templatetags-manager.php' ) );
		require_once( $this->path( 'core/managers/class-actions-manager.php' ) );
		require_once( $this->path( 'core/managers/class-restrictions-manager.php' ) );
		require_once( $this->path( 'core/managers/class-result-handlers-manager.php' ) );
		require_once( $this->path( 'core/managers/class-extensions-manager.php' ) );

		// load additional manager classes
		require_once( $this->path( 'core/managers/class-admin-notices-manager.php' ) );
	}

	public function components() {
		return Torro_Components_Manager::instance();
	}

	public function elements() {
		return Torro_Form_Elements_Manager::instance();
	}

	public function settings() {
		return Torro_Settings_Manager::instance();
	}

	public function templatetags() {
		return Torro_TemplateTags_Manager::instance();
	}

	public function actions() {
		return Torro_Actions_Manager::instance();
	}

	public function restrictions() {
		return Torro_Restrictions_Manager::instance();
	}

	public function resulthandlers() {
		return Torro_ResultHandlers_Manager::instance();
	}

	public function extensions() {
		return Torro_Extensions_Manager::instance();
	}

	public function admin_notices() {
		return Torro_Admin_Notices_Manager::instance();
	}

	public function ajax() {
		return Torro_AJAX::instance();
	}

	public function path( $path = '' ) {
		return plugin_dir_path( $this->plugin_file ) . ltrim( $path, '/' );
	}

	public function url( $path = '' ) {
		return plugin_dir_url( $this->plugin_file ) . ltrim( $path, '/' );
	}

	public function asset_url( $name, $mode = '' ) {
		$urlpath = 'assets/';

		$can_min = true;

		switch ( $mode ) {
			case 'css':
				$urlpath .= 'css/' . $name . '.css';
				break;
			case 'js':
				$urlpath .= 'js/' . $name . '.js';
				break;
			case 'png':
			case 'gif':
			case 'svg':
				$urlpath .= 'img/' . $name . '.' . $mode;
				$can_min = false;
				break;
			case 'vendor-css':
				$urlpath .= 'vendor/' . $name . '.css';
				break;
			case 'vendor-js':
				$urlpath .= 'vendor/' . $name . '.js';
				break;
			default:
				return false;
		}

		//TODO: some kind of notice if file can not be found
		if ( ! file_exists( $this->path( $urlpath ) ) ) {
			if ( ! $can_min ) {
				return false;
			} elseif ( false !== strpos( $urlpath, '.min' ) ) {
				$urlpath = str_replace( '.min', '', $urlpath );
			} else {
				$urlpath = explode( '.', $urlpath );
				array_splice( $urlpath, count( $urlpath ) - 1, 0, 'min' );
				$urlpath = implode( '.', $urlpath );
			}

			if ( ! file_exists( $this->path( $urlpath ) ) ) {
				return false;
			}
		}

		return $this->url( $urlpath );
	}

	public function css_url( $name = '' ) {
		return $this->url( 'assets/css/' . $name . '.css' );
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

function torro() {
	return Torro::instance();
}
