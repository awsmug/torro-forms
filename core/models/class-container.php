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

	public function move( $form_id ) {
		return parent::move( $form_id );
	}

	public function copy( $form_id ) {
		return parent::copy( $form_id );
	}

	protected function init() {
		$this->table_name = 'torro_containers';
		$this->superior_id_name = 'form_id';
		$this->manager_method = 'containers';
		$this->valid_args = array(
			'label' 	=> 'string',
			'sort' 		=> 'int',
		);
	}

	/**
	 * Populating object
	 *
	 * @param int $id
	 *
	 * @since 1.0.0
	 */
	protected function populate( $id ) {
		parent::populate( $id );

		if ( $this->id ) {
			$this->elements = torro()->elements()->query( array(
				'container_id'	=> $this->id,
				'number'		=> -1,
				'orderby'		=> 'sort',
				'order'			=> 'ASC',
			) );
		}
	}

	protected function delete_from_db(){
		$status = parent::delete_from_db();

		if ( $status && ! is_wp_error( $status ) ) {
			foreach ( $this->elements as $element ) {
				torro()->elements()->delete( $element->id );
			}
		}

		return $status;
	}
}
