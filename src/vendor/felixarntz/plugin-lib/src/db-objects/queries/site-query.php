<?php
/**
 * Query class for sites
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Queries;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Site_Collection;
use WP_Site_Query;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Queries\Site_Query' ) ) :

	/**
	 * Class for a sites query
	 *
	 * This class represents a sites query. Must only be used in a multisite setup.
	 *
	 * @since 1.0.0
	 */
	class Site_Query extends Core_Query {
		/**
		 * Sets up the query for retrieving sites.
		 *
		 * @since 1.0.0
		 *
		 * @param string|array $query Array or query string of site query arguments. See `WP_Site_Query::__construct()`
		 *                            for a list of supported arguments.
		 * @return Site_Collection Collection of sites.
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
			return new WP_Site_Query();
		}

		/**
		 * Parses the results of the internal Core query into a collection.
		 *
		 * @since 1.0.0
		 *
		 * @return Site_Collection Results as a collection.
		 */
		protected function parse_results_collection() {
			$ids    = $this->original->sites;
			$fields = $this->original->query_vars['fields'];

			if ( 'ids' !== $fields ) {
				$ids    = wp_list_pluck( $ids, 'blog_id' );
				$fields = 'objects';
			}

			return $this->create_collection( $ids, $this->original->found_sites, $fields );
		}
	}

endif;
