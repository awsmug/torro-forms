<?php
/**
 * Service instantiator class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib;

use Leaves_And_Love\Plugin_Lib\Translations\Translations;
use ReflectionClass;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Service_Instantiator' ) ) :

	/**
	 * Class to instantiate services comfortably.
	 *
	 * @since 1.0.0
	 */
	final class Service_Instantiator {
		/**
		 * Contains the service instances for all plugins.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @var array
		 */
		private static $instances = array();

		/**
		 * Instantiates a specific class.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $class_name Fully qualified service class name.
		 * @param string $prefix     Prefix for the plugin the service should be instantiated for.
		 * @param mixed  $args,...   Further arguments to pass to the constructor.
		 * @return Service|null The service instance, or null if invalid.
		 */
		public static function instantiate( $class_name, $prefix ) {
			if ( ! class_exists( $class_name ) || ! is_subclass_of( $class_name, 'Leaves_And_Love\Plugin_Lib\Service' ) ) {
				return null;
			}

			if ( ! isset( self::$instances[ $prefix ][ $class_name ] ) ) {
				if ( ! isset( self::$instances[ $prefix ] ) ) {
					self::$instances[ $prefix ] = array();
				}

				$reflected_class = new ReflectionClass( $class_name );

				$args = func_num_args() === 2 ? array() : array_slice( func_get_args(), 2 );

				$instantiation_args = array();
				foreach ( self::get_constructor_params( $reflected_class ) as $param ) {
					if ( $param->isOptional() ) {
						continue;
					}

					$instantiation_args[] = self::fill_constructor_param( $param->name, $class_name, $prefix, $args );
				}

				self::$instances[ $prefix ][ $class_name ] = $reflected_class->newInstanceArgs( $instantiation_args );
			}

			return self::$instances[ $prefix ][ $class_name ];
		}

		/**
		 * Fills a specific parameter for a class constructor.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $param      Parameter name.
		 * @param string $class_name Fully qualified service class name.
		 * @param string $prefix     Prefix for the plugin the class should be instantiated for.
		 * @param array  $args       Further arguments.
		 * @return mixed Value for the parameter.
		 */
		private static function fill_constructor_param( $param, $class_name, $prefix, $args ) {
			switch ( $param ) {
				case 'prefix':
					return $prefix;
				case 'services':
					return self::fill_constructor_services( $class_name, $prefix, $args );
				case 'args':
					return self::fill_constructor_args( $class_name, $args );
				case 'translations':
					return self::fill_constructor_translations( $class_name, $args );
			}

			return null;
		}

		/**
		 * Fills the $services parameter of a constructor.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $class_name Fully qualified service class name.
		 * @param string $prefix     Prefix for the plugin the class should be instantiated for.
		 * @param array  $args       Further arguments.
		 * @return array Array of required service instances.
		 */
		private static function fill_constructor_services( $class_name, $prefix, $args ) {
			$service_definitions = call_user_func( array( $class_name, 'get_service_definitions' ) );

			$services_arg = array();
			foreach ( $args as $arg ) {
				if ( ! is_array( $arg ) ) {
					continue;
				}

				foreach ( $arg as $key => $value ) {
					if ( isset( $service_definitions[ $key ] ) && is_a( $value, $service_definitions[ $key ] ) ) {
						$services_arg = $arg;
						break 2;
					}
				}
			}

			$services = array();
			foreach ( $service_definitions as $name => $class_name ) {
				if ( isset( $services_arg[ $name ] ) ) {
					$services[ $name ] = $services_arg[ $name ];
				} else {
					$services[ $name ] = self::instantiate( $class_name, $prefix );
				}
			}

			return $services;
		}

		/**
		 * Fills the $args parameter of a constructor.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $class_name Fully qualified service class name.
		 * @param array  $args       Further arguments.
		 * @return array Array of arguments.
		 */
		private static function fill_constructor_args( $class_name, $args ) {
			$args_parsers = call_user_func( array( $class_name, 'get_args_parsers' ) );

			foreach ( $args as $arg ) {
				if ( ! is_array( $arg ) ) {
					continue;
				}

				foreach ( $arg as $key => $value ) {
					if ( isset( $args_parsers[ $key ] ) && ! is_a( $value, 'Leaves_And_Love\Plugin_Lib\Service' ) ) {
						return $arg;
					}
				}
			}

			return array();
		}

		/**
		 * Fills the $translations parameter of a constructor.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $class_name Fully qualified service class name.
		 * @param array  $args       Further arguments.
		 * @return Translations|null Translations object, or null on failure.
		 */
		private static function fill_constructor_translations( $class_name, $args ) {
			foreach ( $args as $arg ) {
				if ( is_object( $arg ) && is_a( $arg, 'Leaves_And_Love\Plugin_Lib\Translations\Translations' ) ) {
					return $arg;
				}
			}

			$class_name_parts = explode( '\\', $class_name );
			if ( count( $class_name_parts ) < 3 ) {
				return null;
			}

			$translations_class_name = $class_name_parts[0] . '\\' . $class_name_parts[1] . '\\Translations\\Translations_' . array_pop( $class_name_parts );
			if ( ! class_exists( $translations_class_name ) ) {
				return null;
			}

			return new $translations_class_name();
		}

		/**
		 * Returns the parameters for a specific class constructor.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param ReflectionClass $reflected_class The reflected class.
		 * @return array Array of ReflectionParam elements.
		 */
		private static function get_constructor_params( $reflected_class ) {
			$reflected_constructor = $reflected_class->getConstructor();
			if ( ! $reflected_constructor ) {
				return array();
			}

			return $reflected_constructor->getParameters();
		}
	}

endif;
