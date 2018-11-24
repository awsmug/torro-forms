<?php
/**
 * Storage class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Storage' ) ) :

	/**
	 * Storage class for database objects.
	 *
	 * @since 1.0.0
	 */
	class Storage {
		/**
		 * All stored models.
		 *
		 * @since 1.0.0
		 * @static
		 * @var array
		 */
		private static $models = array();

		/**
		 * Types that are global across all sites.
		 *
		 * @since 1.0.0
		 * @static
		 * @var array
		 */
		private static $global_groups = array();

		/**
		 * Stores a model in the storage.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $cache_group The object cache group.
		 * @param int    $model_id    ID of the model to set.
		 * @param Model  $model       Model to set for the ID.
		 * @return bool True on success, or false on failure.
		 */
		public static function store( $cache_group, $model_id, $model ) {
			if ( ! isset( $models[ $cache_group ] ) ) {
				$models[ $cache_group ] = array();
			}

			if ( ! self::is_group_global( $cache_group ) && is_multisite() ) {
				$site_id = get_current_blog_id();

				if ( ! isset( self::$models[ $cache_group ][ $site_id ] ) ) {
					self::$models[ $cache_group ][ $site_id ] = array();
				}

				self::$models[ $cache_group ][ $site_id ][ $model_id ] = $model;

				return true;
			}

			self::$models[ $cache_group ][ $model_id ] = $model;

			return true;
		}

		/**
		 * Retrieves a model from the storage.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $cache_group The object cache group.
		 * @param int    $model_id    ID of the model to retrieve.
		 * @return Model|null The model on success, or null if it doesn't exist.
		 */
		public static function retrieve( $cache_group, $model_id ) {
			if ( ! isset( self::$models[ $cache_group ] ) ) {
				return null;
			}

			if ( ! self::is_group_global( $cache_group ) && is_multisite() ) {
				$site_id = get_current_blog_id();

				if ( ! isset( self::$models[ $cache_group ][ $site_id ] ) ) {
					return null;
				}

				if ( ! isset( self::$models[ $cache_group ][ $site_id ][ $model_id ] ) ) {
					return null;
				}

				return self::$models[ $cache_group ][ $site_id ][ $model_id ];
			}

			if ( ! isset( self::$models[ $cache_group ][ $model_id ] ) ) {
				return null;
			}

			return self::$models[ $cache_group ][ $model_id ];
		}

		/**
		 * Checks whether a model is set in the storage.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $cache_group The object cache group.
		 * @param int    $model_id    ID of the model to check for.
		 * @return bool True if the model is set, or false otherwise.
		 */
		public static function is_stored( $cache_group, $model_id ) {
			if ( ! isset( self::$models[ $cache_group ] ) ) {
				return false;
			}

			if ( ! self::is_group_global( $cache_group ) && is_multisite() ) {
				$site_id = get_current_blog_id();

				if ( ! isset( self::$models[ $cache_group ][ $site_id ] ) ) {
					return false;
				}

				return isset( self::$models[ $cache_group ][ $site_id ][ $model_id ] );
			}

			return isset( self::$models[ $cache_group ][ $model_id ] );
		}

		/**
		 * Registers a global cache group.
		 *
		 * Global cache groups are not scoped within a specific site.
		 * Instead their IDs are unique across all sites.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $cache_group The object cache group.
		 * @return bool Always returns true.
		 */
		public static function register_global_group( $cache_group ) {
			if ( ! self::is_group_global( $cache_group ) ) {
				self::$global_groups[] = $cache_group;
			}

			return true;
		}

		/**
		 * Checks whether an object cache group is registered as global.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $cache_group The object cache group.
		 * @return boolean True if the cache group is global, otherwise false.
		 */
		private static function is_group_global( $cache_group ) {
			return in_array( $cache_group, self::$global_groups, true );
		}
	}

endif;
