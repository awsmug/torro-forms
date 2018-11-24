<?php
/**
 * Cache abstraction class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Cache' ) ) :

	/**
	 * Class for Cache API
	 *
	 * The class is a wrapper for the WordPress cache.
	 *
	 * @since 1.0.0
	 */
	class Cache extends Service {
		/**
		 * Constructor.
		 *
		 * This sets the cache group prefix.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prefix The prefix for all cache groups.
		 */
		public function __construct( $prefix ) {
			$this->set_prefix( $prefix );
		}

		/**
		 * Adds data to the cache, if the cache key doesn't already exist.
		 *
		 * @since 1.0.0
		 *
		 * @param int|string $key    The cache key to use for retrieval later.
		 * @param mixed      $data   The data to add to the cache.
		 * @param string     $group  Optional. The group to add the cache to. Enables the same key
		 *                           to be used across groups. Default 'general'.
		 * @param int        $expire Optional. When the cache data should expire, in seconds.
		 *                           Default 0 (no expiration).
		 * @return bool False if cache key and group already exist, true on success.
		 */
		public function add( $key, $data, $group = 'general', $expire = 0 ) {
			return wp_cache_add( $key, $data, $this->get_prefix() . $group, $expire );
		}

		/**
		 * Removes cache contents matching key and group.
		 *
		 * @since 1.0.0
		 *
		 * @param int|string $key   What the contents in the cache are called.
		 * @param string     $group Optional. Where the cache contents are grouped. Default 'general'.
		 * @return bool True on successful removal, false on failure.
		 */
		public function delete( $key, $group = 'general' ) {
			return wp_cache_delete( $key, $this->get_prefix() . $group );
		}

		/**
		 * Retrieves cache contents from the cache by key and group.
		 *
		 * @since 1.0.0
		 *
		 * @param int|string $key   The key under which the cache contents are stored.
		 * @param string     $group Optional. Where the cache contents are grouped. Default 'general'.
		 * @param bool       $force Optional. Whether to force an update of the local cache from the
		 *                          persistent cache. Default false.
		 * @param bool       $found Optional. Whether the key was found in the cache. Disambiguates a
		 *                          return of false, a storable value. Passed by reference. Default null.
		 * @return bool|mixed False on failure to retrieve contents, or the cache contents on success.
		 */
		public function get( $key, $group = 'general', $force = false, &$found = null ) {
			return wp_cache_get( $key, $this->get_prefix() . $group, $force, $found );
		}

		/**
		 * Replaces contents of the cache with new data.
		 *
		 * @since 1.0.0
		 *
		 * @param int|string $key    The key for the cache data that should be replaced.
		 * @param mixed      $data   The new data to store in the cache.
		 * @param string     $group  Optional. The group for the cache data that should be replaced.
		 *                           Default 'general'.
		 * @param int        $expire Optional. When to expire the cache contents, in seconds.
		 *                           Default 0 (no expiration).
		 * @return bool False if original value does not exist, true if contents were replaced.
		 */
		public function replace( $key, $data, $group = 'general', $expire = 0 ) {
			return wp_cache_replace( $key, $data, $this->get_prefix() . $group, $expire );
		}

		/**
		 * Saves data to the cache.
		 *
		 * Differs from Leaves_And_Love\Plugin_Lib\Cache::add() and Leaves_And_Love\Plugin_Lib\Cache::replace()
		 * in that it will always write data.
		 *
		 * @since 1.0.0
		 *
		 * @param int|string $key    The cache key to use for retrieval later.
		 * @param mixed      $data   The contents to store in the cache.
		 * @param string     $group  Optional. Where to group the cache contents. Enables the same key
		 *                           to be used across groups. Default 'general'.
		 * @param int        $expire Optional. When to expire the cache contents, in seconds.
		 *                           Default 0 (no expiration).
		 * @return bool False on failure, true on success.
		 */
		public function set( $key, $data, $group = 'general', $expire = 0 ) {
			return wp_cache_set( $key, $data, $this->get_prefix() . $group, $expire );
		}
	}

endif;
