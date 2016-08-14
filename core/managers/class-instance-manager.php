<?php
/**
 * Core: Torro_Instance_Manager class
 *
 * @package TorroForms
 * @subpackage CoreManagers
 * @version 1.0.0-beta.7
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms instance manager base class
 *
 * This base class holds and manages all class instances.
 *
 * @since 1.0.0-beta.1
 */
abstract class Torro_Instance_Manager {
	protected $instances = array();

	protected $table_name = false;

	protected $class_name = false;

	protected function __construct() {
		$this->init();
	}

	public function create( $superior_id, $args = array() ) {
		$instance = $this->create_raw( $args );
		if ( $superior_id ) {
			$instance->superior_id = absint( $superior_id );
		} else {
			$instance->superior_id = 0;
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
			'orderby'	=> 'none',
			'order'		=> 'ASC',
			'count'		=> false,
		) );

		$number = intval( $args['number'] );
		$offset = intval( $args['offset'] );
		$orderby = 'none' !== $args['orderby'] ? $args['orderby'] : '';
		$order = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';
		$count = $args['count'] ? true : false;
		unset( $args['number'] );
		unset( $args['offset'] );
		unset( $args['orderby'] );
		unset( $args['order'] );
		unset( $args['count'] );

		if ( 0 === $number ) {
			if ( $count ) {
				return 0;
			}

			return array();
		}

		$table_name = $wpdb->{$this->table_name};

		$fields = $count ? 'COUNT(*)' : '*';

		$query = "SELECT {$fields} FROM {$table_name}";

		$keys = array();
		$values = array();
		foreach ( $args as $key => $value ) {
			if ( is_array( $value ) ) {
				$value = array_values( $value );
				if ( 0 === count( $value ) ) {
					return array();
				}

				if ( 1 === count( $value ) ) {
					$value = $value[0];
				} else {
					$args = array();
					foreach ( $value as $v ) {
						if ( is_int( $v ) ) {
							$args[] = '%d';
						} elseif ( is_float( $v ) ) {
							$args[] = '%f';
						} else {
							$args[] = '%s';
						}
						$values[] = $v;
					}
					$keys[] = $key . ' IN (' . implode( ', ', $args ) . ')';
					continue;
				}
			}

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

		if ( $orderby ) {
			$query .= " ORDER BY " . $orderby . " " . $order;
		}

		if ( 0 < $number ) {
			$query .= " LIMIT " . $offset . ", " . $number;
		}

		if ( 0 < count( $values ) ) {
			array_unshift( $values, $query );
			$query = call_user_func_array( array( $wpdb, 'prepare' ), $values );
		}

		if ( $count ) {
			return (int) $wpdb->get_var( $query );
		}

		$results = $wpdb->get_results( $query );

		return array_map( array( $this, 'get' ), $results );
	}

	protected function move( $id, $superior_id ) {
		// this is protected as it's not needed for forms
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

	public function empty_cache() {
		$this->instances[ $this->get_category() ] = array();
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

	/**
	 * Getting category
	 *
	 * @return mixed
	 */
	protected abstract function get_category();
}
