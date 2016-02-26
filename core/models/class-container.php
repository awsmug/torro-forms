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

	/**
	 * ID of container
	 *
	 * @var int
	 * @since 1.0.0
	 */
	private $id = null;

	/**
	 * Label of container
	 *
	 * @var string
	 * @since 1.0.0
	 */
	private $label = null;

	/**
	 * Sort number of container
	 *
	 * @var int
	 * @since 1.0.0
	 */
	private $sort = null;

	/**
	 * Form ID of container
	 *
	 * @var int
	 * @since 1.0.0
	 */
	private $form_id = null;

	/**
	 * ID of container
	 *
	 * @var Torro_Form_Element[]
	 * @since 1.0.0
	 */
	private $elements = array();

	/**
	 * Torro_Container constructor.
	 *
	 * @param int $id
	 *
	 * @since 1.0.0
	 */
	public function __construct( $id = null ) {
		$this->populate( $id );
	}

	/**
	 * Populating object
	 *
	 * @param int $id
	 *
	 * @since 1.0.0
	 */
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

	/**
	 * Saving container
	 *
	 * @return int Container ID
	 * @since 1.0.0
	 */
	public function save(){
		global $wpdb;

		if ( null !== $this->id ) {
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
	/**
	 * Deleting container
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function delete(){
		global $wpdb;

		if ( null !== $this->id ) {
			if( 0 !== count( $this->elements ) ){
				foreach( $this->elements AS $element ){
					$element->delete();
				}
			}
			return $wpdb->delete( $wpdb->torro_containers, array( 'id' => $this->id ) );
		}
		return false;
	}

	/**
	 * Internal function to get elements of container
	 *
	 * @return array Torro_Form_Element
	 * @since 1.0.0
	 */
	private function __get_elements(){
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->torro_elements} WHERE container_id = %d ORDER BY sort ASC", $this->id );
		$results = $wpdb->get_results( $sql );

		$elements = array();
		foreach( $results AS $element ){
			$elements[] = torro()->elements()->get( $element->id );
		}

		return $elements;
	}

	/**
	 * Returns elements of container
	 *
	 * @return Torro_Form_Element[]|Torro_Error[]
	 * @since 1.0.0
	 */
	public function get_elements(){
		return $this->elements;
	}

	/**
	 * Getting form html of container
	 *
	 * @param array $response
	 * @param array $errors
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_html( $response = array(), $errors = array() )
	{
		$html = sprintf( '<input type="hidden" name="torro_response[container_id]" value="%d" />', $this->id );

		foreach( $this->elements AS $element )
		{
			if( is_wp_error( $element ) ){
				$html .= $element->get_error_message();
				continue;
			}

			if( ! isset( $response[ $element->id ] ) )
			{
				$response[ $element->id ] = null;
			}
			if( ! isset( $errors[ $element->id ] ) )
			{
				$errors[ $element->id ] = null;
			}
			$html .= $element->get_html( $response[ $element->id ], $errors[ $element->id ] );
		}

		return $html;
	}

	/**
	 * Checks if the container exists
	 *
	 * @return boolean $exists true if Form exists, false if not
	 * @since 1.0.0
	 */
	public function exists() {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT COUNT( id ) FROM {$wpdb->torro_containers} WHERE id = %d", $this->id );
		$var = $wpdb->get_var( $sql );

		if ( $var > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Magic setter function
	 *
	 * @param $key
	 * @param $value
	 *
	 * @since 1.0.0
	 */
	public function __set( $key, $value ) {
		switch ( $key ) {
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

	/**
	 * Magic getter function
	 *
	 * @param $key
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public function __get( $key ) {
		if ( property_exists( $this, $key ) ) {
			return $this->$key;
		}
		return null;
	}

	/**
	 * Magic isset function
	 *
	 * @param $key
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function __isset( $key ) {
		if ( property_exists( $this, $key ) ) {
			return true;
		}
		return false;
	}
}
