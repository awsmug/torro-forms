<?php
/**
 * Container service trait
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Traits;

use Leaves_And_Love\Plugin_Lib\Error_Handler;
use Leaves_And_Love\Plugin_Lib\Service;
use ReflectionClass;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\Traits\Container_Service_Trait' ) ) :

	/**
	 * Container service trait.
	 *
	 * This adds functionality to better manage dependency injection of internal services.
	 * Each class using this trait can specify the required internal services through static
	 * properties with names like `$service_{$service_name}` and the required class name as
	 * value.
	 *
	 * @since 1.0.0
	 */
	trait Container_Service_Trait {
		/**
		 * The internal service instances, as `$name => $instance` pairs.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $services = array();

		/**
		 * Magic call method.
		 *
		 * Supports retrieval of an internally used service.
		 *
		 * @since 1.0.0
		 *
		 * @param string $method_name Method name. Should be the name of a service.
		 * @param array  $args        Method arguments. Unused here.
		 * @return Service The service instance, or null if it does not exist.
		 */
		public function __call( $method_name, $args ) {
			if ( isset( $this->services[ $method_name ] ) ) {
				return $this->services[ $method_name ];
			}

			return null;
		}

		/**
		 * Sets the services for this class.
		 *
		 * @since 1.0.0
		 *
		 * @param array $services Array of passed services.
		 */
		protected function set_services( $services ) {
			$missing_services = array();

			foreach ( self::get_service_definitions() as $name => $class_name ) {
				if ( ! isset( $services[ $name ] ) || ! is_a( $services[ $name ], $class_name ) ) {
					$missing_services[] = $name;
					continue;
				}

				$this->services[ $name ] = $services[ $name ];
			}

			if ( ! empty( $missing_services ) ) {
				$error_handler = $this->service( 'error_handler' );
				if ( null === $error_handler ) {
					$error_handler = Error_Handler::get_base_handler();
				}

				$method_name = get_class( $this ) . '::set_services';

				$error_handler->missing_services( $method_name, $missing_services );
			}
		}

		/**
		 * Returns the internal service definitions.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @return array Array of `$name => $class_name` pairs.
		 */
		public static function get_service_definitions() {
			$reflection = new ReflectionClass( get_called_class() );
			$properties = $reflection->getStaticProperties();

			$definitions = array();
			foreach ( $properties as $name => $value ) {
				if ( 0 !== strpos( $name, 'service_' ) ) {
					continue;
				}

				$definitions[ substr( $name, 8 ) ] = $value;
			}

			// The error_handler service is always required unless no services are required at all.
			if ( ! empty( $definitions ) && ! isset( $definitions['error_handler'] ) ) {
				$definitions['error_handler'] = 'Leaves_And_Love\Plugin_Lib\Error_Handler';
			}

			return $definitions;
		}
	}

endif;
