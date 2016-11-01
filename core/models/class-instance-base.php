<?php
/**
 * Core: Torro_Instance_Base class
 *
 * @package TorroForms
 * @subpackage CoreModels
 * @version 1.0.0-beta.7
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms instance base class.
 *
 * This class is the base for every component-like class in the plugin that is represented in the database.
 *
 * @since 1.0.0-beta.1
 *
 * @property int $id
 */
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
					$this->$key = (int) $value;
					break;
				case 'double':
				case 'float':
					$this->$key = (float) $value;
					break;
				case 'string':
				default:
					$this->$key = (string) $value;
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

	protected function move( $superior_id ) {
		// this is protected as it's not needed for forms
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

	public function refresh() {
		if ( ! $this->id ) {
			return false;
		}

		$this->populate( $this->id );

		return true;
	}

	protected function populate( $id ) {
		global $wpdb;

		if ( ! $this->table_name ) {
			return;
		}

		$data = false;
		if ( is_object( $id ) ) {
			if ( ! isset( $id->id ) ) {
				return;
			}

			$okay = false;
			if ( isset( $id->{$this->superior_id_name} ) ) {
				$okay = true;
				foreach ( $this->valid_args as $arg => $type ) {
					if ( ! isset( $id->$arg ) ) {
						$okay = false;
						break;
					}
				}
			}
			if ( $okay ) {
				$data = $id;
			} else {
				$id = $id->id;
			}
		}

		if ( ! $data ) {
			$table_name = $wpdb->{$this->table_name};
			$data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", absint( $id ) ) );
			if ( 0 === $wpdb->num_rows ) {
				return;
			}
		}

		$this->id = absint( $data->id );
		if ( $this->superior_id_name ) {
			$this->superior_id = absint( $data->{$this->superior_id_name} );
		}
		foreach ( $this->valid_args as $arg => $type ) {
			switch ( $type ) {
				case 'int':
					$this->$arg = (int) $data->$arg;
					break;
				case 'double':
				case 'float':
					$this->$arg = (float) $data->$arg;
					break;
				case 'string':
				default:
					$this->$arg = (string) $data->$arg;
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
			if ( false === $status ) {
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
