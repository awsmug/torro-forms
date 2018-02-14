<?php
/**
 * API-API Storages class
 *
 * @package APIAPI\Core
 * @since 1.0.0
 */

namespace APIAPI\Core;

use APIAPI\Core\Storages\Storage;

if ( ! class_exists( 'APIAPI\Core\Storages' ) ) {

	/**
	 * Storages class for the API-API.
	 *
	 * Manages storages.
	 *
	 * @since 1.0.0
	 */
	class Storages extends Container {
		/**
		 * Registers a storage.
		 *
		 * @since 1.0.0
		 *
		 * @param string         $name    Unique slug for the storage.
		 * @param Storage|string $storage Storage class instance or class name.
		 */
		public function register( $name, $storage ) {
			$args = func_get_args();
			call_user_func_array( array( 'parent', __FUNCTION__ ), $args );
		}

		/**
		 * Unregisters a storage.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Unique slug of the storage.
		 */
		public function unregister( $name ) {
			parent::unregister( $name );
		}

		/**
		 * Returns a specific storage.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Unique slug of the storage.
		 * @return Storage|null The storage object, or null if it does not exist.
		 */
		public function get( $name ) {
			return parent::get( $name );
		}

		/**
		 * Returns all registered storages.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of storage objects.
		 */
		public function get_all() {
			return parent::get_all();
		}

		/**
		 * Checks whether a specific storage is registered.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Unique slug of the storage.
		 * @return bool True if the storage is registered, false otherwise.
		 */
		public function is_registered( $name ) {
			return parent::is_registered( $name );
		}

		/**
		 * Returns the type of the modules in this container.
		 *
		 * @since 1.0.0
		 *
		 * @return string Type of the modules.
		 */
		protected function get_type() {
			return 'storage';
		}

		/**
		 * Returns the name of the class all modules must inherit.
		 *
		 * @since 1.0.0
		 *
		 * @return string Name of the base module class.
		 */
		protected function get_module_class_name() {
			return Storage::class;
		}
	}

}
