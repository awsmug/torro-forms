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

class Torro_Element_Setting extends Torro_Instance_Base {

	protected $name = '';

	protected $value = '';

	public function __construct( $id = null ) {
		parent::__construct( $id );
	}

	protected function init() {
		$this->superior_id_name = 'element_id';
		$this->manager_method = 'element_settings';
		$this->valid_args = array( 'name', 'value' );
	}

	protected function populate( $id ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->torro_element_settings} WHERE id = %d", absint( $id ) );

		$setting = $wpdb->get_row( $sql );

		if ( 0 !== $wpdb->num_rows ) {
			$this->id          = $setting->id;
			$this->superior_id = $setting->element_id;
			$this->name        = $setting->name;
			$this->value       = $setting->value;
		}
	}

	protected function exists_in_db() {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT COUNT( id ) FROM {$wpdb->torro_element_settings} WHERE id = %d", $this->id );
		$var = $wpdb->get_var( $sql );

		if ( $var > 0 ) {
			return true;
		}

		return false;
	}

	protected function save_to_db() {
		global $wpdb;

		if ( ! empty( $this->id ) ) {
			$status = $wpdb->update( $wpdb->torro_element_settings, array(
				'element_id' => $this->superior_id,
				'name'       => $this->name,
				'value'      => $this->value,
			), array(
				'id' => $this->id
			) );
			if ( ! $status ) {
				return new Torro_Error( 'cannot_update_db', __( 'Could not update element setting in the database.', 'torro-forms' ), __METHOD__ );
			}
		} else {
			$status = $wpdb->insert( $wpdb->torro_element_settings, array(
				'element_id' => $this->superior_id,
				'name'       => $this->name,
				'value'      => $this->value
			) );
			if ( ! $status ) {
				return new Torro_Error( 'cannot_insert_db', __( 'Could not insert element setting into the database.', 'torro-forms' ), __METHOD__ );
			}

			$this->id = $wpdb->insert_id;
		}

		return $this->id;
	}

	protected function delete_from_db() {
		global $wpdb;

		if ( empty( $this->id ) ) {
			return new Torro_Error( 'cannot_delete_empty', __( 'Cannot delete element setting without ID.', 'torro-forms' ), __METHOD__ );
		}

		return $wpdb->delete( $wpdb->torro_element_settings, array( 'id' => $this->id ) );
	}
}
