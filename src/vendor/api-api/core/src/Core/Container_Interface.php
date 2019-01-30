<?php
/**
 * API-API Container interface
 *
 * @package APIAPI\Core
 * @since 1.0.0
 */

namespace APIAPI\Core;

if ( ! interface_exists( 'APIAPI\Core\Container_Interface' ) ) {

	/**
	 * Container interface for the API-API.
	 *
	 * @since 1.0.0
	 */
	interface Container_Interface {
		/**
		 * Registers a module.
		 *
		 * @since 1.0.0
		 *
		 * @param string        $name   Unique slug for the module.
		 * @param object|string $module Module class instance or class name.
		 */
		public function register( $name, $module );

		/**
		 * Unregisters a module.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Unique slug of the module.
		 */
		public function unregister( $name );

		/**
		 * Returns a specific module.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Unique slug of the module.
		 * @return object|null The module object, or null if it does not exist.
		 */
		public function get( $name );

		/**
		 * Checks whether a specific module is registered.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Unique slug of the module.
		 * @return bool True if the module is registered, false otherwise.
		 */
		public function is_registered( $name );
	}

}
