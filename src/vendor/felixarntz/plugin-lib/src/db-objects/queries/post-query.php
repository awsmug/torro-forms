<?php
/**
 * Query class for posts
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Queries;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Post_Collection;
use WP_Query;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Queries\Post_Query' ) ) :

	/**
	 * Class for a posts query
	 *
	 * This class represents a posts query.
	 *
	 * @since 1.0.0
	 */
	class Post_Query extends Core_Query {
		/**
		 * Sets up the query for retrieving posts.
		 *
		 * @since 1.0.0
		 *
		 * @param string|array $query Array or query string of post query arguments. See `WP_Query::parse_query()` for a list
		 *                            of supported arguments.
		 * @return Post_Collection Collection of posts.
		 */
		public function query( $query ) {
			if ( isset( $query['fields'] ) && 'ids' !== $query['fields'] ) {
				$query['fields'] = '';
			}

			return parent::query( $query );
		}

		/**
		 * Instantiates the internal Core query object.
		 *
		 * @since 1.0.0
		 *
		 * @return object Internal Core query object.
		 */
		protected function instantiate_query_object() {
			return new WP_Query();
		}

		/**
		 * Parses the results of the internal Core query into a collection.
		 *
		 * @since 1.0.0
		 *
		 * @return Post_Collection Results as a collection.
		 */
		protected function parse_results_collection() {
			$ids    = $this->original->posts;
			$fields = $this->original->query_vars['fields'];

			if ( 'ids' !== $fields ) {
				$ids    = wp_list_pluck( $ids, 'ID' );
				$fields = 'objects';
			}

			return $this->create_collection( $ids, $this->original->found_posts, $fields );
		}
	}

endif;
