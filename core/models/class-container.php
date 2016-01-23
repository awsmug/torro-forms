<?php

/**
 * Container base class
 *
 * @author  awesome.ug <contact@awesome.ug>
 * @package TorroForms
 * @version 2015-04-16
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 rheinschmiede (contact@awesome.ug)
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

class Torro_Container {

	private $id;

	private $label;

	private $sort;

	private $form_id;

	private $elements = array();

	public function __construct( $id ) {
		$this->populate( $id );
	}

	private function populate( $id ) {
		global $wpdb;

		if ( ! empty( $id ) ) {

			$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->torro_containers} WHERE id =%d", $id );

			$container = $wpdb->get_row( $sql );

			if ( 0 !== $wpdb->num_rows ) {
				$this->id      = $container->id;
				$this->form_id = $container->form_id;
				$this->label   = $container->label;
				$this->sort    = $container->sort;

				$this->elements = $this->__get_elements();
			}
		}
	}

	public function save(){
		global $wpdb;

		if( ! empty( $this->id ) ){
			$wpdb->update(
				$wpdb->torro_containers,
				array(
					'form_id' => $this->form_id,
					'label' => $this->label,
					'sort' => $this->sort
				),
				array(
					'id' => $this->id
				)
			);
			return $this->id;
		}else{
			$wpdb->insert(
				$wpdb->torro_containers,
				array(
					'form_id' => $this->form_id,
					'label' => $this->label,
					'sort' => $this->sort
				)
			);

			return $wpdb->insert_id;
		}
	}

	public function delete(){
		global $wpdb;

		if ( ! empty( $this->id ) ) {
			if( 0 !== count( $this->elements ) ){
				foreach( $this->elements AS $element ){
					$element_class_name = 'Torro_Form_Element_' . ucfirst( $element[ 'type' ] );

					if( class_exists( $element_class_name ) ) {
						$torro_element = $element_class_name::instance( $element[ 'id' ] );
						$torro_element->delete();
					}
				}
			}
			return $wpdb->delete( $wpdb->torro_element_answers, array( 'id' => $this->id ) );
		}
		return FALSE;
	}

	private function __get_elements(){
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->torro_elements} WHERE container_id=%d", $this->id );
		$results = $wpdb->get_results( $sql );

		$elements = array();
		foreach( $results AS $element ){
			$elements[] = torro()->elements()->get( $element->id, $element->type );
		}

		return $elements;
	}

	public function get_elements(){
		return $this->elements;
	}

	public function __set( $key, $value ) {
		switch ( $key ) {
			case 'id':
				return FALSE;
				break;

			case 'sort':
				$value = absint( $value );
				$this->$key = $value;
				break;

			default:
				if ( property_exists( $this, $key ) ) {
					$this->$key = $value;
				}
		}
	}

	public function __get( $key ) {
		if ( property_exists( $this, $key ) ) {
			return $this->$key;
		}
		return null;
	}

	public function __isset( $key ) {
		if ( property_exists( $this, $key ) ) {
			return true;
		}
		return false;
	}
}