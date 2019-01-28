<?php
/**
 * API-API Container class
 *
 * @package APIAPI\Core
 * @since 1.0.0
 */

namespace APIAPI\Core;

use APIAPI\Core\Exception\Invalid_Argument_Exception;
use ReflectionClass;

if ( ! class_exists( 'APIAPI\Core\Container' ) ) {

	/**
	 * Container class for the API-API.
	 *
	 * Manages modules of a certain class type.
	 *
	 * @since 1.0.0
	 */
	abstract class Container implements Container_Interface {
		/**
		 * The type of the modules in this container.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $type;

		/**
		 * The name of the class all modules must inherit.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $module_class_name;

		/**
		 * The registered modules.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $modules = array();

		/**
		 * The manager instance.
		 *
		 * @since 1.0.0
		 * @var Manager
		 */
		protected $manager;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param Manager $manager The APIAPI manager instance.
		 */
		public function __construct( Manager $manager ) {
			$this->manager = $manager;

			$this->type              = $this->get_type();
			$this->module_class_name = $this->get_module_class_name();
		}

		/**
		 * Registers a module.
		 *
		 * @since 1.0.0
		 *
		 * @param string        $name   Unique slug for the module.
		 * @param object|string $module Module class instance or class name.
		 *
		 * @throws Invalid_Argument_Exception Thrown when module is already registered or an invalid class.
		 */
		public function register( $name, $module ) {
			if ( $this->is_registered( $name ) ) {
				throw new Invalid_Argument_Exception( sprintf( 'The %1$s %2$s already exists.', $this->type, $name ) );
			}

			if ( is_string( $module ) ) {
				$args = array_slice( func_get_args(), 2 );

				if ( ! empty( $args ) ) {
					array_unshift( $args, $name );
					$reflected_class = new ReflectionClass( $module );

					$module = $reflected_class->newInstanceArgs( $args );
				} else {
					$module = new $module( $name );
				}
			}

			if ( ! is_a( $module, $this->module_class_name ) ) {
				throw new Invalid_Argument_Exception( sprintf( 'The %1$s %2$s must have a subclass of %3$s.', $this->type, $name, $this->module_class_name ) );
			}

			$this->modules[ $name ] = $module;
		}

		/**
		 * Unregisters a module.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Unique slug of the module.
		 */
		public function unregister( $name ) {
			if ( ! $this->is_registered( $name ) ) {
				return;
			}

			unset( $this->modules[ $name ] );
		}

		/**
		 * Returns a specific module.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Unique slug of the module.
		 * @return object|null The module object, or null if it does not exist.
		 */
		public function get( $name ) {
			if ( ! $this->is_registered( $name ) ) {
				return null;
			}

			return $this->modules[ $name ];
		}

		/**
		 * Returns all registered modules.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of module objects.
		 */
		public function get_all() {
			return $this->modules;
		}

		/**
		 * Checks whether a specific module is registered.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Unique slug of the module.
		 * @return bool True if the module is registered, false otherwise.
		 */
		public function is_registered( $name ) {
			$this->manager->hooks()->trigger( 'apiapi.manager.' . $this->type . 's.pre_is_registered', $name, $this );

			return isset( $this->modules[ $name ] );
		}

		/**
		 * Returns the type of the modules in this container.
		 *
		 * @since 1.0.0
		 *
		 * @return string Type of the modules.
		 */
		protected abstract function get_type();

		/**
		 * Returns the name of the class all modules must inherit.
		 *
		 * @since 1.0.0
		 *
		 * @return string Name of the base module class.
		 */
		protected abstract function get_module_class_name();
	}

}
