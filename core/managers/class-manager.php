<?php
/**
 * Torro Forms classes manager class
 *
 * This abstract class holds and manages all class instances.
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

abstract class Torro_Manager {
	/**
	 * Modules
	 *
	 * @var array Torro_Base[]
	 * @since 1.0.0
	 */
	protected $modules = array();

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {}

	/**
	 * Registering a module
	 *
	 * @param $class_name
	 *
	 * @return bool|Torro_Error
	 * @since 1.0.0
	 */
	public function register( $class_name ) {
		if ( ! class_exists( $class_name ) ) {
			return new Torro_Error( 'torro_class_not_exist', sprintf( __( 'The class %s does not exist.', 'torro-forms' ), $class_name ), __METHOD__ );
		}

		if( method_exists( $this, 'allowed_modules' ) ){
			$allowed_modules = $this->allowed_modules();

			if( ! array_key_exists( $this->get_category(), $allowed_modules ) ){
				return new Torro_Error( 'torro_module_category_not_exist', sprintf( __( 'The module category %s does not exist.', 'torro-forms' ), $this->get_category() ), __METHOD__ );
			}

			$base_class_name = $allowed_modules[ $this->get_category() ];

			if( ! is_subclass_of( $class_name, $base_class_name ) ){
				return new Torro_Error( 'torro_module_class_not_allowed', sprintf( __( 'The module class %s is not allowed for this module category.', 'torro-forms' ), $class_name ), __METHOD__ );
			}
		}

		if ( is_callable( array( $class_name, 'instance' ) ) ) {
			$class = call_user_func( array( $class_name, 'instance' ) );
		} else {
			$class = new $class_name();
		}

		if ( empty( $class->name ) ) {
			$class->name = $class_name;
		}

		if ( empty( $class->title ) ) {
			$class->title = ucwords( $class_name, '_' );
		}

		if ( empty( $class->description ) ) {
			$class->description = sprintf( __( 'This is a %s module.', 'torro-forms' ), ucwords( $class_name, '_' ) );
		}

		if ( isset( $this->modules[ $this->get_category() ][ $class->name ] ) ) {
			return new Torro_Error( 'torro_module_already_exist', sprintf( __( 'The module %s already exists.', 'torro-forms' ), $class_name ), __METHOD__ );
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

		$class->initialized = true;

		$this->modules[ $this->get_category() ][ $class->name ] = $class;

		return true;
	}

	/**
	 * Get registered module
	 *
	 * @param $name
	 *
	 * @return Torro_Base
	 * @since 1.0.0
	 */
	public function get_registered( $name ) {
		if ( ! isset( $this->modules[ $this->get_category() ][ $name ] ) ) {
			return new Torro_Error( 'torro_module_not_exist', sprintf( __( 'The module %s does not exist.', 'torro-forms' ), $name ), __METHOD__ );
		}

		return $this->modules[ $this->get_category() ][ $name ];
	}

	/**
	 * Returning all registered modules
	 *
	 * @return array|Torro_Error
	 * @since 1.0.0
	 */
	public function get_all_registered() {
		if( ! isset( $this->modules[ $this->get_category() ]  ) ){
			return new Torro_Error( 'torro_module_category_not_exist', sprintf( __( 'The module category %s does not exist.', 'torro-forms' ), $this->get_category() ), __METHOD__ );
		}
		return $this->modules[ $this->get_category() ];
	}

	/**
	 * Will be executed after instance was added
	 *
	 * @param $instance
	 *
	 * @todo Do we really need that function?
	 * @since 1.0.0
	 */
	protected function after_instance_added( $instance ){}

	/**
	 * Getting category
	 *
	 * @return mixed
	 */
	protected abstract function get_category();
}
