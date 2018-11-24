<?php
/**
 * Model status manager class for Core objects
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Status_Managers;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Status_Manager;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Status;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Status_Managers\Core_Model_Status_Manager' ) ) :

	/**
	 * Base class for a core model status
	 *
	 * This class represents a general core model status.
	 *
	 * @since 1.0.0
	 */
	abstract class Core_Model_Status_Manager extends Model_Status_Manager {
		/**
		 * Slug of the default status.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $default = '';

		/**
		 * Registers a new status.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug for the status.
		 * @param array  $args Optional. Array of status arguments. Default empty.
		 * @return bool True on success, false on failure.
		 */
		public function register( $slug, $args = array() ) {
			if ( isset( $this->items[ $slug ] ) ) {
				return false;
			}

			$status = $this->register_in_core( $slug, $args );
			if ( ! $status ) {
				return false;
			}

			$this->get( $slug );

			return true;
		}

		/**
		 * Retrieves a status object.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug of the status.
		 * @return Model_Status|null Status object, or null it it does not exist.
		 */
		public function get( $slug ) {
			if ( isset( $this->items[ $slug ] ) ) {
				return $this->items[ $slug ];
			}

			$status_object = $this->get_from_core( $slug );
			if ( ! $status_object ) {
				return null;
			}

			$class_name = $this->item_class_name;

			$this->items[ $slug ] = new $class_name( $this, $slug, $status_object );

			return $this->items[ $slug ];
		}

		/**
		 * Retrieves a list of status objects.
		 *
		 * By default, all registered status objects will be returned.
		 * However, the result can be modified by specifying arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args {
		 *     Array of arguments for querying statuses. Any field available on the status can be passed
		 *     as key with a value to filter the result. Furthermore the following arguments may be
		 *     provided for additional tweaks.
		 *
		 *     @type string $operator The logical operation to perform the filter. Must be either
		 *                            'AND', 'OR' or 'NOT'. Default 'AND'.
		 *     @type string $field    Field from the objects to return instead of the entire objects.
		 *                            Only accepts 'slug' or 'name'. Default empty.
		 * }
		 * @return array A list of status objects or specific status object fields, depending on $args.
		 */
		public function query( $args = array() ) {
			foreach ( array( 'operator', 'field' ) as $arg ) {
				$$arg = '';
				if ( isset( $args[ $arg ] ) ) {
					$$arg = $args[ $arg ];
					unset( $args[ $arg ] );
				}
			}

			if ( ! in_array( strtolower( $operator ), array( 'or', 'not' ), true ) ) {
				$operator = 'and';
			}

			if ( ! empty( $field ) && 'name' !== $field ) {
				$field = 'name';
			}

			$status_names = $this->query_core( $args, 'names', $operator );

			$model_statuses = array();
			if ( empty( $field ) ) {
				foreach ( $status_names as $slug ) {
					$model_statuses[ $slug ] = $this->get( $slug );
				}
			} else {
				$model_statuses = array_combine( $status_names, $status_names );
			}

			return $model_statuses;
		}

		/**
		 * Unregisters an existing status.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug of the status.
		 * @return bool True on success, false on failure.
		 */
		public function unregister( $slug ) {
			$status = $this->unregister_in_core( $slug );
			if ( ! $status ) {
				return false;
			}

			if ( isset( $this->items[ $slug ] ) ) {
				unset( $this->items[ $slug ] );
			}

			return true;
		}

		/**
		 * Returns the slug of the default status.
		 *
		 * @since 1.0.0
		 *
		 * @return string Default status.
		 */
		public function get_default() {
			if ( ! empty( $this->default ) && null !== $this->get( $this->default ) ) {
				return $this->default;
			}

			return '';
		}

		/**
		 * Registers default statuses.
		 *
		 * @since 1.0.0
		 */
		protected function register_defaults() {
			/* Default core statuses already exist. */
		}

		/**
		 * Registers a new status in Core.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug for the status.
		 * @param array  $args Optional. Array of status arguments. Default empty.
		 * @return bool True on success, false on failure.
		 */
		abstract protected function register_in_core( $slug, $args = array() );

		/**
		 * Retrieves a status object from Core.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug of the status.
		 * @return object|null Status object, or null it it does not exist.
		 */
		abstract protected function get_from_core( $slug );

		/**
		 * Retrieves a list of status objects.
		 *
		 * By default, all registered status objects will be returned.
		 * However, the result can be modified by specifying arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param array|string $args     Optional. An array of key => value arguments
		 *                               to match against the status objects. Default
		 *                               empty array.
		 * @param string       $output   Optional. The type of output to return. Accepts
		 *                               type 'names' or 'objects'. Default 'names'.
		 * @param string       $operator Optional. The logical operation to perform. 'or'
		 *                               means only one element from the array needs to match;
		 *                               'and' means all elements must match; 'not' means no
		 *                               elements may match. Default 'and'.
		 * @return array A list of status names or objects.
		 */
		abstract protected function query_core( $args = array(), $output = 'names', $operator = 'and' );

		/**
		 * Unregisters an existing status in Core.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug of the status.
		 * @return bool True on success, false on failure.
		 */
		abstract protected function unregister_in_core( $slug );
	}

endif;
