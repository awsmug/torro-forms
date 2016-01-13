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
	/**
	 * The Single instances of the components
	 *
	 * @since 1.0.0
	 */
	protected static $_instances = array();

	/**
	 * Main Instance
	 *
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( function_exists( 'get_called_class' ) ) {
			$class = get_called_class();
		} else {
			$class = self::php52_get_called_class();
		}

		if ( ! isset( self::$_instances[ $class ] ) ) {
			self::$_instances[ $class ] = new $class();
		}

		return self::$_instances[ $class ];
	}

	/**
	 * PHP 5.2 variant of `get_called_class()`
	 *
	 * Really ugly, but PHP 5.2 does not support late static binding.
	 * Using `debug_backtrace()` is the only way.
	 *
	 * This function must exist in every class that should use `get_called_class()`.
	 *
	 * @since 1.0.0
	 */
	protected static function php52_get_called_class() {
		$arr = array();
		$arr_traces = debug_backtrace();
		foreach ( $arr_traces as $arr_trace ) {
			$class_name = '';
			if ( isset( $arr_trace['class'] ) ) {
				$class_name = $arr_trace['class'];
			} elseif ( isset( $arr_trace['function'] ) && isset( $arr_trace['args'] ) && isset( $arr_trace['args'][0] ) && is_array( $arr_trace['args'][0] ) ) {
				if ( 'call_user_func' == $arr_trace['function'] && 'instance' == $arr_trace['args'][0][1] && is_string( $arr_trace['args'][0][0] ) ) {
					$class_name = $arr_trace['args'][0][0];
				}
			}

			if ( $class_name && 0 == count( $arr ) || get_parent_class( $class_name ) == end( $arr ) ) {
				$arr[] = $class_name;
			}
		}
		return end( $arr );
	}

	protected $base_class = '';

	protected $instances = array();

	public function __construct() {
		$this->init();

		if ( empty( $this->base_class ) ) {
			$this->base_class = 'Torro_Instance';
		}
	}

	protected abstract function init();

	protected abstract function after_instance_added( $instance );

	public function add( $class_name ) {
		if ( isset( $this->instances[ $class_name ] ) ) {
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

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $class, 'admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $class, 'admin_scripts' ) );
		} else {
			add_action( 'wp_enqueue_scripts', array( $class, 'frontend_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $class, 'frontend_scripts' ) );
		}

		$this->after_instance_added( $class );

		if ( ! $class->initialized ) {
			$class->initialized = true;
		}

		$this->instances[ $class->name ] = $class;
	}

	public function get( $name ) {
		if ( ! isset( $this->instances[ $name ] ) ) {
			return new Torro_Error( 'torro_instance_not_exist', sprintf( __( 'The instance %s does not exist.', 'torro-forms' ), $name ), __METHOD__ );
		}

		return $this->instances[ $name ];
	}

	public function get_all() {
		return $this->instances;
	}
}
