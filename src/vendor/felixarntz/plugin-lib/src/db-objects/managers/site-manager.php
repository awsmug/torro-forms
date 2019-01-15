<?php
/**
 * Manager class for sites
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Managers;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Storage;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Date_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Meta_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Site;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Site_Collection;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Queries\Site_Query;
use Leaves_And_Love\Plugin_Lib\Translations\Translations_Site_Manager;
use Leaves_And_Love\Plugin_Lib\DB;
use Leaves_And_Love\Plugin_Lib\Cache;
use Leaves_And_Love\Plugin_Lib\Meta;
use Leaves_And_Love\Plugin_Lib\Error_Handler;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Managers\Site_Manager' ) ) :

	/**
	 * Class for a sites manager
	 *
	 * This class represents a sites manager. Must only be used in a multisite setup.
	 *
	 * @since 1.0.0
	 */
	class Site_Manager extends Core_Manager {
		use Date_Manager_Trait, Meta_Manager_Trait;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string                    $prefix       The instance prefix.
		 * @param array                     $services     {
		 *     Array of service instances.
		 *
		 *     @type DB            $db            The database instance.
		 *     @type Cache         $cache         The cache instance.
		 *     @type Meta          $meta          The meta instance.
		 *     @type Error_Handler $error_handler The error handler instance.
		 * }
		 * @param Translations_Site_Manager $translations Translations instance.
		 */
		public function __construct( $prefix, $services, $translations ) {
			$this->class_name            = Site::class;
			$this->collection_class_name = Site_Collection::class;
			$this->query_class_name      = Site_Query::class;

			$this->singular_slug = 'site';
			$this->plural_slug   = 'sites';

			$this->table_name       = 'blogs';
			$this->cache_group      = 'sites';
			$this->meta_type        = 'site';
			$this->fetch_callback   = 'get_site';
			$this->primary_property = 'blog_id';
			$this->date_property    = 'registered';

			Storage::register_global_group( $this->cache_group );

			parent::__construct( $prefix, $services, $translations );
		}

		/**
		 * Internal method to insert a new site into the database.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args Array of column => value pairs for the new database row.
		 * @return int|false The ID of the new site, or false on failure.
		 */
		protected function insert_into_db( $args ) {
			if ( ! isset( $args['domain'] ) || ! isset( $args['path'] ) ) {
				return false;
			}

			$domain = $args['domain'];
			unset( $args['domain'] );

			$path = $args['path'];
			unset( $args['path'] );

			if ( isset( $args['site_id'] ) ) {
				$network_id = $args['site_id'];
				unset( $args['site_id'] );
			} else {
				$network_id = get_current_network_id();
			}

			if ( isset( $args['registered'] ) ) {
				unset( $args['registered'] );
			}

			if ( isset( $args['last_updated'] ) ) {
				unset( $args['last_updated'] );
			}

			if ( isset( $args['user_id'] ) ) {
				$user_id = absint( $args['user_id'] );
				unset( $args['user_id'] );
			} else {
				$user_id = get_current_user_id();
				if ( ! $user_id ) {
					$user_id = 1;
				}
			}

			$result = wpmu_create_blog( $domain, $path, '', $user_id, $args, $network_id );
			if ( is_wp_error( $result ) ) {
				return false;
			}

			return (int) $result;
		}

		/**
		 * Internal method to update an existing site in the database.
		 *
		 * @since 1.0.0
		 *
		 * @param int   $site_id ID of the site to update.
		 * @param array $args    Array of column => value pairs to update in the database row.
		 * @return bool True on success, or false on failure.
		 */
		protected function update_in_db( $site_id, $args ) {
			return update_blog_details( $site_id, $args );
		}

		/**
		 * Internal method to delete a site from the database.
		 *
		 * @since 1.0.0
		 *
		 * @param int $site_id ID of the site to delete.
		 * @return bool True on success, or false on failure.
		 */
		protected function delete_from_db( $site_id ) {
			$site = get_site( $site_id );
			if ( ! $site ) {
				return false;
			}

			if ( ! function_exists( 'wpmu_delete_blog' ) ) {
				require_once ABSPATH . 'wp-admin/includes/ms.php';
			}

			wpmu_delete_blog( $site_id, true );

			return true;
		}
	}

endif;
