<?php

/**
 * Container base class
 *
 * @author  awesome.ug <contact@awesome.ug>
 * @package TorroForms
 * @version 1.0.0alpha1
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

class Torro_Container extends Torro_Instance_Base {

	/**
	 * Label of container
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $label = null;

	/**
	 * Sort number of container
	 *
	 * @var int
	 * @since 1.0.0
	 */
	protected $sort = null;

	/**
	 * ID of container
	 *
	 * @var Torro_Form_Element[]
	 * @since 1.0.0
	 */
	protected $elements = array();

	/**
	 * Torro_Container constructor.
	 *
	 * @param int $id
	 *
	 * @since 1.0.0
	 */
	public function __construct( $id = null ) {
		parent::__construct( $id );
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
	public function get_html( $response = array(), $errors = array() ) {
		$html = sprintf( '<input type="hidden" name="torro_response[container_id]" value="%d" />', $this->id );

		foreach ( $this->elements as $element ) {
			if ( is_wp_error( $element ) ) {
				$html .= $element->get_error_message();
				continue;
			}

			if ( ! isset( $response[ $element->id ] ) ) {
				$response[ $element->id ] = null;
			}
			if ( ! isset( $errors[ $element->id ] ) ) {
				$errors[ $element->id ] = null;
			}
			$html .= $element->get_html( $response[ $element->id ], $errors[ $element->id ] );
		}

		return $html;
	}

	protected function init() {
		$this->superior_id_name = 'form_id';
		$this->manager_method = 'containers';
		$this->valid_args = array( 'label', 'sort' );
	}

	/**
	 * Populating object
	 *
	 * @param int $id
	 *
	 * @since 1.0.0
	 */
	protected function populate( $id ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->torro_containers} WHERE id = %d", absint( $id ) );

		$container = $wpdb->get_row( $sql );

		if ( 0 !== $wpdb->num_rows ) {
			$this->id          = $container->id;
			$this->superior_id = $container->form_id;
			$this->label       = $container->label;
			$this->sort        = $container->sort;

			$this->elements = $this->populate_elements();
		}
	}

	protected function exists_in_db() {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT COUNT( id ) FROM {$wpdb->torro_containers} WHERE id = %d", $this->id );
		$var = $wpdb->get_var( $sql );

		if ( $var > 0 ) {
			return true;
		}

		return false;
	}

	protected function save_to_db(){
		global $wpdb;

		if ( ! empty( $this->id ) ) {
			$status = $wpdb->update(
				$wpdb->torro_containers,
				array(
					'form_id' => $this->superior_id,
					'label'   => $this->label,
					'sort'    => $this->sort
				),
				array(
					'id' => $this->id
				)
			);
			if ( ! $status ) {
				return new Torro_Error( 'cannot_update_db', __( 'Could not update container in the database.', 'torro-forms' ), __METHOD__ );
			}
		} else {
			$status = $wpdb->insert(
				$wpdb->torro_containers,
				array(
					'form_id' => $this->superior_id,
					'label'   => $this->label,
					'sort'    => $this->sort
				)
			);
			if ( ! $status ) {
				return new Torro_Error( 'cannot_insert_db', __( 'Could not insert container into the database.', 'torro-forms' ), __METHOD__ );
			}

			$this->id = $wpdb->insert_id;
		}

		return $this->id;
	}

	protected function delete_from_db(){
		global $wpdb;

		if ( empty( $this->id ) ) {
			return new Torro_Error( 'cannot_delete_empty', __( 'Cannot delete container without ID.', 'torro-forms' ), __METHOD__ );
		}

		foreach ( $this->elements as $element ) {
			$element->delete();
		}

		return $wpdb->delete( $wpdb->torro_containers, array( 'id' => $this->id ) );
	}

	/**
	 * Internal function to get elements of container
	 *
	 * @return array Torro_Form_Element
	 * @since 1.0.0
	 */
	private function populate_elements(){
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->torro_elements} WHERE container_id = %d ORDER BY sort ASC", $this->id );
		$results = $wpdb->get_results( $sql );

		$elements = array();
		foreach( $results as $element ){
			$elements[] = torro()->elements()->get( $element->id );
		}

		return $elements;
	}
}
