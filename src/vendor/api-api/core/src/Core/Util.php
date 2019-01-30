<?php
/**
 * API-API Util class
 *
 * @package APIAPI\Core
 * @since 1.0.0
 */

namespace APIAPI\Core;

use APIAPI\Core\Exception\Invalid_Request_Parameter_Exception;

if ( ! class_exists( 'APIAPI\Core\Util' ) ) {

	/**
	 * Utility class with static methods.
	 *
	 * @since 1.0.0
	 */
	class Util {
		/**
		 * Parses an object or query string into an array of arguments, optionally filled with defaults.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param array|string|object $args     Input to parse.
		 * @param array               $defaults Optional. Array of defaults to fill missing arguments. Default none.
		 * @param bool                $strict   Optional. Whether to only allow arguments contained in the defaults.
		 *                                      Default false.
		 * @return array Array of arguments.
		 */
		public static function parse_args( $args, $defaults = null, $strict = false ) {
			if ( is_object( $args ) ) {
				$result = get_object_vars( $args );
			} elseif ( is_string( $args ) ) {
				parse_str( $args, $defaults );
			} else {
				$result = $args;
			}

			if ( is_array( $defaults ) ) {
				$result = array_merge( $defaults, $result );

				if ( $strict ) {
					$result = array_intersect_key( $result, $defaults );
				}
			}

			return $result;
		}

		/**
		 * Parses data for multiple parameters.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param array $params              Associative array of parameters and their data.
		 * @param array $additional_defaults Optional. Additional defaults to use. Passing defaults here also ensures
		 *                                   those are whitelisted as parameter data. Default empty array.
		 * @return array Parsed parameters data.
		 */
		public static function parse_params_data( array $params, array $additional_defaults = array() ) {
			foreach ( $params as $param => $data ) {
				$params[ $param ] = self::parse_param_data( $data, $additional_defaults );
			}

			return $params;
		}

		/**
		 * Parses data for a parameter.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param array $data                {
		 *     Associative array of parameter data.
		 *
		 *     @type bool   $required    Whether the parameter is required. Default false.
		 *     @type string $description Parameter description. Default empty string.
		 *     @type string $type        Parameter type. Must be one out of 'string', 'integer', 'float', 'number',
		 *                               'boolean', 'array' or 'object'. Default 'string'.
		 *     @type mixed  $default     Default parameter value. Default null.
		 *     @type string $location    Where in a request the parameter is located. Default empty string.
		 *     @type array  $enum        Whitelist of values for the parameter.
		 *     @type array  $items       If $type is 'array', this is the parameter definition for each element that
		 *                               is allowed in the array. It must be an associative array similar like this one.
		 *                               Default empty array.
		 *     @type array $properties   If $type is 'object', this is the associative array of properties the object may
		 *                               have. Each value must again be an associative array similar like this one.
		 *                               Default empty array.
		 * }
		 * @param array $additional_defaults Optional. Additional defaults to use. Passing defaults here also ensures
		 *                                   those are whitelisted as parameter data. Default empty array.
		 * @return array Parsed parameter data.
		 *
		 * @throws Invalid_Request_Parameter_Exception Thrown when the parameter type is invalid.
		 */
		public static function parse_param_data( array $data, array $additional_defaults = array() ) {
			$data = static::parse_args( $data, array_merge( array(
				'required'    => false,
				'description' => '',
				'type'        => 'string',
				'default'     => null,
				'location'    => '',
				'enum'        => array(),
				'items'       => array(),
				'properties'  => array(),
			), $additional_defaults ), true );

			if ( ! in_array( $data['type'], self::get_param_types(), true ) ) {
				throw new Invalid_Request_Parameter_Exception( sprintf( 'The type %s is not a valid parameter type.', $data['type'] ) );
			}

			if ( 'array' === $data['type'] && ! empty( $data['items'] ) ) {
				$data['items'] = self::parse_param_data( $data['items'] );
			} else {
				$data['items'] = array();
			}

			if ( 'object' === $data['type'] && ! empty( $data['properties'] ) ) {
				$data['properties'] = self::parse_params_data( $data['properties'] );
			} else {
				$data['properties'] = array();
			}

			return $data;
		}

		/**
		 * Gets the available types a parameter can have.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @return array Array of types.
		 */
		public static function get_param_types() {
			return array( 'string', 'integer', 'float', 'number', 'boolean', 'array', 'object' );
		}
	}

}
