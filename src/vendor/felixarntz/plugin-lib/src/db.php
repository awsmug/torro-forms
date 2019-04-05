<?php
/**
 * Database abstraction class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib;

use Leaves_And_Love\Plugin_Lib\Traits\Hook_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Traits\Container_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Traits\Translations_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Translations\Translations_DB;
use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB' ) ) :

	/**
	 * Class for database handling.
	 *
	 * @since 1.0.0
	 *
	 * @method Options options()
	 */
	class DB extends Service {
		use Hook_Service_Trait, Container_Service_Trait, Translations_Service_Trait;

		/**
		 * WordPress database abstraction object.
		 *
		 * @since 1.0.0
		 * @var \wpdb
		 */
		protected $wpdb;

		/**
		 * Table names with their prefixes.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $tables = array();

		/**
		 * The database schema SQL queries.
		 *
		 * The queries contain placeholders for the table names in the form of `%table_name%`.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $schema = '';

		/**
		 * Version of the database schema.
		 *
		 * When changing the schema, this number should be increased as well.
		 *
		 * @since 1.0.0
		 * @var int
		 */
		protected $version = 0;

		/**
		 * The Option API service definition.
		 *
		 * @since 1.0.0
		 * @static
		 * @var string
		 */
		protected static $service_options = Options::class;

		/**
		 * Constructor.
		 *
		 * This sets the table prefix and adds the tables.
		 *
		 * @since 1.0.0
		 *
		 * @global \wpdb $wpdb WordPress database abstraction object.
		 *
		 * @param string          $prefix       The prefix for all database tables.
		 * @param array           $services {
		 *     Array of service instances.
		 *
		 *     @type Options       $options       The Option API class instance.
		 *     @type Error_Handler $error_handler The error handler instance.
		 * }
		 * @param Translations_DB $translations Translations instance.
		 */
		public function __construct( $prefix, $services, $translations ) {
			global $wpdb;

			$this->set_prefix( $prefix );
			$this->set_services( $services );
			$this->set_translations( $translations );

			$this->wpdb = $wpdb;

			$this->options()->store_in_network( 'db_version' );

			$this->setup_hooks();
		}

		/**
		 * Performs any SQL query.
		 *
		 * @since 1.0.0
		 *
		 * @param string      $query    SQL query. Should use table placeholder instead of fully qualified table names.
		 * @param array|mixed $args     Optional. The array of variables to substitute into the query's placeholders
		 *                              if being called like {@link https://secure.php.net/vsprintf vsprintf()}, or the
		 *                              first variable to substitute into the query's placeholders if being called like
		 *                              {@link https://secure.php.net/sprintf sprintf()}.
		 * @param mixed       $args,... Optional. Further variables to substitute into the query's placeholders if being
		 *                              called like {@link https://secure.php.net/sprintf sprintf()}.
		 * @return int|bool Number of rows affected/selected or false on error.
		 */
		public function query( $query, $args = array() ) {
			$_args = func_get_args();

			return $this->generic_query_helper( 'query', $query, $this->process_query_placeholder_args( $_args ) );
		}

		/**
		 * Retrieves one variable from the database.
		 *
		 * @since 1.0.0
		 *
		 * @param string      $query    SQL query. Should use table placeholder instead of fully qualified table names.
		 * @param array|mixed $args     Optional. The array of variables to substitute into the query's placeholders
		 *                              if being called like {@link https://secure.php.net/vsprintf vsprintf()}, or the
		 *                              first variable to substitute into the query's placeholders if being called like
		 *                              {@link https://secure.php.net/sprintf sprintf()}.
		 * @param mixed       $args,... Optional. Further variables to substitute into the query's placeholders if being
		 *                              called like {@link https://secure.php.net/sprintf sprintf()}.
		 * @return string|null Database query result or null on failure.
		 */
		public function get_var( $query, $args = array() ) {
			$_args = func_get_args();

			return $this->generic_query_helper( 'get_var', $query, $this->process_query_placeholder_args( $_args ) );
		}

		/**
		 * Retrieves one row from the database.
		 *
		 * @since 1.0.0
		 *
		 * @param string      $query    SQL query. Should use table placeholder instead of fully qualified table names.
		 * @param array|mixed $args     Optional. The array of variables to substitute into the query's placeholders
		 *                              if being called like {@link https://secure.php.net/vsprintf vsprintf()}, or the
		 *                              first variable to substitute into the query's placeholders if being called like
		 *                              {@link https://secure.php.net/sprintf sprintf()}.
		 * @param mixed       $args,... Optional. Further variables to substitute into the query's placeholders if being
		 *                              called like {@link https://secure.php.net/sprintf sprintf()}.
		 * @return object|null Database query result or null on failure.
		 */
		public function get_row( $query, $args = array() ) {
			$_args = func_get_args();

			return $this->generic_query_helper( 'get_row', $query, $this->process_query_placeholder_args( $_args ) );
		}

		/**
		 * Retrieves one column from the database.
		 *
		 * @since 1.0.0
		 *
		 * @param string      $query    SQL query. Should use table placeholder instead of fully qualified table names.
		 * @param array|mixed $args     Optional. The array of variables to substitute into the query's placeholders
		 *                              if being called like {@link https://secure.php.net/vsprintf vsprintf()}, or the
		 *                              first variable to substitute into the query's placeholders if being called like
		 *                              {@link https://secure.php.net/sprintf sprintf()}.
		 * @param mixed       $args,... Optional. Further variables to substitute into the query's placeholders if being
		 *                              called like {@link https://secure.php.net/sprintf sprintf()}.
		 * @return array Database query result. Array indexed from 0 by SQL result row number.
		 */
		public function get_col( $query, $args = array() ) {
			$_args = func_get_args();

			return $this->generic_query_helper( 'get_col', $query, $this->process_query_placeholder_args( $_args ) );
		}

		/**
		 * Retrieves an entire SQL result set from the database.
		 *
		 * @since 1.0.0
		 *
		 * @param string      $query    SQL query. Should use table placeholder instead of fully qualified table names.
		 * @param array|mixed $args     Optional. The array of variables to substitute into the query's placeholders
		 *                              if being called like {@link https://secure.php.net/vsprintf vsprintf()}, or the
		 *                              first variable to substitute into the query's placeholders if being called like
		 *                              {@link https://secure.php.net/sprintf sprintf()}.
		 * @param mixed       $args,... Optional. Further variables to substitute into the query's placeholders if being
		 *                              called like {@link https://secure.php.net/sprintf sprintf()}.
		 * @return array|null Database query result. An array of row objects, or null on failure.
		 */
		public function get_results( $query, $args = array() ) {
			$_args = func_get_args();

			return $this->generic_query_helper( 'get_results', $query, $this->process_query_placeholder_args( $_args ) );
		}

		/**
		 * Inserts a row into a database table.
		 *
		 * @since 1.0.0
		 *
		 * @param string $table Name of the table. Will be replaced by the fully qualified database table name.
		 * @param array  $data  Data to insert (in column => value pairs).
		 *                      Both $data columns and $data values should be "raw" (neither should be SQL escaped).
		 *                      Sending a null value will cause the column to be set to NULL.
		 * @return int|bool The number of rows inserted, or false on error.
		 */
		public function insert( $table, $data ) {
			return $this->insert_replace_delete_helper( 'insert', $table, $data );
		}

		/**
		 * Replaces a row into a database table.
		 *
		 * @since 1.0.0
		 *
		 * @param string $table Name of the table. Will be replaced by the fully qualified database table name.
		 * @param array  $data  Data to replace (in column => value pairs).
		 *                      Both $data columns and $data values should be "raw" (neither should be SQL escaped).
		 *                      Sending a null value will cause the column to be set to NULL.
		 * @return int|bool The number of rows affected, or false on error.
		 */
		public function replace( $table, $data ) {
			return $this->insert_replace_delete_helper( 'replace', $table, $data );
		}

		/**
		 * Updates a row in a database table.
		 *
		 * @since 1.0.0
		 *
		 * @param string $table Name of the table. Will be replaced by the fully qualified database table name.
		 * @param array  $data  Data to update (in column => value pairs).
		 *                      Both $data columns and $data values should be "raw" (neither should be SQL escaped).
		 *                      Sending a null value will cause the column to be set to NULL.
		 * @param array  $where A named array of WHERE clauses (in column => value pairs). Multiple clauses will be
		 *                      joined with ANDs. Both $where columns and $where values should be "raw".
		 *                      Sending a null value will create an IS NULL comparison.
		 * @return int|bool The number of rows updated, or false on error.
		 */
		public function update( $table, $data, $where ) {
			return $this->update_helper( $table, $data, $where );
		}

		/**
		 * Deletes a row in a database table.
		 *
		 * @since 1.0.0
		 *
		 * @param string $table Name of the table. Will be replaced by the fully qualified database table name.
		 * @param array  $where A named array of WHERE clauses (in column => value pairs). Multiple clauses will be
		 *                      joined with ANDs. Both $where columns and $where values should be "raw".
		 *                      Sending a null value will create an IS NULL comparison.
		 * @return int|bool The number of rows updated, or false on error.
		 */
		public function delete( $table, $where ) {
			return $this->insert_replace_delete_helper( 'delete', $table, $where );
		}

		/**
		 * Magic isset-er.
		 *
		 * Proxies to $wpdb.
		 *
		 * @since 1.0.0
		 *
		 * @param string $property Property to check for.
		 * @return bool True if property is available, false otherwise.
		 */
		public function __isset( $property ) {
			switch ( $property ) {
				case 'insert_id':
					return true;
			}

			return false;
		}

		/**
		 * Magic getter.
		 *
		 * Proxies to $wpdb.
		 *
		 * @since 1.0.0
		 *
		 * @param string $property Property to get.
		 * @return mixed Property value if available, null otherwise.
		 */
		public function __get( $property ) {
			switch ( $property ) {
				case 'insert_id':
					return $this->wpdb->$property;
			}

			return null;
		}

		/**
		 * Checks whether the database tables are up to date for the current site.
		 *
		 * If outdated, the tables will be refreshed.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $force Optional. Whether to force install regardless of the check. Default false.
		 */
		public function check( $force = false ) {
			if ( ! $force && $this->version <= $this->options()->get( 'db_version', 0 ) ) {
				return;
			}

			$this->install_single();

			$this->options()->update( 'db_version', $this->version );
		}

		/**
		 * Uninstalls the database tables.
		 *
		 * This method is called on plugin deletion.
		 *
		 * On a Multisite/Multinetwork installation, this method ensures that the database tables
		 * for all sites in the entire setup are wiped out.
		 *
		 * @since 1.0.0
		 */
		public function uninstall() {
			if ( is_multisite() ) {
				$network_ids = $this->options()->get_networks_with_option( 'db_version' );
				foreach ( $network_ids as $network_id ) {
					$versions = $this->options()->get_for_all_sites( 'db_version', $network_id );

					foreach ( array_keys( $versions ) as $site_id ) {
						switch_to_blog( $site_id );
						$this->uninstall_single();
						restore_current_blog();
					}

					$this->options()->flush( 'db_version', $network_id );
				}
			} else {
				$this->uninstall_single();

				$this->options()->flush( 'db_version' );
			}
		}

		/**
		 * Sets the database version.
		 *
		 * @since 1.0.0
		 *
		 * @param int $version The database version.
		 */
		public function set_version( $version ) {
			$this->version = $version;
		}

		/**
		 * Adds a database table.
		 *
		 * The second parameter $schema must be an array of strings, i.e. lines for the inner part of the table
		 * schema used to create the table. Trailing commas must not be included. For example, a line could
		 * look like this:
		 *
		 * `"id bigint(20) unsigned NOT NULL auto_increment"`
		 *
		 * @since 1.0.0
		 *
		 * @param string $table  Unprefixed name of database table.
		 * @param array  $schema Array of lines for the database schema.
		 * @return bool|WP_Error True if successful, error object otherwise.
		 */
		public function add_table( $table, $schema = array() ) {
			if ( isset( $this->wpdb->$table ) || isset( $this->tables[ $table ] ) ) {
				return new WP_Error( 'table_already_exist', sprintf( $this->get_translation( 'table_already_exist' ), $table ) );
			}

			if ( empty( $schema ) ) {
				return new WP_Error( 'schema_empty', $this->get_translation( 'schema_empty' ) );
			}

			$schemastring = "\n\t" . implode( ",\n\t", $schema ) . "\n";

			if ( ! empty( $this->schema ) ) {
				$this->schema .= "\n";
			}
			$this->schema .= 'CREATE TABLE %' . $table . '% (' . $schemastring . ') ' . $this->wpdb->get_charset_collate() . ';';

			$prefixed_table_name = $this->get_prefix() . $table;

			$this->wpdb->tables[]               = $prefixed_table_name;
			$this->wpdb->{$prefixed_table_name} = $this->wpdb->prefix . $prefixed_table_name;

			$this->tables[ $table ] = $prefixed_table_name;

			return true;
		}

		/**
		 * Returns the name of a registered table in a specific format.
		 *
		 * @since 1.0.0
		 *
		 * @param string $table Unprefixed table name.
		 * @param string $mode  Optional. Either 'raw', 'prefixed' or 'full'. Default 'raw'.
		 * @return string|null The table name according to $mode, or null if table not registered.
		 */
		public function get_table( $table, $mode = 'raw' ) {
			if ( ! $this->table_exists( $table ) ) {
				return null;
			}

			if ( 'raw' === $mode ) {
				return $table;
			}

			$prefixed_table_name = $this->tables[ $table ];

			if ( 'prefixed' === $mode ) {
				return $prefixed_table_name;
			}

			return $this->wpdb->$prefixed_table_name;
		}

		/**
		 * Checks whether a specific plugin database table is registered.
		 *
		 * @since 1.0.0
		 *
		 * @param string $table  Unprefixed name of database table.
		 * @return bool True if the table is registered, false otherwise.
		 */
		public function table_exists( $table ) {
			return isset( $this->tables[ $table ] );
		}

		/**
		 * Handles SELECT queries to the database.
		 *
		 * Used internally by all `get_*()` methods of the class.
		 *
		 * @since 1.0.0
		 *
		 * @param string $method_name Method name in `$wpdb` to call.
		 * @param string $query       SQL query. Should use table placeholder instead of fully qualified table names.
		 * @param array  $args        Optional. The array of variables to substitute into the query's placeholders.
		 * @return array|object|string|null Database query result based on method used, or null on failure.
		 */
		protected function generic_query_helper( $method_name, $query, $args = array() ) {
			$query = $this->replace_table_placeholders( $query );

			if ( ! empty( $args ) ) {
				$query = $this->wpdb->prepare( $query, $args ); // phpcs:ignore
			}

			$result = call_user_func( array( $this->wpdb, $method_name ), $query );

			if ( empty( $result ) ) {
				return $result;
			}

			if ( is_array( $result ) ) {
				$results = array();

				foreach ( $result as $key => $value ) {
					$results[ $key ] = $this->prepare_data_from_database( $value );
				}

				return $results;
			}

			return $this->prepare_data_from_database( $result );
		}

		/**
		 * Handles insert and replace queries to the database.
		 *
		 * @since 1.0.0
		 *
		 * @param string $method_name Method name in `$wpdb` to call.
		 * @param string $table       Name of the table. Will be replaced by the fully qualified database table name.
		 * @param array  $data        Data to insert or replace or conditional data for deletion (in column => value pairs).
		 *                            Both $data columns and $data values should be "raw" (neither should be SQL escaped).
		 *                            Sending a null value will cause the column to be set to NULL.
		 * @return int|bool The number of rows affected, or false on error.
		 */
		protected function insert_replace_delete_helper( $method_name, $table, $data ) {
			if ( isset( $this->tables[ $table ] ) ) {
				$table = $this->table_to_db_table( $this->tables[ $table ] );
			}

			$data = $this->prepare_data_for_database( $data );

			return call_user_func( array( $this->wpdb, $method_name ), $table, $data, $this->create_format_from_data( $data ) );
		}

		/**
		 * Handles update queries to the database.
		 *
		 * @since 1.0.0
		 *
		 * @param string $table Name of the table. Will be replaced by the fully qualified database table name.
		 * @param array  $data  Data to update (in column => value pairs).
		 *                      Both $data columns and $data values should be "raw" (neither should be SQL escaped).
		 *                      Sending a null value will cause the column to be set to NULL.
		 * @param array  $where A named array of WHERE clauses (in column => value pairs). Multiple clauses will be
		 *                      joined with ANDs. Both $where columns and $where values should be "raw".
		 *                      Sending a null value will create an IS NULL comparison.
		 * @return int|bool The number of rows updated, or false on error.
		 */
		protected function update_helper( $table, $data, $where ) {
			if ( isset( $this->tables[ $table ] ) ) {
				$table = $this->table_to_db_table( $this->tables[ $table ] );
			}

			$data  = $this->prepare_data_for_database( $data );
			$where = $this->prepare_data_for_database( $where );

			return $this->wpdb->update( $table, $data, $where, $this->create_format_from_data( $data ), $this->create_format_from_data( $where ) );
		}

		/**
		 * Prepares data prior to setting it in the database.
		 *
		 * @since 1.0.0
		 *
		 * @param array $data Data as $column => $value pairs.
		 * @return array Prepared data.
		 */
		protected function prepare_data_for_database( $data ) {
			$prepared_data = array();

			foreach ( $data as $column => $value ) {
				$prepared_data[ $column ] = maybe_serialize( $value );
			}

			return $prepared_data;
		}

		/**
		 * Prepares data after retrieving it from the database.
		 *
		 * @since 1.0.0
		 *
		 * @param object|string $data Database object or column value.
		 * @return object|string|array Prepared database object or column value.
		 */
		protected function prepare_data_from_database( $data ) {
			if ( is_object( $data ) ) {
				$prepared_data = new \stdClass();

				foreach ( get_object_vars( $data ) as $column => $value ) {
					$prepared_data->$column = maybe_unserialize( $value );
				}

				return $prepared_data;
			}

			return maybe_unserialize( $data );
		}

		/**
		 * Installs the database tables for the current site.
		 *
		 * @since 1.0.0
		 */
		protected function install_single() {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$queries = $this->replace_table_placeholders( $this->schema );

			dbDelta( $queries );

			/**
			 * Fires when the plugin is installed on the current site.
			 *
			 * @since 1.0.0
			 */
			do_action( "{$this->get_prefix()}install" );
		}

		/**
		 * Uninstalls the database tables for the current site.
		 *
		 * @since 1.0.0
		 */
		protected function uninstall_single() {
			foreach ( $this->tables as $prefixed_table_name ) {
				$db_table_name = $this->table_to_db_table( $prefixed_table_name );
				$this->wpdb->query( "DROP TABLE $db_table_name" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}

			/**
			 * Fires when the plugin is uninstalled from the current site.
			 *
			 * @since 1.0.0
			 */
			do_action( "{$this->get_prefix()}uninstall" );
		}

		/**
		 * Processes query placeholder arguments.
		 *
		 * This method ensures that arguments in both `sprintf()` and `vsprintf()` syntax are supported.
		 * Used internally with `func_get_args()` results passed to it. The first argument will be omitted.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args Array of method arguments.
		 * @return array Array of query placeholder arguments.
		 */
		protected function process_query_placeholder_args( $args ) {
			array_shift( $args );

			if ( isset( $args[0] ) && is_array( $args[0] ) ) {
				$args = $args[0];
			}

			return $args;
		}

		/**
		 * Creates the format array from data input.
		 *
		 * The WordPress database abstraction object requires passing the format placeholders for the parameters
		 * on several types of queries. This method automatically generates the necessary array.
		 *
		 * @since 1.0.0
		 *
		 * @param array $data Array of data (in column => value pairs).
		 * @return array Format array to use with WordPress database methods.
		 */
		protected function create_format_from_data( $data ) {
			$format = array();
			foreach ( $data as $value ) {
				if ( is_integer( $value ) ) {
					$format[] = '%d';
				} elseif ( is_float( $value ) ) {
					$format[] = '%f';
				} else {
					$format[] = '%s';
				}
			}

			return $format;
		}

		/**
		 * Replaces table placeholders in an SQL query.
		 *
		 * @since 1.0.0
		 *
		 * @param string $query SQL query with table placeholders.
		 * @return string Valid SQL query with fully qualified table names.
		 */
		protected function replace_table_placeholders( $query ) {
			$search  = array_map( array( $this, 'table_to_placeholder' ), array_keys( $this->tables ) );
			$replace = array_map( array( $this, 'table_to_db_table' ), $this->tables );

			return str_replace( $search, $replace, $query );
		}

		/**
		 * Transforms a basic table name into a placeholder for it.
		 *
		 * @since 1.0.0
		 *
		 * @param string $table_name The basic table name, without any prefixes.
		 * @return string The table name placeholder.
		 */
		protected function table_to_placeholder( $table_name ) {
			return '%' . $table_name . '%';
		}

		/**
		 * Transforms a prefixed table name into the full database table name for the current site.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prefixed_table_name The table name with the plugin prefix.
		 * @return string The full database table name.
		 */
		protected function table_to_db_table( $prefixed_table_name ) {
			return $this->wpdb->$prefixed_table_name;
		}

		/**
		 * Sets up all action and filter hooks for the service.
		 *
		 * This method must be implemented and then be called from the constructor.
		 *
		 * @since 1.0.0
		 */
		protected function setup_hooks() {
			$this->actions = array(
				array(
					'name'     => 'admin_init',
					'callback' => array( $this, 'check' ),
					'priority' => 10,
					'num_args' => 0,
				),
			);
		}
	}

endif;
