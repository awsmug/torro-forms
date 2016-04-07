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

	protected $table_name = false;

	protected $superior_id_name = false;

	protected $manager_method = false;

	protected $valid_args = array();

	public function __construct( $id = null ) {
		parent::__construct();

		if ( $id ) {
			$this->populate( absint( $id ) );
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
			case 'table_name':
			case 'superior_id_name':
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
			case 'table_name':
			case 'superior_id_name':
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
			case 'table_name':
			case 'superior_id_name':
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
			if ( ! isset( $this->valid_args[ $key ] ) ) {
				continue;
			}

			$type = $this->valid_args[ $key ];
			switch ( $type ) {
				case 'int':
					$this->$key = intval( $value );
					break;
				case 'double':
				case 'float':
					$this->$key = floatval( $value );
					break;
				case 'string':
				default:
					$this->$key = strval( $value );
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
		foreach ( $this->valid_args as $arg => $type ) {
			if ( property_exists( $this, $arg ) ) {
				$args[ $arg ] = $this->$arg;
			}
		}

		return call_user_func( array( torro(), $this->manager_method ) )->create( $superior_id, $args );
	}

	protected function populate( $id ) {
		global $wpdb;

		if ( ! $this->table_name ) {
			return;
		}

		$table_name = $wpdb->{$this->table_name};

		$data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $id ) );
		if ( 0 < $wpdb->num_rows ) {
			$this->id = absint( $data->id );
			if ( $this->superior_id_name ) {
				$this->superior_id = absint( $data->{$this->superior_id_name} );
			}
			foreach ( $this->valid_args as $arg => $type ) {
				switch ( $type ) {
					case 'int':
						$this->$arg = intval( $data->$arg );
						break;
					case 'double':
					case 'float':
						$this->$arg = floatval( $data->$arg );
						break;
					case 'string':
					default:
						$this->$arg = strval( $data->$arg );
				}
			}
		}
	}

	protected function exists_in_db() {
		global $wpdb;

		if ( ! $this->table_name ) {
			return false;
		}

		$table_name = $wpdb->{$this->table_name};

		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$table_name} WHERE id = %d", $this->id ) );

		return 0 < $count;
	}

	protected function save_to_db() {
		global $wpdb;

		if ( ! $this->table_name ) {
			return new Torro_Error( 'no_db_table', __( 'Cannot save to database since no table name is provided.', 'torro-forms' ), __METHOD__ );
		}

		$table_name = $wpdb->{$this->table_name};

		$args = array();
		$args_format = array();
		if ( $this->superior_id_name ) {
			$args[ $this->superior_id_name ] = $this->superior_id;
			$args_format[] = '%d';
		}
		foreach ( $this->valid_args as $arg => $type ) {
			$args[ $arg ] = $this->$arg;
			switch ( $type ) {
				case 'int':
					$args_format[] = '%d';
					break;
				case 'double':
				case 'float':
					$args_format[] = '%f';
					break;
				case 'string':
				default:
					$args_format[] = '%s';
			}
		}

		if ( $this->id ) {
			$status = $wpdb->update( $table_name, $args, array( 'id' => $this->id ), $args_format, array( '%d' ) );
			if ( ! $status ) {
				return new Torro_Error( 'cannot_update_db', __( 'Could not update item in the database.', 'torro-forms' ), __METHOD__ );
			}
		} else {
			$status = $wpdb->insert( $table_name, $args, $args_format );
			if ( ! $status ) {
				return new Torro_Error( 'cannot_insert_db', __( 'Could not insert item into the database.', 'torro-forms' ), __METHOD__ );
			}
			$this->id = absint( $wpdb->insert_id );
		}

		return $this->id;
	}

	protected function delete_from_db() {
		global $wpdb;

		if ( ! $this->table_name ) {
			return new Torro_Error( 'no_db_table', __( 'Cannot delete from database since no table name is provided.', 'torro-forms' ), __METHOD__ );
		}

		$table_name = $wpdb->{$this->table_name};

		if ( ! $this->id ) {
			return new Torro_Error( 'cannot_delete_empty', __( 'Cannot delete item without ID from the database.', 'torro-forms' ), __METHOD__ );
		}

		return $wpdb->delete( $table_name, array( 'id' => $this->id ), array( '%d' ) );
	}
}
