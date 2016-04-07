<?php
/**
 * Torro Forms classes manager class
 *
 * This abstract class holds and manages all class instances.
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

abstract class Torro_Instance_Manager extends Torro_Manager {

	protected $table_name = false;

	protected $class_name = false;

	protected function __construct() {
		parent::__construct();
		$this->init();
	}

	public function create( $superior_id, $args = array() ) {
		$instance = $this->create_raw( $args );
		if ( $superior_id ) {
			$instance->superior_id = absint( $superior_id );
		} else {
			$superior_id = 0;
		}
		$id = $instance->update( $args );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		$this->instances[ $this->get_category() ][ $instance->id ] = $instance;

		return $instance;
	}

	public function exists( $id ) {
		$instance = $this->get( $id );
		if ( is_wp_error( $instance ) ) {
			return false;
		}
		return true;
	}

	public function update( $id, $args = array() ) {
		$instance = $this->get( $id );
		if ( is_wp_error( $instance ) ) {
			return $instance;
		}
		$id = $instance->update( $args );
		if ( is_wp_error( $id ) ) {
			return $id;
		}

		return $instance;
	}

	public function get( $id ) {
		if ( ! $id ) {
			return new Torro_Error( 'missing_id', __( 'An ID must be provided when calling a get() method.', 'torro-forms' ), __METHOD__ );
		}
		$_id = $id;
		if ( is_object( $id ) && isset( $id->id ) ) {
			$_id = $id->id;
		}

		$_id = absint( $_id );

		if ( ! isset( $this->instances[ $this->get_category() ][ $_id ] ) ) {
			// get from database
			$instance = $this->get_from_db( $id );
			if ( is_wp_error( $instance ) ) {
				return $instance;
			}
			if ( ! $instance ) {
				return new Torro_Error( 'torro_instance_not_exist', sprintf( __( 'The instance %s does not exist.', 'torro-forms' ), $_id ), __METHOD__ );
			}
			$this->instances[ $this->get_category() ][ $_id ] = $instance;
		}
		return $this->instances[ $this->get_category() ][ $_id ];
	}

	public function query( $args = array() ) {
		global $wpdb;

		if ( ! $this->table_name ) {
			return array();
		}

		$args = wp_parse_args( $args, array(
			'number'	=> 10,
			'offset'	=> 0,
		) );

		$number = intval( $args['number'] );
		$offset = intval( $args['offset'] );
		unset( $args['number'] );
		unset( $args['offset'] );

		if ( 0 === $number ) {
			return 0;
		}

		$table_name = $wpdb->{$this->table_name};

		$query = "SELECT * FROM {$table_name}";

		$keys = array();
		$values = array();
		foreach ( $args as $key => $value ) {
			if ( is_int( $value ) ) {
				$keys[] = $key . ' = %d';
			} elseif ( is_float( $value ) ) {
				$keys[] = $key . ' = %f';
			} else {
				$keys[] = $key . ' = %s';
			}
			$values[] = $value;
		}

		if ( 0 < count( $keys ) ) {
			$query .= " WHERE " . implode( " AND ", $keys );
		}

		if ( 0 < $number ) {
			$query .= " LIMIT " . $offset . ", " . $number;
		}

		if ( 0 < count( $values ) ) {
			array_unshift( $values, $query );
			$query = call_user_func_array( array( $wpdb, 'prepare' ), $values );
		}

		$results = $wpdb->get_results( $query );

		return array_map( array( $this, 'get' ), $results );
	}

	public function move( $id, $superior_id ) {
		$instance = $this->get( $id );
		if ( is_wp_error( $instance ) ) {
			return $instance;
		}
		$id = $instance->move( $superior_id );
		if ( is_wp_error( $id ) ) {
			return $id;
		}

		return $instance;
	}

	public function copy( $id, $superior_id ) {
		$instance = $this->get( $id );
		if ( is_wp_error( $instance ) ) {
			return $instance;
		}
		return $instance->copy( $superior_id );
	}

	public function delete( $id ) {
		$instance = $this->get( $id );
		if ( is_wp_error( $instance ) ) {
			return $instance;
		}

		// delete instance from database
		$status = $instance->delete();
		if ( is_wp_error( $status ) ) {
			return $status;
		}

		unset( $this->instances[ $this->get_category() ][ $id ] );

		return $instance;
	}

	public function delete_by_query( $args = array() ) {
		$instances = $this->query( $args );
		foreach ( $instances as $instance ) {
			$id = $instance->id;
			$status = $instance->delete();
			if ( ! is_wp_error( $status ) ) {
				unset( $this->instances[ $this->get_category() ][ $id ] );
			}
		}

		return $instances;
	}

	protected abstract function init();

	protected function create_raw( $args = array() ) {
		if ( ! $this->class_name ) {
			return new Torro_Error( 'no_class_name', __( 'No class name provided. Cannot create raw instance.', 'torro-forms' ), __METHOD__ );
		}

		$class_name = $this->class_name;

		return new $class_name();
	}

	protected function get_from_db( $id ) {
		if ( ! $this->class_name ) {
			return new Torro_Error( 'no_class_name', __( 'No class name provided. Cannot create raw instance.', 'torro-forms' ), __METHOD__ );
		}

		$class_name = $this->class_name;

		$instance = new $class_name( $id );
		if ( ! $instance->id ) {
			return false;
		}

		return $instance;
	}
}
