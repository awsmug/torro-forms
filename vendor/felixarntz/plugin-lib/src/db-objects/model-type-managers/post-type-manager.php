<?php
/**
 * Manager class for post types
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Managers;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Types\Post_Type;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Managers\Post_Type_Manager' ) ) :

	/**
	 * Class for a post types manager
	 *
	 * This class represents a post types manager.
	 *
	 * @since 1.0.0
	 */
	class Post_Type_Manager extends Core_Model_Type_Manager {
		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prefix The instance prefix.
		 */
		public function __construct( $prefix ) {
			$this->item_class_name = Post_Type::class;

			$this->default = 'post';

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
			$type_object = register_post_type( $slug, $args );
			if ( is_wp_error( $type_object ) ) {
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
			return get_post_type_object( $slug );
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
			return get_post_types( $args, $output, $operator );
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
			$status = unregister_post_type( $slug );
			if ( is_wp_error( $status ) ) {
				return false;
			}

			return true;
		}
	}

endif;
