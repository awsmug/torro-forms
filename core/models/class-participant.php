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

class Torro_Participant extends Torro_Instance_Base {

	protected $user_id;

	protected $user;

	public function __construct( $id = null ) {
		parent::__construct( $id );
	}

	protected function init() {
		$this->superior_id_name = 'form_id';
		$this->manager_method = 'participants';
		$this->valid_args = array( 'user_id' );
	}

	protected function populate( $id ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->torro_participants} WHERE id = %d", absint( $id ) );

		$participant = $wpdb->get_row( $sql );

		if ( 0 !== $wpdb->num_rows ) {
			$this->id          = $participant->id;
			$this->superior_id = $participant->form_id;
			$this->user_id     = $participant->user_id;

			$this->user = $this->populate_user();
		}
	}

	protected function exists_in_db() {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT COUNT( id ) FROM {$wpdb->torro_participants} WHERE id = %d", $this->id );
		$var = $wpdb->get_var( $sql );

		if ( $var > 0 ) {
			return true;
		}

		return false;
	}

	protected function save_to_db() {
		global $wpdb;

		if ( ! empty( $this->id ) ) {
			$status = $wpdb->update( $wpdb->torro_participants, array(
				'form_id' => $this->superior_id,
				'user_id' => $this->user_id,
			), array(
				'id' => $this->id
			) );
			if ( ! $status ) {
				return new Torro_Error( 'cannot_update_db', __( 'Could not update participant in the database.', 'torro-forms' ), __METHOD__ );
			}
		} else {
			$status = $wpdb->insert( $wpdb->torro_participants, array(
				'form_id' => $this->superior_id,
				'user_id' => $this->user_id,
			) );
			if ( ! $status ) {
				return new Torro_Error( 'cannot_insert_db', __( 'Could not insert participant into the database.', 'torro-forms' ), __METHOD__ );
			}

			$this->id = $wpdb->insert_id;
		}

		return $this->id;
	}

	protected function delete_from_db() {
		global $wpdb;

		if ( empty( $this->id ) ) {
			return new Torro_Error( 'cannot_delete_empty', __( 'Cannot delete participant without ID.', 'torro-forms' ), __METHOD__ );
		}

		return $wpdb->delete( $wpdb->torro_participants, array( 'id' => $this->id ) );
	}

	private function populate_user() {
		return get_user_by( 'id', $this->user_id );
	}
}
