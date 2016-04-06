<?php
/**
 * Torro Forms instance base class.
 *
 * This class is the base for every component-like class in the plugin that is represented in the database.
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 1.0.0alpha1
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

abstract class Torro_Instance_Base extends Torro_Base {
	protected $id = 0;

	protected $superior_id = 0;

	protected $superior_id_name = false;

	protected $manager_method = false;

	protected $valid_args = array();

	public function __construct( $id = null ) {
		parent::__construct();

		if ( $id ) {
			$this->populate( $id );
		}
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
			case 'manager_method':
			case 'valid_args':
				break;
			default:
				if ( $this->superior_id_name && $key === $this->superior_id_name ) {
					$this->superior_id = $value;
					return;
				}
				parent::__set( $key, $value );
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
		switch ( $key ) {
			case 'manager_method':
			case 'valid_args':
				return null;
			default:
				if ( $this->superior_id_name && $key === $this->superior_id_name ) {
					return $this->superior_id;
				}
				return parent::__get( $key );
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
		switch ( $key ) {
			case 'manager_method':
			case 'valid_args':
				return false;
			default:
				if ( $this->superior_id_name && $key === $this->superior_id_name ) {
					return true;
				}
				return parent::__isset( $key );
		}
	}

	public function exists() {
		if ( empty( $this->id ) ) {
			return false;
		}

		return $this->exists_in_db();
	}

	public function update( $args = array() ) {
		foreach ( $args as $key => $value ) {
			if ( in_array( $key, $this->valid_args, true ) && property_exists( $this, $key ) ) {
				$this->$key = $value;
			}
		}

		return $this->save_to_db();
	}

	public function delete() {
		$status = $this->delete_from_db();
		if ( is_wp_error( $status ) ) {
			return $status;
		}
		if ( ! $status ) {
			return new Torro_Error( 'cannot_delete_instance', __( 'Unknown error while trying to delete instance.', 'torro-forms' ), __METHOD__ );
		}
		return $status;
	}

	public function move( $superior_id ) {
		$this->superior_id = $superior_id;

		return $this->save_to_db();
	}

	public function copy( $superior_id ) {
		if ( ! $this->manager_method || ! is_callable( array( torro(), $this->manager_method ) ) ) {
			return new Torro_Error( 'invalid_manager_method', __( 'Invalid manager retrieval method.', 'torro-forms' ), __METHOD__ );
		}

		$args = array();
		foreach ( $this->valid_args as $arg ) {
			if ( property_exists( $this, $arg ) ) {
				$args[ $arg ] = $this->$arg;
			}
		}

		return call_user_func( array( torro(), $this->manager_method ) )->create( $superior_id, $args );
	}

	protected abstract function populate( $id );

	protected abstract function exists_in_db();

	protected abstract function save_to_db();

	protected abstract function delete_from_db();
}
