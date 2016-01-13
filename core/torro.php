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

class Torro {
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private $managers = array();
	private $plugin_file = '';

	public function __construct() {
		$this->plugin_file = dirname( dirname( __FILE__ ) ) . '/torro-forms.php';

		// load manager classes
		require_once( $this->path( 'core/managers/class-manager.php' ) );
		require_once( $this->path( 'core/managers/class-components-manager.php' ) );
		require_once( $this->path( 'core/managers/class-form-elements-manager.php' ) );
		require_once( $this->path( 'core/managers/class-settings-manager.php' ) );
		require_once( $this->path( 'core/managers/class-templatetags-manager.php' ) );
		require_once( $this->path( 'core/managers/class-actions-manager.php' ) );
		require_once( $this->path( 'core/managers/class-restrictions-manager.php' ) );
		require_once( $this->path( 'core/managers/class-result-handlers-manager.php' ) );

		// initialize managers
		$this->managers['components'] = Torro_Components_Manager::instance();
		$this->managers['form_elements'] = Torro_Form_Elements_Manager::instance();
		$this->managers['settings'] = Torro_Settings_Manager::instance();
		$this->managers['templatetags'] = Torro_TemplateTags_Manager::instance();

		$this->managers['actions'] = Torro_Actions_Manager::instance();
		$this->managers['restrictions'] = Torro_Restrictions_Manager::instance();
		$this->managers['result_handlers'] = Torro_ResultHandlers_Manager::instance();
	}

	public function components() {
		return $this->managers['components'];
	}

	public function form_elements() {
		return $this->managers['form_elements'];
	}

	public function settings() {
		return $this->managers['settings'];
	}

	public function templatetags() {
		return $this->managers['templatetags'];
	}

	public function actions() {
		return $this->managers['actions'];
	}

	public function restrictions() {
		return $this->managers['restrictions'];
	}

	public function result_handlers() {
		return $this->managers['result_handlers'];
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
}

function torro() {
	return Torro::instance();
}
