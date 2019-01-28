<?php
/**
 * API-API Authenticators class
 *
 * @package APIAPI\Core
 * @since 1.0.0
 */

namespace APIAPI\Core;

use APIAPI\Core\Authenticators\Authenticator;

if ( ! class_exists( 'APIAPI\Core\Authenticators' ) ) {

	/**
	 * Authenticators class for the API-API.
	 *
	 * Manages authenticators.
	 *
	 * @since 1.0.0
	 */
	class Authenticators extends Container {
		/**
		 * Registers an authenticator.
		 *
		 * @since 1.0.0
		 *
		 * @param string               $name          Unique slug for the authenticator.
		 * @param Authenticator|string $authenticator Authenticator class instance or class name.
		 */
		public function register( $name, $authenticator ) {
			$args = func_get_args();
			call_user_func_array( array( 'parent', __FUNCTION__ ), $args );
		}

		/**
		 * Unregisters an authenticator.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Unique slug of the authenticator.
		 */
		public function unregister( $name ) {
			parent::unregister( $name );
		}

		/**
		 * Returns a specific authenticator.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Unique slug of the authenticator.
		 * @return Authenticator|null The authenticator object, or null if it does not exist.
		 */
		public function get( $name ) {
			return parent::get( $name );
		}

		/**
		 * Returns all registered authenticators.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of authenticator objects.
		 */
		public function get_all() {
			return parent::get_all();
		}

		/**
		 * Checks whether a specific authenticator is registered.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Unique slug of the authenticator.
		 * @return bool True if the authenticator is registered, false otherwise.
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
			return 'authenticator';
		}

		/**
		 * Returns the name of the class all modules must inherit.
		 *
		 * @since 1.0.0
		 *
		 * @return string Name of the base module class.
		 */
		protected function get_module_class_name() {
			return Authenticator::class;
		}
	}

}
