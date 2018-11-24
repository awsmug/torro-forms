<?php
/**
 * Query class for networks
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Queries;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Network_Collection;
use WP_Network_Query;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Queries\Network_Query' ) ) :

	/**
	 * Class for a networks query
	 *
	 * This class represents a networks query. Must only be used in a multisite setup.
	 *
	 * @since 1.0.0
	 */
	class Network_Query extends Core_Query {
		/**
		 * Sets up the query for retrieving networks.
		 *
		 * @since 1.0.0
		 *
		 * @param string|array $query Array or query string of network query arguments. See `WP_Network_Query::__construct()`
		 *                            for a list of supported arguments.
		 * @return Network_Collection Collection of networks.
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
			return new WP_Network_Query();
		}

		/**
		 * Parses the results of the internal Core query into a collection.
		 *
		 * @since 1.0.0
		 *
		 * @return Network_Collection Results as a collection.
		 */
		protected function parse_results_collection() {
			$ids    = $this->original->networks;
			$fields = $this->original->query_vars['fields'];

			if ( 'ids' !== $fields ) {
				$ids    = wp_list_pluck( $ids, 'id' );
				$fields = 'objects';
			}

			return $this->create_collection( $ids, $this->original->found_networks, $fields );
		}
	}

endif;
