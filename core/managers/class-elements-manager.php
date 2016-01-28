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

	private $form_id = null;

	private $container_id = null;

	private $type = null;

	private $label = null;

	private $sort = null;

	public static function instance( $id = null ) {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		self::set_element( $id );

		return self::$instance;
	}

	private static function set_element( $id = null ){
		// If new $id was set
		if ( self::$instance->element_id !== $id  && null !== $id ) {
			$element_instance = self::$instance->get_element_instance( $id );

			self::$instance->element_id = $id;
			self::$instance->element = $element_instance;

			self::$instance->form_id = $element_instance->form_id;
			self::$instance->container_id = $element_instance->container_id;
			self::$instance->type = $element_instance->type;
			self::$instance->label = $element_instance->label;
			self::$instance->sort = $element_instance->sort;
		}
	}

	protected function allowed_modules(){
		$allowed = array(
			'elements' => 'Torro_Form_Element'
		);
		return $allowed;
	}

	public function form( $id = null ){
		if( null !== $id ) {
			$this->form_id = $id;
		}else{
			return $this->form_id;
		}
	}

	public function container( $id = null ){
		if( null !== $id ) {
			$this->container_id = $id;
		}else{
			return $this->container_id;
		}
	}

	public function label( $label = null ){
		if( null !== $label ) {
			$this->label = $label;
		}else{
			return $this->label;
		}
	}

	public function sort( $number = null ){
		if( null !== $number ) {
			$this->sort = $number;
		}else{
			return $this->sort;
		}
	}

	public function type( $type = '' ){
		if( null !== $type ) {
			$this->type = $type;
		}else{
			return $this->type;
		}
	}

	public function save(){
		// New Element
		if( empty( $this->element_id ) ) {
			if( null === $this->type ){
				return new Torro_Error( 'torro_element_missing_type_on_save', __( 'Missing element type to save element.', 'torro-forms' ), __METHOD__ );
			}
			if( null === $this->form_id ){
				return new Torro_Error( 'torro_element_missing_form_id_on_save', __( 'Missing form id to save element.', 'torro-forms' ), __METHOD__ );
			}
			if( null === $this->container_id ){
				return new Torro_Error( 'torro_element_missing_container_id_on_save', __( 'Missing container id to save element.', 'torro-forms' ), __METHOD__ );
			}
			$class = $this->get_class_name( $this->type );
			$this->element = $class::instance();
			$this->element->form_id = $this->form_id;
			$this->element->container_id = $this->container_id;
			$this->element->label = $this->label;
			$this->element->sort = $this->sort;
		}

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

		$sql = $wpdb->prepare( "SELECT type FROM $wpdb->torro_elements WHERE id = %d ORDER BY sort ASC", $id );
		$type = $wpdb->get_var( $sql );

		if ( ! $type ) {
			return false;
		}

		$class_name = $this->get_class_name( $type );
		$element_instance = $this->get_registered( $class_name );

		return $element_instance::instance( $id );
	}
}