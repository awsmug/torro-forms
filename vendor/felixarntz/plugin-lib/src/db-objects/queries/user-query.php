<?php
/**
 * Query class for users
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Queries;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\User_Collection;
use WP_User_Query;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Queries\User_Query' ) ) :

	/**
	 * Class for a users query
	 *
	 * This class represents a users query.
	 *
	 * @since 1.0.0
	 */
	class User_Query extends Core_Query {
		/**
		 * Sets up the query for retrieving users.
		 *
		 * @since 1.0.0
		 *
		 * @param string|array $query Array or query string of user query arguments. See `WP_User_Query::prepare_query()`
		 *                            for a list of supported arguments.
		 * @return User_Collection Collection of users.
		 */
		public function query( $query ) {
			if ( isset( $query['fields'] ) ) {
				if ( 'ids' === $query['fields'] ) {
					$query['fields'] = 'ID';
				} else {
					$query['fields'] = 'all';
				}
			}

			$this->original->prepare_query( $query );
			$this->original->query();

			$this->results = $this->parse_results_collection();

			return $this->results;
		}

		/**
		 * Instantiates the internal Core query object.
		 *
		 * @since 1.0.0
		 *
		 * @return object Internal Core query object.
		 */
		protected function instantiate_query_object() {
			return new WP_User_Query();
		}

		/**
		 * Parses the results of the internal Core query into a collection.
		 *
		 * @since 1.0.0
		 *
		 * @return User_Collection Results as a collection.
		 */
		protected function parse_results_collection() {
			$ids    = $this->original->get_results();
			$fields = $this->original->query_vars['fields'];

			if ( 'ID' !== $fields ) {
				$ids    = wp_list_pluck( $ids, 'ID' );
				$fields = 'objects';
			} else {
				$fields = 'ids';
			}

			return $this->create_collection( $ids, $this->original->get_total(), $fields );
		}
	}

endif;
