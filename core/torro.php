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

	public function __construct() {
		// load manager classes
		require_once( TORRO_FOLDER . 'core/managers/class-manager.php' );
		require_once( TORRO_FOLDER . 'core/managers/class-form-elements-manager.php' );
		require_once( TORRO_FOLDER . 'core/managers/class-invalid-manager.php' );

		// initialize managers
		$this->managers['components'] = new Torro_Manager( 'Torro_Component', true );
		$this->managers['form_elements'] = new Torro_Form_Elements_Manager( 'Torro_Form_Element', false );
		$this->managers['settings'] = new Torro_Manager( 'Torro_Settings', false, array( $this, 'after_settings_added' ) );
		$this->managers['templatetags'] = new Torro_Manager( 'Torro_TemplateTags', false, array( $this, 'after_templatetags_added' ) );

		$this->managers['actions'] = new Torro_Manager( 'Torro_Action', true, array( $this, 'after_actions_added' ) );
		$this->managers['restrictions'] = new Torro_Manager( 'Torro_Restriction', true );
		$this->managers['result_handlers'] = new Torro_Manager( 'Torro_ResultHandler', false );

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

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $instance, 'admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $instance, 'admin_scripts' ) );
		} else {
			add_action( 'wp_enqueue_scripts', array( $instance, 'frontend_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $instance, 'frontend_scripts' ) );
		}

		return $instance;
	}
}

function torro() {
	return Torro::instance();
}
