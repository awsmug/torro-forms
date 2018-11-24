<?php
/**
 * Model type manager class for Core objects
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Managers;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Manager;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Managers\Core_Model_Type_Manager' ) ) :

	/**
	 * Base class for a core model type
	 *
	 * This class represents a general core model type.
	 *
	 * @since 1.0.0
	 */
	abstract class Core_Model_Type_Manager extends Model_Type_Manager {
		/**
		 * Slug of the default type.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $default = '';

		/**
		 * Registers a new type.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug for the type.
		 * @param array  $args Optional. Array of type arguments. Default empty.
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
		 * Retrieves a type object.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug of the type.
		 * @return Model_Type|null Type object, or null it it does not exist.
		 */
		public function get( $slug ) {
			if ( isset( $this->items[ $slug ] ) ) {
				return $this->items[ $slug ];
			}

			$type_object = $this->get_from_core( $slug );
			if ( ! $type_object ) {
				return null;
			}

			$class_name = $this->item_class_name;

			$this->items[ $slug ] = new $class_name( $this, $slug, $type_object );

			return $this->items[ $slug ];
		}

		/**
		 * Retrieves a list of type objects.
		 *
		 * By default, all registered type objects will be returned.
		 * However, the result can be modified by specifying arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args {
		 *     Array of arguments for querying types. Any field available on the type can be passed
		 *     as key with a value to filter the result. Furthermore the following arguments may be
		 *     provided for additional tweaks.
		 *
		 *     @type string $operator The logical operation to perform the filter. Must be either
		 *                            'AND', 'OR' or 'NOT'. Default 'AND'.
		 *     @type string $field    Field from the objects to return instead of the entire objects.
		 *                            Only accepts 'slug' or 'name'. Default empty.
		 * }
		 * @return array A list of type objects or specific type object fields, depending on $args.
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

			$type_names = $this->query_core( $args, 'names', $operator );

			$model_types = array();
			if ( empty( $field ) ) {
				foreach ( $type_names as $slug ) {
					$model_types[ $slug ] = $this->get( $slug );
				}
			} else {
				$model_types = array_combine( $type_names, $type_names );
			}

			return $model_types;
		}

		/**
		 * Unregisters an existing type.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug of the type.
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
		 * Returns the slug of the default type.
		 *
		 * @since 1.0.0
		 *
		 * @return string Default type.
		 */
		public function get_default() {
			if ( ! empty( $this->default ) && null !== $this->get( $this->default ) ) {
				return $this->default;
			}

			return '';
		}

		/**
		 * Registers default types.
		 *
		 * @since 1.0.0
		 */
		protected function register_defaults() {
			/* Default core types already exist. */
		}

		/**
		 * Registers a new type in Core.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug for the type.
		 * @param array  $args Optional. Array of type arguments. Default empty.
		 * @return bool True on success, false on failure.
		 */
		abstract protected function register_in_core( $slug, $args = array() );

		/**
		 * Retrieves a type object from Core.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug of the type.
		 * @return object|null Type object, or null it it does not exist.
		 */
		abstract protected function get_from_core( $slug );

		/**
		 * Retrieves a list of type objects.
		 *
		 * By default, all registered type objects will be returned.
		 * However, the result can be modified by specifying arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param array|string $args     Optional. An array of key => value arguments
		 *                               to match against the type objects. Default
		 *                               empty array.
		 * @param string       $output   Optional. The type of output to return. Accepts
		 *                               type 'names' or 'objects'. Default 'names'.
		 * @param string       $operator Optional. The logical operation to perform. 'or'
		 *                               means only one element from the array needs to match;
		 *                               'and' means all elements must match; 'not' means no
		 *                               elements may match. Default 'and'.
		 * @return array A list of type names or objects.
		 */
		abstract protected function query_core( $args = array(), $output = 'names', $operator = 'and' );

		/**
		 * Unregisters an existing type in Core.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug of the type.
		 * @return bool True on success, false on failure.
		 */
		abstract protected function unregister_in_core( $slug );
	}

endif;
