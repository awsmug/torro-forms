<?php
/**
 * Manager class for users
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Managers;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Storage;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Date_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Meta_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Title_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Models\User;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\User_Collection;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Queries\User_Query;
use Leaves_And_Love\Plugin_Lib\Translations\Translations_User_Manager;
use Leaves_And_Love\Plugin_Lib\DB;
use Leaves_And_Love\Plugin_Lib\Cache;
use Leaves_And_Love\Plugin_Lib\Meta;
use Leaves_And_Love\Plugin_Lib\Error_Handler;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Managers\User_Manager' ) ) :

	/**
	 * Class for a users manager
	 *
	 * This class represents a users manager.
	 *
	 * @since 1.0.0
	 */
	class User_Manager extends Core_Manager {
		use Date_Manager_Trait, Meta_Manager_Trait, Title_Manager_Trait;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string                    $prefix       The instance prefix.
		 * @param array                     $services     {
		 *     Array of service instances.
		 *
		 *     @type DB            $db            The database instance.
		 *     @type Cache         $cache         The cache instance.
		 *     @type Meta          $meta          The meta instance.
		 *     @type Error_Handler $error_handler The error handler instance.
		 * }
		 * @param Translations_User_Manager $translations Translations instance.
		 */
		public function __construct( $prefix, $services, $translations ) {
			$this->class_name            = User::class;
			$this->collection_class_name = User_Collection::class;
			$this->query_class_name      = User_Query::class;

			$this->singular_slug = 'user';
			$this->plural_slug   = 'users';

			$this->table_name       = 'users';
			$this->cache_group      = 'users';
			$this->meta_type        = 'user';
			$this->fetch_callback   = 'get_userdata';
			$this->primary_property = 'ID';
			$this->date_property    = 'user_registered';
			$this->title_property   = 'display_name';

			Storage::register_global_group( $this->cache_group );

			parent::__construct( $prefix, $services, $translations );
		}

		/**
		 * Internal method to insert a new user into the database.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args Array of column => value pairs for the new database row.
		 * @return int|false The ID of the new user, or false on failure.
		 */
		protected function insert_into_db( $args ) {
			$result = wp_insert_user( $args );
			if ( is_wp_error( $result ) ) {
				return false;
			}

			return $result;
		}

		/**
		 * Internal method to update an existing user in the database.
		 *
		 * @since 1.0.0
		 *
		 * @param int   $user_id ID of the user to update.
		 * @param array $args    Array of column => value pairs to update in the database row.
		 * @return bool True on success, or false on failure.
		 */
		protected function update_in_db( $user_id, $args ) {
			$args['ID'] = $user_id;

			$result = wp_update_user( $args );
			if ( is_wp_error( $result ) ) {
				return false;
			}

			return $result;
		}

		/**
		 * Internal method to delete a user from the database.
		 *
		 * @since 1.0.0
		 *
		 * @param int $user_id ID of the user to delete.
		 * @return bool True on success, or false on failure.
		 */
		protected function delete_from_db( $user_id ) {
			if ( ! function_exists( 'wp_delete_user' ) ) {
				require_once ABSPATH . 'wp-admin/includes/user.php';
			}

			return wp_delete_user( $user_id );
		}
	}

endif;
