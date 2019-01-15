<?php
/**
 * Manager class for Core objects
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Managers;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Managers\Core_Manager' ) ) :

	/**
	 * Base class for a core manager
	 *
	 * This class represents a general core manager.
	 *
	 * @since 1.0.0
	 */
	abstract class Core_Manager extends Manager {
		/**
		 * The callback to fetch an item from the database.
		 *
		 * @since 1.0.0
		 * @var callable
		 */
		protected $fetch_callback;

		/**
		 * Adds a new model to the database.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args Array of column => value pairs for the new database row.
		 * @return int|false The ID of the new model, or false on failure.
		 */
		public function add( $args ) {
			$id = $this->insert_into_db( $args );
			if ( ! $id ) {
				return false;
			}

			return $id;
		}

		/**
		 * Updates an existing model in the database.
		 *
		 * @since 1.0.0
		 *
		 * @param int   $model_id ID of the model to update.
		 * @param array $args     Array of column => value pairs to update in the database row.
		 * @return bool True on success, or false on failure.
		 */
		public function update( $model_id, $args ) {
			$model_id = absint( $model_id );

			$result = $this->update_in_db( $model_id, $args );
			if ( ! $result ) {
				return false;
			}

			return true;
		}

		/**
		 * Deletes an model from the database.
		 *
		 * @since 1.0.0
		 *
		 * @param int $model_id ID of the model to delete.
		 * @return bool True on success, or false on failure.
		 */
		public function delete( $model_id ) {
			$model_id = absint( $model_id );

			$result = $this->delete_from_db( $model_id );
			if ( ! $result ) {
				return false;
			}

			$this->storage_unset( $model_id );

			return true;
		}

		/**
		 * Fetches the Core object for a specific ID.
		 *
		 * @since 1.0.0
		 *
		 * @param int $model_id ID of the object to fetch.
		 * @return object|null The Core object for the requested ID, or null if it does not exist.
		 */
		public function fetch( $model_id ) {
			return call_user_func( $this->fetch_callback, $model_id );
		}

		/**
		 * Adds data to the model cache, if the cache key doesn't already exist.
		 *
		 * @since 1.0.0
		 *
		 * @param int|string $key    The cache key to use for retrieval later.
		 * @param mixed      $data   The data to add to the cache.
		 * @param int        $expire Optional. When the cache data should expire, in seconds.
		 *                           Default 0 (no expiration).
		 * @return bool False if cache key already exists, true on success.
		 */
		public function add_to_cache( $key, $data, $expire = 0 ) {
			return wp_cache_add( $key, $data, $this->cache_group, $expire );
		}

		/**
		 * Removes model cache contents matching key.
		 *
		 * @since 1.0.0
		 *
		 * @param int|string $key What the contents in the cache are called.
		 * @return bool True on successful removal, false on failure.
		 */
		public function delete_from_cache( $key ) {
			return wp_cache_delete( $key, $this->cache_group );
		}

		/**
		 * Retrieves model cache contents from the cache by key.
		 *
		 * @since 1.0.0
		 *
		 * @param int|string $key   The key under which the cache contents are stored.
		 * @param bool       $force Optional. Whether to force an update of the local cache from the
		 *                          persistent cache. Default false.
		 * @param bool       $found Optional. Whether the key was found in the cache. Disambiguates a
		 *                          return of false, a storable value. Passed by reference. Default null.
		 * @return bool|mixed False on failure to retrieve contents, or the cache contents on success.
		 */
		public function get_from_cache( $key, $force = false, &$found = null ) {
			return wp_cache_get( $key, $this->cache_group, $force, $found );
		}

		/**
		 * Replaces contents of the model cache with new data.
		 *
		 * @since 1.0.0
		 *
		 * @param int|string $key    The key for the cache data that should be replaced.
		 * @param mixed      $data   The new data to store in the cache.
		 * @param int        $expire Optional. When to expire the cache contents, in seconds.
		 *                           Default 0 (no expiration).
		 * @return bool False if original value does not exist, true if contents were replaced.
		 */
		public function replace_in_cache( $key, $data, $expire = 0 ) {
			return wp_cache_replace( $key, $data, $this->cache_group, $expire );
		}

		/**
		 * Saves data to the model cache.
		 *
		 * Differs from Leaves_And_Love\Plugin_Lib\DB_Objects\Manager::add_to_cache() and
		 * Leaves_And_Love\Plugin_Lib\DB_Objects\Manager::replace_in_cache() in that it will
		 * always write data.
		 *
		 * @since 1.0.0
		 *
		 * @param int|string $key    The cache key to use for retrieval later.
		 * @param mixed      $data   The contents to store in the cache.
		 * @param int        $expire Optional. When to expire the cache contents, in seconds.
		 *                           Default 0 (no expiration).
		 * @return bool False on failure, true on success.
		 */
		public function set_in_cache( $key, $data, $expire = 0 ) {
			return wp_cache_set( $key, $data, $this->cache_group, $expire );
		}

		/**
		 * Adds the database table.
		 *
		 * @since 1.0.0
		 */
		protected function add_database_table() {
			/* Core tables already exist. */
		}

		/**
		 * Sets up all action and filter hooks for the service.
		 *
		 * This method must be implemented and then be called from the constructor.
		 *
		 * @since 1.0.0
		 */
		protected function setup_hooks() {
			/* No hooks should be executed for core models. */
			$this->actions = array();
			$this->filters = array();
		}

		/**
		 * Internal method to insert a new model into the database.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args Array of column => value pairs for the new database row.
		 * @return int|false The ID of the new model, or false on failure.
		 */
		abstract protected function insert_into_db( $args );

		/**
		 * Internal method to update an existing model in the database.
		 *
		 * @since 1.0.0
		 *
		 * @param int   $model_id ID of the model to update.
		 * @param array $args     Array of column => value pairs to update in the database row.
		 * @return bool True on success, or false on failure.
		 */
		abstract protected function update_in_db( $model_id, $args );

		/**
		 * Internal method to delete a model from the database.
		 *
		 * @since 1.0.0
		 *
		 * @param int $model_id ID of the model to delete.
		 * @return bool True on success, or false on failure.
		 */
		abstract protected function delete_from_db( $model_id );
	}

endif;
