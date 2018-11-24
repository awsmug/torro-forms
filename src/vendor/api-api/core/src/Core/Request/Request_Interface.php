<?php
/**
 * API-API Request interface
 *
 * @package APIAPI\Core\Request
 * @since 1.0.0
 */

namespace APIAPI\Core\Request;

if ( ! interface_exists( 'APIAPI\Core\Request\Request_Interface' ) ) {

	/**
	 * Request interface for the API-API.
	 *
	 * Represents an API request.
	 *
	 * @since 1.0.0
	 */
	interface Request_Interface {
		/**
		 * Returns the full URI this request should be sent to.
		 *
		 * @since 1.0.0
		 *
		 * @return string The full request URI.
		 */
		public function get_uri();

		/**
		 * Returns the method for this request.
		 *
		 * @since 1.0.0
		 *
		 * @return string The method.
		 */
		public function get_method();

		/**
		 * Sets a header.
		 *
		 * @since 1.0.0
		 *
		 * @param string $header Header name.
		 * @param string $value  Header value.
		 * @param bool   $add    Optional. Whether to add the value instead of replacing it.
		 *                       Default false.
		 */
		public function set_header( $header, $value, $add = false );

		/**
		 * Gets a header.
		 *
		 * @since 1.0.0
		 *
		 * @param string $header   Header name.
		 * @param bool   $as_array Optional. Whether to return the value as array. Default false.
		 * @return string|array|null Header value as string or array depending on $as_array, or
		 *                           null if not set.
		 */
		public function get_header( $header, $as_array = false );

		/**
		 * Gets all headers.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $as_array Optional. Whether to return the individual values as array.
		 *                       Default false.
		 * @return array Array of headers as `$header_name => $header_values` pairs.
		 */
		public function get_headers( $as_array = false );

		/**
		 * Sets a parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param Parameter name.
		 * @param mixed  $value Parameter value.
		 */
		public function set_param( $param, $value );

		/**
		 * Sets a sub parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $param_path,... Parameter names up to the parameter that should be set. The last parameter
		 *                              passed should be the value to set, or null to unset it.
		 */
		public function set_subparam( ...$param_path );

		/**
		 * Sets multiple parameters.
		 *
		 * @since 1.0.0
		 *
		 * @param array $params Array of `$param => $value` pairs.
		 */
		public function set_params( array $params );

		/**
		 * Gets a parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param Parameter name.
		 * @return mixed Parameter value, or null if unset.
		 */
		public function get_param( $param );

		/**
		 * Gets a sub parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $param_path,... Parameter names up to the parameter to retrieve its value.
		 * @return mixed Parameter value, or null if unset.
		 */
		public function get_subparam( ...$param_path );

		/**
		 * Gets all parameters.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of parameters as `$param_name => $param_value` pairs.
		 */
		public function get_params();
	}

}
