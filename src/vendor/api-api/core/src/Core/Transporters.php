<?php
/**
 * API-API Transporters class
 *
 * @package APIAPI\Core
 * @since 1.0.0
 */

namespace APIAPI\Core;

use APIAPI\Core\Transporters\Transporter;
use APIAPI\Core\Exception\Module_Not_Registered_Exception;

if ( ! class_exists( 'APIAPI\Core\Transporters' ) ) {

	/**
	 * Transporters class for the API-API.
	 *
	 * Manages transporters.
	 *
	 * @since 1.0.0
	 */
	class Transporters extends Container {
		/**
		 * Name of the default transporter.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $default = '';

		/**
		 * Registers a transporter.
		 *
		 * @since 1.0.0
		 *
		 * @param string             $name        Unique slug for the transporter.
		 * @param Transporter|string $transporter Transporter class instance or class name.
		 */
		public function register( $name, $transporter ) {
			$args = func_get_args();
			call_user_func_array( array( 'parent', __FUNCTION__ ), $args );

			$class_name = get_class( $this->modules[ $name ] );

			if ( empty( $this->default ) ) {
				$this->default = $name;
			}
		}

		/**
		 * Unregisters a transporter.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Unique slug of the transporter.
		 */
		public function unregister( $name ) {
			parent::unregister( $name );
		}

		/**
		 * Returns a specific transporter.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Unique slug of the transporter.
		 * @return Transporter|null The transporter object, or null if it does not exist.
		 */
		public function get( $name ) {
			return parent::get( $name );
		}

		/**
		 * Returns all registered transporterss.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of transporters objects.
		 */
		public function get_all() {
			return parent::get_all();
		}

		/**
		 * Checks whether a specific transporter is registered.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Unique slug of the transporter.
		 * @return bool True if the transporter is registered, false otherwise.
		 */
		public function is_registered( $name ) {
			return parent::is_registered( $name );
		}

		/**
		 * Returns the default transporter.
		 *
		 * @since 1.0.0
		 *
		 * @return Transporter|null The default transporter object, or null if not set.
		 *
		 * @throws Module_Not_Registered_Exception Thrown when no default transporter is available.
		 */
		public function get_default() {
			if ( empty( $this->default ) ) {
				throw new Module_Not_Registered_Exception( 'No transporter is available to make a request.' );
			}

			return $this->get( $this->default );
		}

		/**
		 * Returns the name of the default transporter.
		 *
		 * @since 1.0.0
		 *
		 * @return string Name of the default transporter.
		 */
		public function get_default_name() {
			return $this->default;
		}

		/**
		 * Returns the type of the modules in this container.
		 *
		 * @since 1.0.0
		 *
		 * @return string Type of the modules.
		 */
		protected function get_type() {
			return 'transporter';
		}

		/**
		 * Returns the name of the class all modules must inherit.
		 *
		 * @since 1.0.0
		 *
		 * @return string Name of the base module class.
		 */
		protected function get_module_class_name() {
			return Transporter::class;
		}
	}

}
