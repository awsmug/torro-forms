<?php
/**
 * Torro Forms element classes manager class
 *
 * This class holds and manages all element class instances.
 * It can return both general instances for a type and instances for a specific element.
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

final class Torro_Form_Elements_Manager extends Torro_Manager {
	protected $element_instances = array();
	private static $instance = null;

	private $id;

	private $object;

	public static function instance( $id = null, $type = null ) {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		if ( self::$instance->id !== $id  && null !== $id ) {
			self::$instance->id = $id;

			if( empty( $id ) && $type !== null ) {
				$class = self::$instance->get_class_name( $type );
				self::$instance->object = $class::instance();
				self::$instance->object->type = $type;

			}elseif( !empty( $id ) ) {
				self::$instance->object = self::$instance->get_element_instance( $id );
			}
		}

		return self::$instance;
	}

	public function container( $id = null ){
		if( null !== $id ) {
			$this->object->container_id = $id;
		}else{
			return $this->object->container_id;
		}
	}

	public function form( $id = null ){
		if( null !== $id ) {
			$this->object->form_id = $id;
		}else{
			return $this->object->form_id;
		}
	}

	public function label( $label = null ){
		if( null !== $label ) {
			$this->object->label = $label;
		}else{
			return $this->object->label;
		}
	}

	public function sort( $number = null ){
		if( null !== $number ) {
			$this->object->sort = $number;
		}else{
			return $this->object->sort;
		}
	}

	public function type( $type = '' ){
		if( null !== $type ) {
			$this->object->type = $type;
		}else{
			return $this->object->type;
		}
	}

	public function save(){
		return $this->object->save();
	}

	public function delete(){
		return $this->object->delete();
	}

	public function get_input_name(){
		return $this->object->get_input_name();
	}

	public function get_input_html(){
		return $this->object->get_input_name();
	}

	public function get_input_name_selector(){
		return $this->object->get_input_name_selector();
	}

	public function get_class_name( $type = null ) {
		if( null === $type ){
			$type = $this->object->type;
		}
		$class = 'Torro_Form_Element_' . ucfirst( $type );
		return $class;
	}

	protected function init() {
		$this->base_class = 'Torro_Form_Element';
	}

	public function validate( $input ){
		return $this->object->validate( $input );
	}

	public function register( $class_name ){
		return $this->register_module( 'elements', $class_name );
	}

	public function get(){
		return $this->object;
	}

	public function get_registered( $class_name ){
		return $this->get_module( 'elements', $class_name );
	}

	public function get_all_registered(){
		return $this->get_all_modules( 'elements' );
	}

	public function get_element_instance( $id ){
		global $wpdb;

		if ( ! isset( $this->element_instances[ $id ] ) ) {
			if ( empty( $type ) ) {
				$sql = $wpdb->prepare( "SELECT type FROM $wpdb->torro_elements WHERE id = %d ORDER BY sort ASC", $id );

				$type = $wpdb->get_var( $sql );
				if ( ! $type ) {
					return new Torro_Error( 'torro_element_id_invalid', sprintf( __( 'The element ID %s is invalid. The type could not be detected.', 'torro-forms' ), $id ), __METHOD__ );
				}
			}

			$class = $this->modules[ 'elements' ][ $type ];

			$this->element_instances[ $id ] = call_user_func( array( $class, 'instance' ), $id );
		}

		return $this->element_instances[ $id ];
	}
}
