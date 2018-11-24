<?php
/**
 * API-API class for a general external request
 *
 * @package APIAPI\Core\Request
 * @since 1.0.0
 */

namespace APIAPI\Core\Request;

if ( ! class_exists( 'APIAPI\Core\Request\Request' ) ) {

	/**
	 * Request class for the API-API.
	 *
	 * Represents a general external API request.
	 *
	 * @since 1.0.0
	 */
	class Request implements Request_Interface {
		/**
		 * URI for this request.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $uri = '';

		/**
		 * The method for this request. Either 'GET', 'POST', 'PUT', 'PATCH' or 'DELETE'.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $method = '';

		/**
		 * Request headers.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $headers = array();

		/**
		 * Request parameters.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $params = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $uri    URI for the request.
		 * @param string $method Either 'GET', 'POST', 'PUT', 'PATCH' or 'DELETE'. Default 'GET'.
		 */
		public function __construct( $uri, $method = Method::GET ) {
			$this->uri    = $uri;
			$this->method = $method;
		}

		/**
		 * Returns the full URI this request should be sent to.
		 *
		 * @since 1.0.0
		 *
		 * @return string The full request URI.
		 */
		public function get_uri() {
			return $this->uri;
		}

		/**
		 * Returns the method for this request.
		 *
		 * @since 1.0.0
		 *
		 * @return string The method.
		 */
		public function get_method() {
			return $this->method;
		}

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
		public function set_header( $header, $value, $add = false ) {
			// $header = $this->canonicalize_header_name( $header );

			if ( $add && ! empty( $this->headers[ $header ] ) ) {
				$this->headers[ $header ][] = $value;
			} else {
				$this->headers[ $header ] = (array) $value;
			}
		}

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
		public function get_header( $header, $as_array = false ) {
			// $header = $this->canonicalize_header_name( $header );

			if ( ! isset( $this->headers[ $header ] ) ) {
				return null;
			}

			if ( $as_array ) {
				return $this->headers[ $header ];
			}

			return implode( ',', $this->headers[ $header ] );
		}

		/**
		 * Gets all headers.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $as_array Optional. Whether to return the individual values as array.
		 *                       Default false.
		 * @return array Array of headers as `$header_name => $header_values` pairs.
		 */
		public function get_headers( $as_array = false ) {
			if ( $as_array ) {
				return $this->headers;
			}

			$all_headers = array();

			foreach ( $this->headers as $header_name => $header_values ) {
				$all_headers[ $header_name ] = implode( ',', $header_values );
			}

			return $all_headers;
		}

		/**
		 * Sets a parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param Parameter name.
		 * @param mixed  $value Parameter value.
		 */
		public function set_param( $param, $value ) {
			$this->params[ $param ] = $value;

			$this->maybe_set_default_content_type();
		}

		/**
		 * Sets a sub parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $param_path,... Parameter names up to the parameter that should be set. The last parameter
		 *                              passed should be the value to set, or null to unset it.
		 */
		public function set_subparam( ...$param_path ) {
			$value = array_pop( $param_path );

			$this->set_subparam_value( $this->params, $param_path, $value );
		}

		/**
		 * Sets multiple parameters.
		 *
		 * @since 1.0.0
		 *
		 * @param array $params Array of `$param => $value` pairs.
		 */
		public function set_params( array $params ) {
			foreach ( $params as $param => $value ) {
				$this->set_param( $param, $value );
			}
		}

		/**
		 * Gets a parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param Parameter name.
		 * @return mixed Parameter value, or null if unset.
		 */
		public function get_param( $param ) {
			if ( isset( $this->params[ $param ] ) ) {
				return $this->params[ $param ];
			}

			return null;
		}

		/**
		 * Gets a sub parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $param_path,... Parameter names up to the parameter to retrieve its value.
		 * @return mixed Parameter value, or null if unset.
		 */
		public function get_subparam( ...$param_path ) {
			return $this->get_subparam_value( $this->params, $param_path );
		}

		/**
		 * Gets all parameters.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of parameters as `$param_name => $param_value` pairs.
		 */
		public function get_params() {
			return $this->params;
		}

		/**
		 * Internal utility function to set a nested sub parameter value.
		 *
		 * @since 1.0.0
		 *
		 * @param array $base_array Array where the value should be set in. Passed by reference.
		 * @param array $param_path Parameter path.
		 * @param mixed $value      Value to set.
		 */
		protected function set_subparam_value( &$base_array, $param_path, $value ) {
			$last_param = array_pop( $param_path );

			$location = &$base_array;
			foreach ( $param_path as $param ) {
				if ( ! array_key_exists( $param, $location ) ) {
					$location[ $param ] = array();
				}

				$location = &$location[ $param ];
			}

			$location[ $last_param ] = $value;
		}

		/**
		 * Internal utility function to get a nested sub parameter value.
		 *
		 * @since 1.0.0
		 *
		 * @param array $base_array Array where the value should be retrieved from.
		 * @param array $param_path Parameter path.
		 * @return mixed Retrieved value, or null if unset.
		 */
		protected function get_subparam_value( $base_array, $param_path ) {
			$location = $base_array;
			foreach ( $param_path as $param ) {
				if ( ! array_key_exists( $param, $location ) ) {
					return null;
				}

				$location = $location[ $param ];
			}

			return $location;
		}

		/**
		 * Sets the default content type if none has been set yet.
		 *
		 * @since 1.0.0
		 */
		protected function maybe_set_default_content_type() {
			if ( Method::GET !== $this->method && null === $this->get_header( 'content-type' ) ) {
				$this->set_header( 'content-type', 'application/x-www-form-urlencoded' );
			}
		}

		/**
		 * Canonicalizes the header name.
		 *
		 * This ensures that header names are always case insensitive, plus dashes and
		 * underscores are treated as the same character.
		 *
		 * @since 1.0.0
		 *
		 * @param string $header Header name.
		 * @return string Canonicalized header name.
		 */
		protected function canonicalize_header_name( $header ) {
			return strtolower( $header );
		}
	}

}
