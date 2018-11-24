<?php
/**
 * Query class for comments
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Queries;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Comment_Collection;
use WP_Comment_Query;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Queries\Comment_Query' ) ) :

	/**
	 * Class for a comments query
	 *
	 * This class represents a comments query.
	 *
	 * @since 1.0.0
	 */
	class Comment_Query extends Core_Query {
		/**
		 * Sets up the query for retrieving comments.
		 *
		 * @since 1.0.0
		 *
		 * @param string|array $query Array or query string of comment query arguments. See `WP_Comment_Query::__construct()`
		 *                            for a list of supported arguments.
		 * @return Comment_Collection Collection of comments.
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
			return new WP_Comment_Query();
		}

		/**
		 * Parses the results of the internal Core query into a collection.
		 *
		 * @since 1.0.0
		 *
		 * @return Comment_Collection Results as a collection.
		 */
		protected function parse_results_collection() {
			$ids    = $this->original->comments;
			$fields = $this->original->query_vars['fields'];

			if ( 'ids' !== $fields ) {
				$ids    = wp_list_pluck( $ids, 'comment_ID' );
				$fields = 'objects';
			}

			return $this->create_collection( $ids, $this->original->found_comments, $fields );
		}
	}

endif;
