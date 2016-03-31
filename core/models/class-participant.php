<?php

/**
 * Participant base class
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

class Torro_Participant {

	private $id;

	private $form_id;

	private $user_id;

	private $user;

	public function __construct( $id ) {
		$this->populate( $id );
	}

	private function populate( $id ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->torro_participants} WHERE id = %d", absint( $id ) );

		$participant = $wpdb->get_row( $sql );

		if ( 0 !== $wpdb->num_rows ) {
			$this->id      = $participant->id;
			$this->form_id = $participant->form_id;
			$this->user_id = $participant->user_id;

			$this->user = get_user_by( 'ID', $this->user_id );
		}
	}

	public function save() {
		global $wpdb;

		if ( ! empty( $this->id ) ) {
			return $wpdb->update( $wpdb->torro_participants, array(
				'form_id' => $this->form_id,
				'user_id' => $this->user_id,
			), array(
				                      'id' => $this->id
			                      ) );
		} else {
			$wpdb->insert( $wpdb->torro_participants, array(
				'form_id' => $this->form_id,
				'user_id' => $this->user_id,
			) );

			return $wpdb->insert_id;
		}
	}

	public function delete() {
		global $wpdb;

		if ( ! empty( $this->id ) ) {
			return $wpdb->delete( $wpdb->torro_participants, array( 'id' => $this->id ) );
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
