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
		require_once( $this->path( 'core/managers/class-form-elements-manager.php' ) );
		require_once( $this->path( 'core/managers/class-invalid-manager.php' ) );

		// initialize managers
		$this->managers['components'] = new Torro_Manager( 'Torro_Component', array( $this, 'after_components_added' ) );
		$this->managers['form_elements'] = new Torro_Form_Elements_Manager( 'Torro_Form_Element' );
		$this->managers['settings'] = new Torro_Manager( 'Torro_Settings', array( $this, 'after_settings_added' ) );
		$this->managers['templatetags'] = new Torro_Manager( 'Torro_TemplateTags', array( $this, 'after_templatetags_added' ) );

		$this->managers['actions'] = new Torro_Manager( 'Torro_Action', array( $this, 'after_actions_added' ) );
		$this->managers['restrictions'] = new Torro_Manager( 'Torro_Restriction', array( $this, 'after_restrictions_added' ) );
		$this->managers['result_handlers'] = new Torro_Manager( 'Torro_ResultHandler', array( $this, 'after_result_handlers_added' ) );

		// dummy object for invalid functions
		$this->managers['invalid'] = new Torro_Invalid_Manager();
	}

	public function __call( $function, $args = array() ) {
		if ( 'invalid' !== $function && isset( $this->managers[ $function ] ) ) {
			return $this->managers[ $function ];
		}

		// return an invalid dummy object to prevent fatal errors from chaining functions
		$this->managers['invalid']->set_invalid_function( $function );
		return $this->managers['invalid'];
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

	// callback function after having added result handlers
	public function after_components_added( $instance ) {
		$instance->check_and_start();

		return $instance;
	}

	// callback function after having added settings
	public function after_settings_added( $instance ) {
		add_action( 'torro_save_settings', array( $instance, 'save_settings' ), 10, 1 );

		return $instance;
	}

	// callback function after having added templatetags
	public function after_templatetags_added( $instance ) {
		$instance->tags();

		return $instance;
	}

	// callback function after having added actions
	public function after_actions_added( $instance ) {
		add_action( 'init', array( $instance, 'init_settings' ), 15 );

		return $instance;
	}

	// callback function after having added restrictions
	public function after_restrictions_added( $instance ) {
		add_action( 'init', array( $instance, 'init_settings' ), 15 );

		return $instance;
	}

	// callback function after having added result handlers
	public function after_result_handlers_added( $instance ) {
		add_action( 'init', array( $instance, 'init_settings' ), 15 );

		return $instance;
	}
}

function torro() {
	return Torro::instance();
}
