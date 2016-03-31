<?php

/**
 * Element answer class
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

class Torro_Element_Setting {

	private $id = '';

	private $element_id = '';

	private $name = '';

	private $value = '';

	public function __construct( $id = null ) {
		$this->populate( $id );
	}

	private function populate( $id = null ) {
		global $wpdb;

		if ( ! empty( $id ) ) {
			$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->torro_element_settings} WHERE id = %d", absint( $id ) );

			$setting = $wpdb->get_row( $sql );

			if ( 0 !== $wpdb->num_rows ) {
				$this->id         = $setting->id;
				$this->element_id = $setting->element_id;
				$this->name       = $setting->name;
				$this->value      = $setting->value;
			}
		}
	}

	public function save() {
		global $wpdb;

		if ( ! empty( $this->id ) ) {
			return $wpdb->update( $wpdb->torro_element_settings, array(
				                                           'element_id' => $this->element_id,
				                                           'name'       => $this->name,
				                                           'value'      => $this->value,
			                                           ), array(
				'id' => $this->id
			) );
		} else {
			$wpdb->insert( $wpdb->torro_element_settings, array(
				'element_id' => $this->element_id,
				'name'       => $this->name,
				'value'      => $this->value
			) );

			return $wpdb->insert_id;
		}
	}

	public function delete() {
		global $wpdb;

		if ( ! empty( $this->id ) ) {
			return $wpdb->delete( $wpdb->torro_element_settings, array( 'id' => $this->id ) );
		}

		return false;
	}

	/**
	 * Magic getter function
	 *
	 * @param $key
	 *
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function __get( $key ) {
		if ( property_exists( $this, $key ) ) {
			return $this->$key;
		}

		return null;
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
			default:
				if ( property_exists( $this, $key ) ) {
					$this->$key = $value;
				}
		}
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
