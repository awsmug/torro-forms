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

	private static $instance = null;

	private $element_id = null;

	private $element = null;

	private $element_instances = array();

	public static function instance( $id = null, $type = null ) {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		self::set_element( $id, $type );

		return self::$instance;
	}

	private static function set_element( $id = null, $type = null ){
		// If new $id was set
		if ( self::$instance->element_id !== $id  && null !== $id ) {
			self::$instance->element_id = $id;

			// New Element
			if( empty( $id ) && $type !== null ) {
				$class = self::$instance->get_class_name( $type );

				self::$instance->element = $class::instance();
				self::$instance->element->type = $type;

			// Existing Element
			}elseif( ! empty( $id ) ) {
				// Todo: More elegant to get instance
				self::$instance->element = self::$instance->get_element_instance( $id );
			}
		}
	}

	protected function allowed_modules(){
		$allowed = array(
			'elements' => 'Torro_Form_Element'
		);
		return $allowed;
	}

	public function container( $id = null ){
		if( null !== $id ) {
			$this->element->container_id = $id;
		}else{
			return $this->element->container_id;
		}
	}

	public function form( $id = null ){
		if( null !== $id ) {
			$this->element->form_id = $id;
		}else{
			return $this->element->form_id;
		}
	}

	public function label( $label = null ){
		if( null !== $label ) {
			$this->element->label = $label;
		}else{
			return $this->element->label;
		}
	}

	public function sort( $number = null ){
		if( null !== $number ) {
			$this->element->sort = $number;
		}else{
			return $this->element->sort;
		}
	}

	public function type( $type = '' ){
		if( null !== $type ) {
			$this->element->type = $type;
		}else{
			return $this->element->type;
		}
	}

	public function save(){
		return $this->element->save();
	}

	public function delete(){
		return $this->element->delete();
	}

	public function get(){
		return $this->element;
	}

	public function get_input_name(){
		return $this->element->get_input_name();
	}

	public function get_input_html(){
		return $this->element->get_input_name();
	}

	public function get_input_name_selector(){
		return $this->element->get_input_name_selector();
	}

	private function get_class_name( $type = null ) {
		if( null === $type ){
			$type = $this->element->type;
		}
		$class = 'Torro_Form_Element_' . ucfirst( $type );
		return $class;
	}

	public function validate( $input ){
		return $this->element->validate( $input );
	}

	public function register( $class_name ){
		return $this->register_module( 'elements', $class_name );
	}

	public function get_registered( $class_name ){
		return $this->get_module( 'elements', $class_name );
	}

	public function get_all_registered(){
		return $this->get_all_modules( 'elements' );
	}

	private function get_element_instance( $id ){
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
