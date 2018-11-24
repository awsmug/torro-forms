<?php
/**
 * API-API Response interface
 *
 * @package APIAPI\Core\Request
 * @since 1.0.0
 */

namespace APIAPI\Core\Request;

if ( ! interface_exists( 'APIAPI\Core\Request\Response_Interface' ) ) {

	/**
	 * Response interface for the API-API.
	 *
	 * Represents an API response.
	 *
	 * @since 1.0.0
	 */
	interface Response_Interface {
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

		/**
		 * Returns the response code.
		 *
		 * @since 1.0.0
		 *
		 * @return int Response code.
		 */
		public function get_response_code();

		/**
		 * Returns the response message.
		 *
		 * @since 1.0.0
		 *
		 * @return string Response message.
		 */
		public function get_response_message();
	}

}
