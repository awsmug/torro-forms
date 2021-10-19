<?php
/**
 * Query class for terms
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Queries;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Term_Collection;
use WP_Term_Query;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Queries\Term_Query' ) ) :

	/**
	 * Class for a terms query
	 *
	 * This class represents a terms query.
	 *
	 * @since 1.0.0
	 */
	class Term_Query extends Core_Query {
		/**
		 * Sets up the query for retrieving terms.
		 *
		 * @since 1.0.0
		 *
		 * @param string|array $query Array or query string of term query arguments. See `WP_Term_Query::__construct()`
		 *                            for a list of supported arguments.
		 * @return Term_Collection Collection of terms.
		 */
		public function query( $query ) {
			if ( isset( $query['fields'] ) && 'ids' !== $query['fields'] ) {
				$query['fields'] = 'all';
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
			return new WP_Term_Query();
		}

		/**
		 * Parses the results of the internal Core query into a collection.
		 *
		 * @since 1.0.0
		 *
		 * @return Term_Collection Results as a collection.
		 */
		protected function parse_results_collection() {
			$ids    = null !== $this->original->terms ? $this->original->terms : array();
			$fields = $this->original->query_vars['fields'];

			if ( 'ids' !== $fields ) {
				$ids    = wp_list_pluck( $ids, 'term_id' );
				$fields = 'objects';
			}

			return $this->create_collection( $ids, count( $ids ), $fields );
		}
	}

endif;
