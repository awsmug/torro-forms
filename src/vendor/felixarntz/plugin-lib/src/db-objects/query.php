<?php
/**
 * Query class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

use WP_Meta_Query;
use WP_Date_Query;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Query' ) ) :

	/**
	 * Base class for a query
	 *
	 * This class represents a general query.
	 *
	 * @since 1.0.0
	 *
	 * @property-read string     $request
	 * @property-read array      $request_args
	 * @property-read array      $sql_clauses
	 * @property-read array      $query_vars
	 * @property-read array      $query_var_defaults
	 * @property-read Collection $results
	 */
	abstract class Query {
		/**
		 * The manager instance for the query.
		 *
		 * @since 1.0.0
		 * @var Manager
		 */
		protected $manager;

		/**
		 * SQL for database query.
		 *
		 * Contains placeholders that need to be filled with `$request_args`.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $request = '';

		/**
		 * Arguments that need to be escaped in the database query.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $request_args = array();

		/**
		 * SQL query clauses.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $sql_clauses = array(
			'select'  => '',
			'from'    => '',
			'where'   => array(),
			'groupby' => '',
			'orderby' => '',
			'limits'  => '',
		);

		/**
		 * Query vars set by the user.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $query_vars = array();

		/**
		 * Default values for query vars.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $query_var_defaults = array();

		/**
		 * The results for the query.
		 *
		 * @since 1.0.0
		 * @var Collection
		 */
		protected $results;

		/**
		 * Metadata query container.
		 *
		 * @since 1.0.0
		 * @var WP_Meta_Query
		 */
		private $meta_query;

		/**
		 * Date query container.
		 *
		 * @since 1.0.0
		 * @var WP_Date_Query
		 */
		private $date_query;

		/**
		 * Metadata query clauses.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		private $meta_query_clauses = array();

		/**
		 * Constructor.
		 *
		 * Sets the manager instance and assigns the defaults.
		 *
		 * @since 1.0.0
		 *
		 * @param Manager $manager The manager instance for the model query.
		 */
		public function __construct( $manager ) {
			$this->manager = $manager;

			$primary_property = $this->manager->get_primary_property();

			$this->query_var_defaults = array(
				'fields'        => 'objects',
				'number'        => 10,
				'offset'        => 0,
				'no_found_rows' => null,
				'orderby'       => array( $primary_property => 'ASC' ),
				'include'       => '',
				'exclude'       => '',
				'update_cache'  => true,
			);

			if ( method_exists( $this->manager, 'get_slug_property' ) ) {
				$this->query_var_defaults[ $this->manager->get_slug_property() ] = '';
			}

			if ( method_exists( $this->manager, 'get_title_property' ) ) {
				$this->query_var_defaults[ $this->manager->get_title_property() ] = '';
			}

			if ( method_exists( $this->manager, 'get_type_property' ) ) {
				$this->query_var_defaults[ $this->manager->get_type_property() ] = '';
			}

			if ( method_exists( $this->manager, 'get_status_property' ) ) {
				$this->query_var_defaults[ $this->manager->get_status_property() ] = '';
			}

			if ( method_exists( $this->manager, 'get_author_property' ) ) {
				$this->query_var_defaults[ $this->manager->get_author_property() ] = '';
			}

			$search_fields = $this->get_search_fields();
			if ( ! empty( $search_fields ) ) {
				$this->query_var_defaults['search'] = '';
			}

			if ( method_exists( $this->manager, 'get_date_property' ) ) {
				$this->query_var_defaults['date_query'] = null;
			}

			if ( method_exists( $this->manager, 'get_meta_type' ) ) {
				$this->query_var_defaults['meta_key']   = ''; // phpcs:ignore WordPress.DB.SlowDBQuery
				$this->query_var_defaults['meta_value'] = ''; // phpcs:ignore WordPress.DB.SlowDBQuery
				$this->query_var_defaults['meta_query'] = ''; // phpcs:ignore WordPress.DB.SlowDBQuery

				if ( method_exists( $this->manager, 'update_meta_cache' ) ) {
					$this->query_var_defaults['update_meta_cache'] = true;
				}
			}
		}

		/**
		 * Magic isset-er.
		 *
		 * @since 1.0.0
		 *
		 * @param string $property Property to check for.
		 * @return bool True if property is set, false otherwise.
		 */
		public function __isset( $property ) {
			switch ( $property ) {
				case 'request':
				case 'request_args':
				case 'query_vars':
				case 'query_var_defaults':
				case 'results':
					return true;
				case 'date_query':
					if ( method_exists( $this->manager, 'get_date_property' ) ) {
						return true;
					}
					return false;
				case 'meta_query':
				case 'meta_query_clauses':
					if ( method_exists( $this->manager, 'get_meta_type' ) ) {
						return true;
					}
					return false;
			}

			return false;
		}

		/**
		 * Magic getter.
		 *
		 * @since 1.0.0
		 *
		 * @param string $property Property to get.
		 * @return mixed Property value.
		 */
		public function __get( $property ) {
			switch ( $property ) {
				case 'request':
				case 'request_args':
				case 'query_vars':
				case 'query_var_defaults':
				case 'results':
					return $this->$property;
				case 'date_query':
					if ( method_exists( $this->manager, 'get_date_property' ) ) {
						return $this->$property;
					}
					return null;
				case 'meta_query':
				case 'meta_query_clauses':
					if ( method_exists( $this->manager, 'get_meta_type' ) ) {
						return $this->$property;
					}
					return null;
			}

			return null;
		}

		/**
		 * Sets up the query for retrieving models.
		 *
		 * @since 1.0.0
		 *
		 * @param string|array $query {
		 *      Array or query string of model query arguments.
		 *
		 *      @type string       $fields        Fields to return. Accepts 'ids' (returns a collection of model
		 *                                        IDs) or 'objects' (returns a collection of full model objects).
		 *                                        Default 'objects'.
		 *      @type int          $number        Maximum number of models to retrieve. Default -1 (no limit).
		 *      @type int          $offset        Number of models to offset the query. Used to build the LIMIT clause.
		 *                                        Default 0.
		 *      @type bool         $no_found_rows Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default depends
		 *                                        on the $number parameter: If unlimited, default is true, otherwise
		 *                                        default is false.
		 *      @type array        $orderby       Array of orderby => order pairs. Accepted orderby key is 'id'.
		 *                                        The orderby values must be either 'ASC' or 'DESC'. Default
		 *                                        array( 'id' => 'ASC' ).
		 * }
		 * @return Leaves_And_Love\Plugin_Lib\DB_Objects\Collection Collection of models.
		 */
		public function query( $query ) {
			$this->parse_query( $query );

			return $this->get_results();
		}

		/**
		 * Parses arguments passed to the model query with default query arguments.
		 *
		 * @since 1.0.0
		 *
		 * @see Query::query()
		 *
		 * @param string|array $query Array or query string of model query arguments. See
		 *                            {@see Query::query()}.
		 */
		protected function parse_query( $query ) {
			$this->query_vars = wp_parse_args( $query, $this->query_var_defaults );

			$this->query_vars['number'] = intval( $this->query_vars['number'] );
			if ( $this->query_vars['number'] < 0 ) {
				$this->query_vars['number'] = 0;
			}

			$this->query_vars['offset'] = absint( $this->query_vars['offset'] );

			if ( null === $this->query_vars['no_found_rows'] ) {
				if ( 0 === $this->query_vars['number'] ) {
					$this->query_vars['no_found_rows'] = true;
				} else {
					$this->query_vars['no_found_rows'] = false;
				}
			}

			if ( method_exists( $this->manager, 'get_date_property' ) && ! empty( $this->query_vars['date_query'] ) && is_array( $this->query_vars['date_query'] ) ) {
				$full_table_name = $this->manager->db()->get_table( $this->manager->get_table_name(), 'full' );

				$this->date_query         = new WP_Date_Query( $this->query_vars['date_query'], $full_table_name . '.' . $this->manager->get_date_property() );
				$this->date_query->column = $full_table_name . '.' . $this->manager->get_date_property();
			}

			if ( method_exists( $this->manager, 'get_meta_type' ) ) {
				$this->meta_query = new WP_Meta_Query();
				$this->meta_query->parse_query_vars( $this->query_vars );

				if ( ! empty( $this->meta_query->queries ) ) {
					$prefix = $this->manager->db()->get_prefix();
					$name   = $this->manager->get_meta_type();

					$table_name = $this->manager->get_table_name();

					$this->meta_query_clauses = $this->meta_query->get_sql( $prefix . $name, "%{$table_name}%", 'id', $this );
				}
			}
		}

		/**
		 * Retrieves a list of models matching the query vars.
		 *
		 * @since 1.0.0
		 *
		 * @return Collection Collection of models.
		 */
		protected function get_results() {
			$key          = $this->get_cache_key( $this->query_vars );
			$last_changed = $this->manager->get_from_cache( 'last_changed' );
			if ( ! $last_changed ) {
				$last_changed = microtime();
				$this->manager->set_in_cache( 'last_changed', $last_changed );
			}

			$cache_key   = "get_results:$key:$last_changed";
			$cache_value = $this->manager->get_from_cache( $cache_key );

			if ( false === $cache_value ) {
				$total     = 0;
				$model_ids = $this->query_results();
				if ( $model_ids && ! $this->query_vars['no_found_rows'] ) {
					$total_models_query = 'SELECT FOUND_ROWS()';
					$total              = (int) $this->manager->db()->get_var( $total_models_query );
				}

				$cache_value = array(
					'model_ids' => $model_ids,
					'total'     => $total,
				);

				$this->manager->add_to_cache( $cache_key, $cache_value );
			} else {
				$model_ids = $cache_value['model_ids'];
				$total     = $cache_value['total'];
			}

			if ( $this->query_vars['update_cache'] ) {
				$non_cached_ids = $this->get_non_cached_ids( $model_ids );
				if ( ! empty( $non_cached_ids ) ) {
					$this->update_cache( $non_cached_ids );

					if ( isset( $this->query_vars['update_meta_cache'] ) && $this->query_vars['update_meta_cache'] ) {
						$this->manager->update_meta_cache( $non_cached_ids );
					}
				}
			}

			return $this->create_collection( $model_ids, $total, $this->query_vars['fields'] );
		}

		/**
		 * Gets the query cache key for a given set of query vars.
		 *
		 * @since 1.0.0
		 *
		 * @param array $query_vars Array of model query arguments.
		 * @return string Cache key to use for the query.
		 */
		protected function get_cache_key( $query_vars ) {
			$args = wp_array_slice_assoc( $query_vars, array_keys( $this->query_var_defaults ) );

			// The following arguments do not affect the actual query.
			$non_key_args = array( 'fields', 'update_cache', 'update_meta_cache' );
			foreach ( $non_key_args as $arg ) {
				if ( isset( $args[ $arg ] ) ) {
					unset( $args[ $arg ] );
				}
			}

			return md5( serialize( $args ) ); // phpcs:ignore
		}

		/**
		 * Retrieves IDs that are not already present in the cache.
		 *
		 * @since 1.0.0
		 *
		 * @param array $model_ids The model IDs to check for.
		 * @return array List of IDs not present in the cache.
		 */
		protected function get_non_cached_ids( $model_ids ) {
			$clean = array();

			foreach ( $model_ids as $id ) {
				$id = (int) $id;
				if ( false === $this->manager->get_from_cache( $id ) ) {
					$clean[] = $id;
				}
			}

			return $clean;
		}

		/**
		 * Updates the cache for given models.
		 *
		 * @since 1.0.0
		 *
		 * @param array $non_cached_ids Array of model IDs.
		 */
		protected function update_cache( $non_cached_ids ) {
			$table_name       = $this->manager->get_table_name();
			$primary_property = $this->manager->get_primary_property();

			$fresh_models = $this->manager->db()->get_results( sprintf( "SELECT %%{$table_name}%%.* FROM %%{$table_name}%% WHERE {$primary_property} IN (%s)", join( ',', array_map( 'intval', $non_cached_ids ) ) ) );
			foreach ( (array) $fresh_models as $model ) {
				$this->manager->add_to_cache( $model->$primary_property, $model );
			}
		}

		/**
		 * Creates a collection object from the query results.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $model_ids The model IDs, or objects for this collection.
		 * @param int    $total     Optional. The total amount of models in the collection. Default is the
		 *                          number of models.
		 * @param string $fields    Optional. Mode of the models passed. Default 'ids'.
		 * @return Collection Collection of models.
		 */
		protected function create_collection( $model_ids, $total, $fields ) {
			$model_ids = array_map( 'intval', $model_ids );

			$this->results = $this->manager->get_collection( $model_ids, $total, 'ids' );

			if ( 'objects' === $fields ) {
				$this->results->transform_into_objects();
			}

			return $this->results;
		}

		/**
		 * Used internally to get a list of model IDs matching the query vars.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of model IDs.
		 */
		protected function query_results() {
			$table_name = $this->manager->get_table_name();

			list( $fields, $distinct ) = $this->parse_fields();
			if ( is_bool( $distinct ) ) {
				$distinct = $distinct ? 'DISTINCT' : '';
			}

			$number = $this->query_vars['number'];
			$offset = $this->query_vars['offset'];

			$limits = null;
			if ( $number ) {
				if ( $offset ) {
					$limits = "LIMIT $offset,$number";
				} else {
					$limits = "LIMIT $number";
				}
			}

			$found_rows = '';
			if ( ! $this->query_vars['no_found_rows'] ) {
				$found_rows = 'SQL_CALC_FOUND_ROWS';
			}

			$orderby = $this->parse_orderby( $this->query_vars['orderby'] );

			list( $this->sql_clauses['where'], $this->request_args ) = $this->parse_where();

			$join = $this->parse_join();

			$groupby = $this->parse_groupby();

			$where = implode( ' AND ', $this->sql_clauses['where'] );

			$clauses = array(
				'fields'  => $fields,
				'join'    => $join,
				'where'   => $where,
				'orderby' => $orderby,
				'limits'  => $limits,
				'groupby' => $groupby,
			);

			$fields  = isset( $clauses['fields'] ) ? $clauses['fields'] : '';
			$join    = isset( $clauses['join'] ) ? $clauses['join'] : '';
			$where   = isset( $clauses['where'] ) ? $clauses['where'] : '';
			$orderby = isset( $clauses['orderby'] ) ? $clauses['orderby'] : '';
			$limits  = isset( $clauses['limits'] ) ? $clauses['limits'] : '';
			$groupby = isset( $clauses['groupby'] ) ? $clauses['groupby'] : '';

			if ( $where ) {
				$where = "WHERE $where";
			}

			if ( $orderby ) {
				$orderby = "ORDER BY $orderby";
			}

			if ( $groupby ) {
				$groupby = "GROUP BY $groupby";
			}

			$this->sql_clauses['select']  = "SELECT $distinct $found_rows $fields";
			$this->sql_clauses['from']    = "FROM %{$table_name}% $join";
			$this->sql_clauses['groupby'] = $groupby;
			$this->sql_clauses['orderby'] = $orderby;
			$this->sql_clauses['limits']  = $limits;

			$this->request = "{$this->sql_clauses['select']} {$this->sql_clauses['from']} {$where} {$this->sql_clauses['groupby']} {$this->sql_clauses['orderby']} {$this->sql_clauses['limits']}";

			return $this->manager->db()->get_col( $this->request, $this->request_args );
		}

		/**
		 * Parses the SQL fields value.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array with the first element being the fields part of the SQL query and the second
		 *               being a boolean specifying whether to use the DISTINCT keyword.
		 */
		protected function parse_fields() {
			$table_name       = $this->manager->get_table_name();
			$primary_property = $this->manager->get_primary_property();

			return array( '%' . $table_name . '%.' . $primary_property, false );
		}

		/**
		 * Parses the SQL join value.
		 *
		 * @since 1.0.0
		 *
		 * @return string Join value for the SQL query.
		 */
		protected function parse_join() {
			if ( ! empty( $this->meta_query_clauses ) ) {
				return $this->meta_query_clauses['join'];
			}

			return '';
		}

		/**
		 * Parses the SQL where clause.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array with the first element being the array of SQL where clauses and the second
		 *               being the array of arguments for those where clauses.
		 */
		protected function parse_where() {
			$where = array();
			$args  = array();

			list( $where, $args ) = $this->parse_list_where_field( $where, $args, $this->manager->get_primary_property(), 'include', 'exclude', '%d', 'absint' );

			if ( method_exists( $this->manager, 'get_slug_property' ) ) {
				$slug_property = $this->manager->get_slug_property();

				list( $where, $args ) = $this->parse_default_where_field( $where, $args, $slug_property, $slug_property, '%s', 'sanitize_title', true );
			}

			if ( method_exists( $this->manager, 'get_title_property' ) ) {
				$title_property = $this->manager->get_title_property();

				list( $where, $args ) = $this->parse_default_where_field( $where, $args, $title_property, $title_property, '%s', null, false );
			}

			if ( method_exists( $this->manager, 'get_type_property' ) ) {
				$type_property = $this->manager->get_type_property();

				list( $where, $args ) = $this->parse_default_where_field( $where, $args, $type_property, $type_property, '%s', 'sanitize_key', true );
			}

			if ( method_exists( $this->manager, 'get_status_property' ) ) {
				$status_property = $this->manager->get_status_property();

				list( $where, $args ) = $this->parse_default_where_field( $where, $args, $status_property, $status_property, '%s', 'sanitize_key', true );
			}

			if ( method_exists( $this->manager, 'get_author_property' ) ) {
				$author_property = $this->manager->get_author_property();

				list( $where, $args ) = $this->parse_default_where_field( $where, $args, $author_property, $author_property, '%d', 'absint', false );
			}

			$search_fields = $this->get_search_fields();
			if ( ! empty( $search_fields ) && ! empty( $this->query_vars['search'] ) ) {
				$where['search'] = $this->get_search_sql( $this->query_vars['search'], $search_fields );
			}

			if ( $this->date_query ) {
				$where['date_query'] = preg_replace( '/^\s*AND\s*/', '', $this->date_query->get_sql() );
			}

			if ( ! empty( $this->meta_query_clauses ) ) {
				$where['meta_query'] = preg_replace( '/^\s*AND\s*/', '', $this->meta_query_clauses['where'] ); // phpcs:ignore WordPress.DB.SlowDBQuery
			}

			return array( $where, $args );
		}

		/**
		 * Parses the SQL groupby clause.
		 *
		 * @since 1.0.0
		 *
		 * @return string Groupby value for the SQL query.
		 */
		protected function parse_groupby() {
			if ( ! empty( $this->meta_query_clauses ) ) {
				$table_name = $this->manager->get_table_name();

				return '%' . $table_name . '%.id';
			}

			return '';
		}

		/**
		 * Parses the SQL orderby clause.
		 *
		 * @since 1.0.0
		 *
		 * @param string|array $orderby The $orderby query var.
		 * @return string The orderby clause.
		 */
		protected function parse_orderby( $orderby ) {
			if ( in_array( $orderby, array( 'none', array(), false ), true ) ) {
				return '';
			}

			if ( empty( $orderby ) ) {
				$table_name = $this->manager->get_table_name();

				return '%' . $table_name . '%.id ASC';
			}

			$orderby_array = array();
			foreach ( $orderby as $_orderby => $_order ) {
				if ( ! in_array( $_orderby, $this->get_valid_orderby_fields(), true ) ) {
					continue;
				}

				$parsed_orderby = $this->parse_single_orderby( $_orderby );
				$parsed_order   = $this->parse_single_order( $_order, $_orderby );
				if ( ! empty( $parsed_order ) ) {
					$parsed_orderby .= ' ' . $parsed_order;
				}

				$orderby_array[] = $parsed_orderby;
			}

			return implode( ', ', array_unique( $orderby_array ) );
		}

		/**
		 * Parses a single $orderby element.
		 *
		 * @since 1.0.0
		 *
		 * @param string $orderby The orderby field. Must be valid.
		 * @return string The parsed orderby SQL string.
		 */
		protected function parse_single_orderby( $orderby ) {
			$table_name = $this->manager->get_table_name();

			if ( 'include' === $orderby ) {
				$ids = implode( ',', array_map( 'absint', $this->query_vars['include'] ) );

				return "FIELD( %{$table_name}%.id, $ids )";
			}

			if ( method_exists( $this->manager, 'get_meta_type' ) && in_array( $orderby, $this->get_meta_orderby_fields(), true ) ) {
				$meta_table = _get_meta_table( $this->manager->db()->get_prefix() . $this->manager->get_meta_type() );

				if ( $this->query_vars['meta_key'] === $orderby || 'meta_value' === $orderby ) {
					return "$meta_table.meta_value";
				}

				if ( 'meta_value_num' === $orderby ) {
					return "$meta_table.meta_value+0";
				}

				$meta_query_clauses = $this->meta_query->get_clauses();

				return sprintf( 'CAST(%s.meta_value AS %s)', esc_sql( $meta_query_clauses[ $orderby ]['alias'] ), esc_sql( $meta_query_clauses[ $orderby ]['cast'] ) );
			}

			return '%' . $table_name . '%.' . $orderby;
		}

		/**
		 * Parses a single $order element.
		 *
		 * @since 1.0.0
		 *
		 * @param string $order   The order value. Either 'ASC' or 'DESC'.
		 * @param string $orderby The orderby field. Must be valid.
		 * @return string The parsed order SQL string, or empty if not necessary.
		 */
		protected function parse_single_order( $order, $orderby ) {
			if ( 'include' === $orderby ) {
				return '';
			}

			return 'DESC' === strtoupper( $order ) ? 'DESC' : 'ASC';
		}

		/**
		 * Parses a default field for an argument in the WHERE clause.
		 *
		 * This utility method can be used inside the `Leaves_And_Love\Plugin_Lib\DB_Objects\Query::parse_where()`
		 * method to created clauses for individual database properties.
		 *
		 * Clauses created by this method support an exact match search for a specific value, or a whitelist of
		 * values if $support_array is specified as true.
		 *
		 * @since 1.0.0
		 *
		 * @param array         $where             The input where array to modify.
		 * @param array         $args              The input arguments array to modify.
		 * @param string        $property          Name of the property in the database.
		 * @param string        $query_var         Name of the query variable to check for.
		 * @param string        $placeholder       Optional. Placeholder for the SQL statement. Either '%s',
		 *                                         '%d' or '%f'. Default '%s'.
		 * @param callable|null $sanitize_callback Optional. Callback to sanitize each value passed in the query.
		 *                                         Default null.
		 * @param bool          $support_array     Optional. Whether to support array values for an IN clause.
		 *                                         Default false.
		 * @return array Array with the first element being the array of SQL where clauses and the second
		 *               being the array of arguments for those where clauses.
		 */
		protected function parse_default_where_field( $where, $args, $property, $query_var, $placeholder = '%s', $sanitize_callback = null, $support_array = false ) {
			if ( $this->query_vars[ $query_var ] !== $this->query_var_defaults[ $query_var ] ) {
				$table_name = $this->manager->get_table_name();

				if ( $support_array && is_array( $this->query_vars[ $query_var ] ) ) {
					$values = $this->query_vars[ $query_var ];
					if ( $sanitize_callback ) {
						$values = array_map( $sanitize_callback, $values );
					}

					$where[ $query_var ] = "%{$table_name}%.{$property} IN ( " . implode( ',', array_fill( 0, count( $values ), $placeholder ) ) . ' )';
					$args                = array_merge( $args, $values );
				} else {
					$value = $this->query_vars[ $query_var ];
					if ( $sanitize_callback ) {
						$value = call_user_func( $sanitize_callback, $value );
					}

					$where[ $query_var ] = "%{$table_name}%.{$property} = {$placeholder}";
					$args[]              = $value;
				}
			}

			return array( $where, $args );
		}

		/**
		 * Parses a whitelist or blacklist field for an argument in the WHERE clause.
		 *
		 * This utility method can be used inside the `Leaves_And_Love\Plugin_Lib\DB_Objects\Query::parse_where()`
		 * method to created clauses for individual database properties.
		 *
		 * This method creates two clauses, one for the `$property . '__in'` and one for the `$property . '__not_in'`
		 * query variable. Both of these must contain an array with a whitelist or blacklist respectively.
		 *
		 * @since 1.0.0
		 *
		 * @param array         $where             The input where array to modify.
		 * @param array         $args              The input arguments array to modify.
		 * @param string        $property          Name of the property in the database.
		 * @param string        $include_query_var Name of the query variable to check for included values.
		 * @param string        $exclude_query_var Name of the query variable to check for excluded values.
		 * @param string        $placeholder       Optional. Placeholder for the SQL statement. Either '%s',
		 *                                         '%d' or '%f'. Default '%s'.
		 * @param callable|null $sanitize_callback Optional. Callback to sanitize each value passed in the query.
		 *                                         Default null.
		 * @return array Array with the first element being the array of SQL where clauses and the second
		 *               being the array of arguments for those where clauses.
		 */
		protected function parse_list_where_field( $where, $args, $property, $include_query_var, $exclude_query_var, $placeholder = '%s', $sanitize_callback = null ) {
			$table_name = $this->manager->get_table_name();

			if ( ! empty( $this->query_vars[ $include_query_var ] ) ) {
				$values = $this->query_vars[ $include_query_var ];
				if ( $sanitize_callback ) {
					$values = array_map( $sanitize_callback, $values );
				}

				$where[ $include_query_var ] = "%{$table_name}%.{$property} IN ( " . implode( ',', array_fill( 0, count( $values ), $placeholder ) ) . ' )';
				$args                        = array_merge( $args, $values );
			}

			if ( ! empty( $this->query_vars[ $exclude_query_var ] ) ) {
				$values = $this->query_vars[ $exclude_query_var ];
				if ( $sanitize_callback ) {
					$values = array_map( $sanitize_callback, $values );
				}

				$where[ $exclude_query_var ] = "%{$table_name}%.{$property} NOT IN ( " . implode( ',', array_fill( 0, count( $values ), $placeholder ) ) . ' )';
				$args                        = array_merge( $args, $values );
			}

			return array( $where, $args );
		}

		/**
		 * Returns the fields that are valid to be used in orderby clauses.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of valid orderby fields.
		 */
		public function get_valid_orderby_fields() {
			$orderby_fields = array( 'id', 'include' );

			if ( method_exists( $this->manager, 'get_slug_property' ) ) {
				$orderby_fields[] = $this->manager->get_slug_property();
			}

			if ( method_exists( $this->manager, 'get_title_property' ) ) {
				$orderby_fields[] = $this->manager->get_title_property();
			}

			if ( method_exists( $this->manager, 'get_type_property' ) ) {
				$orderby_fields[] = $this->manager->get_type_property();
			}

			if ( method_exists( $this->manager, 'get_status_property' ) ) {
				$orderby_fields[] = $this->manager->get_status_property();
			}

			if ( method_exists( $this->manager, 'get_author_property' ) ) {
				$orderby_fields[] = $this->manager->get_author_property();
			}

			if ( method_exists( $this->manager, 'get_date_property' ) ) {
				$orderby_fields[] = $this->manager->get_date_property();
				$orderby_fields   = array_merge( $orderby_fields, $this->manager->get_secondary_date_properties() );
			}

			if ( method_exists( $this->manager, 'get_meta_type' ) ) {
				$orderby_fields = array_merge( $orderby_fields, $this->get_meta_orderby_fields() );
			}

			return $orderby_fields;
		}

		/**
		 * Returns the fields that are searchable.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of database column names.
		 */
		public function get_search_fields() {
			$search_fields = array();

			if ( method_exists( $this->manager, 'get_title_property' ) ) {
				$search_fields[] = $this->manager->get_title_property();
			}

			if ( method_exists( $this->manager, 'get_content_property' ) ) {
				$search_fields[] = $this->manager->get_content_property();
			}

			return $search_fields;
		}

		/**
		 * Used internally to generate an SQL string for searching across multiple columns.
		 *
		 * @since 1.0.0
		 *
		 * @global \wpdb $wpdb WordPress database abstraction object.
		 *
		 * @param string $string Search string.
		 * @param array  $fields Database columns to search.
		 * @return string Search SQL.
		 */
		protected function get_search_sql( $string, $fields ) {
			global $wpdb;

			$table_name = $this->manager->get_table_name();

			if ( false !== strpos( $string, '*' ) ) {
				$like = '%' . implode( '%', array_map( array( $wpdb, 'esc_like' ), explode( '*', $string ) ) ) . '%';
			} else {
				$like = '%' . $wpdb->esc_like( $string ) . '%';
			}

			$searches = array();
			foreach ( $fields as $field ) {
				$searches[] = "%{$table_name}%." . $wpdb->prepare( "{$field} LIKE %s", $like ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}

			return '(' . implode( ' OR ', $searches ) . ')';
		}

		/**
		 * Returns the meta orderby fields to use in orderby clauses.
		 *
		 * These depend on the current meta query.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of meta orderby fields.
		 */
		private function get_meta_orderby_fields() {
			if ( empty( $this->meta_query->queries ) ) {
				return array();
			}

			$meta_orderby_fields = array();

			if ( ! empty( $this->query_vars['meta_key'] ) ) {
				$meta_orderby_fields[] = $this->query_vars['meta_key'];
				$meta_orderby_fields[] = 'meta_value';
				$meta_orderby_fields[] = 'meta_value_num';
			}

			$meta_query_clauses = $this->meta_query->get_clauses();
			if ( $meta_query_clauses ) {
				$meta_orderby_fields = array_merge( $meta_orderby_fields, array_keys( $meta_query_clauses ) );
			}

			return $meta_orderby_fields;
		}
	}

endif;
