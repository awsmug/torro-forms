<?php
/**
 * Arguments service trait
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Traits;

use ReflectionClass;
use ReflectionMethod;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\Traits\Args_Service_Trait' ) ) :

	/**
	 * Arguments service trait.
	 *
	 * This adds functionality to support class instance arguments.
	 * Each class using this trait can specify the arguments through static methods
	 * with names like `parse_arg_{$argument_name}` which should accept the input
	 * value and parse it.
	 *
	 * @since 1.0.0
	 */
	trait Args_Service_Trait {
		/**
		 * The internal arguments, as `$name => $value` pairs.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $args = array();

		/**
		 * Magic isset method.
		 *
		 * Supports checking for internal arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param string $property_name Property name. Should be the name of an argument.
		 * @return bool True if the argument is set, otherwise false.
		 */
		public function __isset( $property_name ) {
			if ( isset( $this->args[ $property_name ] ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Magic get method.
		 *
		 * Supports retrieval of internal arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param string $property_name Property name. Should be the name of an argument.
		 * @return mixed|null Argument value, or null if it does not exist.
		 */
		public function __get( $property_name ) {
			if ( isset( $this->args[ $property_name ] ) ) {
				return $this->args[ $property_name ];
			}

			return null;
		}

		/**
		 * Sets the arguments for this class.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args Array of arguments.
		 */
		protected function set_args( $args ) {
			foreach ( self::get_args_parsers() as $name => $callback ) {
				$value = isset( $args[ $name ] ) ? $args[ $name ] : null;

				$this->args[ $name ] = call_user_func( $callback, $value );
			}
		}

		/**
		 * Returns the internal argument parsers.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @return Array of `$name => $callback` pairs.
		 */
		public static function get_args_parsers() {
			$reflection = new ReflectionClass( get_called_class() );
			$methods    = $reflection->getMethods( ReflectionMethod::IS_STATIC );

			$parsers = array();
			foreach ( $methods as $method ) {
				if ( 0 !== strpos( $method->name, 'parse_arg_' ) ) {
					continue;
				}

				$parsers[ substr( $method->name, 10 ) ] = array( $method->class, $method->name );
			}

			return $parsers;
		}
	}

endif;
