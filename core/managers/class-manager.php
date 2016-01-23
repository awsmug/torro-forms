<?php
/**
 * Torro Forms classes manager class
 *
 * This abstract class holds and manages all class instances.
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 2015-04-16
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

abstract class Torro_Manager {
	protected $base_class = '';

	protected $modules = array();

	protected $instances = array();

	protected function __construct() {
		$this->init();

		if ( empty( $this->base_class ) ) {
			$this->base_class = 'Torro_Instance';
		}
	}

	protected abstract function init();

	protected function after_instance_added( $instance ){}

	protected function register_module( $module_name, $class_name ) {
		if ( isset( $this->modules[ $module_name ][ $class_name ] ) ) {
			return new Torro_Error( 'torro_instance_already_exist', sprintf( __( 'The instance of class %s already exists.', 'torro-forms' ), $class_name ), __METHOD__ );
		}

		if ( ! class_exists( $class_name ) ) {
			return new Torro_Error( 'torro_class_not_exist', sprintf( __( 'The class %s does not exist.', 'torro-forms' ), $class_name ), __METHOD__ );
		}

		$class = call_user_func( array( $class_name, 'instance' ) );

		if ( ! is_a( $class, $this->base_class ) ) {
			return new Torro_Error( 'torro_class_not_child', sprintf( __( 'The class %1$s is not a child of class %2$s.', 'torro-forms' ), $class_name, $this->base_class ), __METHOD__ );
		}

		if ( empty( $class->name ) ) {
			$class->name = $class_name;
		}

		if ( empty( $class->title ) ) {
			$class->title = ucwords( $class_name, '_' );
		}

		if ( empty( $class->description ) ) {
			$class->description = sprintf( __( 'This is a %s.', 'torro-forms' ), ucwords( $class_name, '_' ) );
		}

		if ( method_exists( $class, 'admin_styles' ) ) {
			add_action( 'admin_enqueue_scripts', array( $class, 'admin_styles' ) );
		}
		if ( method_exists( $class, 'admin_scripts' ) ) {
			add_action( 'admin_enqueue_scripts', array( $class, 'admin_scripts' ) );
		}
		if ( method_exists( $class, 'frontend_styles' ) ) {
			add_action( 'wp_enqueue_scripts', array( $class, 'frontend_styles' ) );
		}
		if ( method_exists( $class, 'frontend_scripts' ) ) {
			add_action( 'wp_enqueue_scripts', array( $class, 'frontend_scripts' ) );
		}
		if ( method_exists( $this, 'after_instance_added' ) ) {
			$this->after_instance_added( $class );
		}

		if ( ! $class->initialized ) {
			$class->initialized = true;
		}

		$this->modules[ $module_name ][ $class->name ] = $class;

		return TRUE;
	}

	protected function add_instance( $id ){

	}

	protected function get_module( $module_name, $class_name ) {
		if ( ! isset( $this->modules[ $module_name ][ $class_name ] ) ) {
			return new Torro_Error( 'torro_module_not_exist', sprintf( __( 'The module %s does not exist.', 'torro-forms' ), $class_name ), __METHOD__ );
		}

		return $this->modules[ $module_name ][ $class_name ];
	}

	protected function get_all_modules( $module_name ) {
		return $this->modules[ $module_name ];
	}
}