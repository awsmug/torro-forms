<?php

/**
 * Element answer class
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

class Torro_Element_Answer {

	private $id = null;

	private $element_id = null;

	private $label = null;

	private $sort = null;

	private $section = '';

	public function __construct( $id = null ) {
		$this->populate( $id );
	}

	private function populate( $id = null ) {
		global $wpdb;

		if ( ! empty( $id ) ) {
			$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->torro_element_answers} WHERE id =%d", $id );

			$answer = $wpdb->get_row( $sql );

			if ( 0 !== $wpdb->num_rows ) {
				$this->id         = $answer->id;
				$this->element_id = $answer->element_id;
				$this->label      = $answer->answer;
				$this->sort       = $answer->sort;
				$this->section    = $answer->section;
			}
		}
	}

	public function save() {
		global $wpdb;

		if ( ! empty( $this->id ) ) {
			return $wpdb->update( $wpdb->torro_element_answers, array(
				'element_id' => $this->element_id,
				'answer'     => $this->label,
				'sort'       => $this->sort,
				'section'    => $this->section
			), array(
				                      'id' => $this->id
			                      ) );
		} else {
			$wpdb->insert( $wpdb->torro_element_answers, array(
				'element_id' => $this->element_id,
				'answer'  => $this->label,
				'sort'    => $this->sort,
				'section' => $this->section
			) );

			return $wpdb->insert_id;
		}
	}

	public function delete() {
		global $wpdb;

		if ( ! empty( $this->id ) ) {
			return $wpdb->delete( $wpdb->torro_element_answers, array( 'id' => $this->id ) );
		}

		return false;
	}

	public function __get( $key ) {
		if ( property_exists( $this, $key ) ) {
			return $this->$key;
		}

		return null;
	}

	public function __set( $key, $value ) {
		switch ( $key ) {
			case 'id':
				return false;
				break;

			case 'sort':
				$value      = absint( $value );
				$this->$key = $value;
				break;

			case 'value':
				if( null === $value ) $value = '';
				$this->$key = $value;
				break;

			default:
				if ( property_exists( $this, $key ) ) {
					$this->$key = $value;
				}
		}
	}

	public function __isset( $key ) {
		if ( property_exists( $this, $key ) ) {
			return true;
		}

		return false;
	}
}