<?php
/**
 * Manager class for post statuses
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Status_Managers;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Statuses\Post_Status;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Status_Managers\Post_Status_Manager' ) ) :

	/**
	 * Class for a post statuses manager
	 *
	 * This class represents a post statuses manager.
	 *
	 * @since 1.0.0
	 */
	class Post_Status_Manager extends Core_Model_Status_Manager {
		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prefix The instance prefix.
		 */
		public function __construct( $prefix ) {
			$this->item_class_name = Post_Status::class;

			$this->default = 'draft';

			parent::__construct( $prefix );
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
		protected function register_in_core( $slug, $args = array() ) {
			$status_object = register_post_status( $slug, $args );
			if ( is_wp_error( $status_object ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Retrieves a status object from Core.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug of the status.
		 * @return object|null Status object, or null it it does not exist.
		 */
		protected function get_from_core( $slug ) {
			return get_post_status_object( $slug );
		}

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
		protected function query_core( $args = array(), $output = 'names', $operator = 'and' ) {
			return get_post_stati( $args, $output, $operator );
		}

		/**
		 * Unregisters an existing status in Core.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug of the status.
		 * @return bool True on success, false on failure.
		 */
		protected function unregister_in_core( $slug ) {
			return false;
		}
	}

endif;
