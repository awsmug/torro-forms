<?php
/**
 * Manager class for taxonomies
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Managers;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Types\Taxonomy;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Managers\Taxonomy_Manager' ) ) :

	/**
	 * Class for a taxonomies manager
	 *
	 * This class represents a taxonomies manager.
	 *
	 * @since 1.0.0
	 */
	class Taxonomy_Manager extends Core_Model_Type_Manager {
		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prefix   The instance prefix.
		 */
		public function __construct( $prefix ) {
			$this->item_class_name = Taxonomy::class;

			$this->default = 'category';

			parent::__construct( $prefix );
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
		protected function register_in_core( $slug, $args = array() ) {
			$object_type = array();
			if ( isset( $args['object_type'] ) ) {
				$object_type = $args['object_type'];
				unset( $args['object_type'] );
			}

			$status = register_taxonomy( $slug, $object_type, $args );
			if ( is_wp_error( $status ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Retrieves a type object from Core.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug of the type.
		 * @return object|null Type object, or null it it does not exist.
		 */
		protected function get_from_core( $slug ) {
			$type_object = get_taxonomy( $slug );
			if ( ! $type_object ) {
				return null;
			}

			return $type_object;
		}

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
		protected function query_core( $args = array(), $output = 'names', $operator = 'and' ) {
			return get_taxonomies( $args, $output, $operator );
		}

		/**
		 * Unregisters an existing type in Core.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug of the type.
		 * @return bool True on success, false on failure.
		 */
		protected function unregister_in_core( $slug ) {
			$status = unregister_taxonomy( $slug );
			if ( is_wp_error( $status ) ) {
				return false;
			}

			return true;
		}
	}

endif;
