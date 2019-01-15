<?php
/**
 * Manager class for posts
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Managers;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Date_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Meta_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Title_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Content_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Slug_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Type_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Status_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Author_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Post;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Post_Collection;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Queries\Post_Query;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Managers\Post_Type_Manager;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Status_Managers\Post_Status_Manager;
use Leaves_And_Love\Plugin_Lib\Translations\Translations_Post_Manager;
use Leaves_And_Love\Plugin_Lib\DB;
use Leaves_And_Love\Plugin_Lib\Cache;
use Leaves_And_Love\Plugin_Lib\Meta;
use Leaves_And_Love\Plugin_Lib\Error_Handler;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Managers\Post_Manager' ) ) :

	/**
	 * Class for a posts manager
	 *
	 * This class represents a posts manager.
	 *
	 * @since 1.0.0
	 */
	class Post_Manager extends Core_Manager {
		use Date_Manager_Trait, Meta_Manager_Trait, Title_Manager_Trait, Content_Manager_Trait, Slug_Manager_Trait, Type_Manager_Trait, Status_Manager_Trait, Author_Manager_Trait;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string                    $prefix       The instance prefix.
		 * @param array                     $services     {
		 *     Array of service instances.
		 *
		 *     @type DB                  $db            The database instance.
		 *     @type Cache               $cache         The cache instance.
		 *     @type Meta                $meta          The meta instance.
		 *     @type Post_Type_Manager   $types         The type manager instance.
		 *     @type Post_Status_Manager $statuses      The status manager instance.
		 *     @type Error_Handler       $error_handler The error handler instance.
		 * }
		 * @param Translations_Post_Manager $translations Translations instance.
		 */
		public function __construct( $prefix, $services, $translations ) {
			$this->class_name            = Post::class;
			$this->collection_class_name = Post_Collection::class;
			$this->query_class_name      = Post_Query::class;

			$this->singular_slug = 'post';
			$this->plural_slug   = 'posts';

			$this->table_name       = 'posts';
			$this->cache_group      = 'posts';
			$this->meta_type        = 'post';
			$this->fetch_callback   = 'get_post';
			$this->primary_property = 'ID';
			$this->date_property    = 'post_date';
			$this->title_property   = 'post_title';
			$this->content_property = 'post_content';
			$this->slug_property    = 'post_name';
			$this->type_property    = 'post_type';
			$this->status_property  = 'post_status';
			$this->author_property  = 'post_author';

			$this->public = true;

			$this->secondary_date_properties = array( 'post_modified' );

			parent::__construct( $prefix, $services, $translations );
		}

		/**
		 * Internal method to insert a new post into the database.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args Array of column => value pairs for the new database row.
		 * @return int|false The ID of the new post, or false on failure.
		 */
		protected function insert_into_db( $args ) {
			$result = wp_insert_post( $args, true );
			if ( is_wp_error( $result ) ) {
				return false;
			}

			return $result;
		}

		/**
		 * Internal method to update an existing post in the database.
		 *
		 * @since 1.0.0
		 *
		 * @param int   $post_id ID of the post to update.
		 * @param array $args    Array of column => value pairs to update in the database row.
		 * @return bool True on success, or false on failure.
		 */
		protected function update_in_db( $post_id, $args ) {
			$args['ID'] = $post_id;

			$result = wp_update_post( $args, true );
			if ( is_wp_error( $result ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Internal method to delete a post from the database.
		 *
		 * @since 1.0.0
		 *
		 * @param int $post_id ID of the post to delete.
		 * @return bool True on success, or false on failure.
		 */
		protected function delete_from_db( $post_id ) {
			return (bool) wp_delete_post( $post_id, true );
		}
	}

endif;
