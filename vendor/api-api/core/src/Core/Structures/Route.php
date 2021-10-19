<?php
/**
 * API-API Route class
 *
 * @package APIAPI\Core\Structures
 * @since 1.0.0
 */

namespace APIAPI\Core\Structures;

use APIAPI\Core\Util;
use APIAPI\Core\Request\Route_Request;
use APIAPI\Core\Request\Route_Response;
use APIAPI\Core\Request\Method;
use APIAPI\Core\Exception\Invalid_Request_Method_Exception;

if ( ! class_exists( 'APIAPI\Core\Structures\Route' ) ) {

	/**
	 * Route class for the API-API.
	 *
	 * Represents a specific route in an API structure.
	 *
	 * @since 1.0.0
	 */
	class Route {
		/**
		 * The route's base URI. May contain regular expressions.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		private $uri;

		/**
		 * The API structure this route belongs to.
		 *
		 * @since 1.0.0
		 * @var Structure
		 */
		private $structure;

		/**
		 * Array of primary parameters.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		private $primary_params = array();

		/**
		 * Array of supported methods and their data.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		private $data = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string    $uri       The route's base URI.
		 * @param array     $data      {
		 *     Array of route data.
		 *
		 *     @type array $primary_params Array of primary parameters as `$param_name => $param_data`
		 *                                 pairs. Each $param_data array can have keys 'required',
		 *                                 'description', 'type', 'default', 'location', 'enum' and
		 *                                 'items'.
		 *     @type array $methods        Array of supported methods as `$method_name => $method_data`
		 *                                 pairs. Each $method_data array can have keys 'description',
		 *                                 'params' (works similar like $primary_params),
		 *                                 'supports_custom_params', 'request_data_type',
		 *                                 'needs_authentication', 'request_class' and 'response_class'.
		 * }
		 * @param Structure $structure The parent API structure.
		 */
		public function __construct( $uri, array $data, Structure $structure ) {
			$this->uri = $uri;

			$this->data = $this->parse_data( $data );

			$this->structure = $structure;

			$this->set_primary_params();
		}

		/**
		 * Returns the URI for this route.
		 *
		 * @since 1.0.0
		 *
		 * @return string The base URI.
		 */
		public function get_uri() {
			return $this->uri;
		}

		/**
		 * Returns the description for what a specific method does.
		 *
		 * @since 1.0.0
		 *
		 * @param string $method Either 'GET', 'POST', 'PUT', 'PATCH' or 'DELETE'.
		 * @return string Description for what the method does at this route, or empty
		 *                string if method not supported.
		 */
		public function get_method_description( $method ) {
			if ( ! $this->is_method_supported( $method ) ) {
				return '';
			}

			return $this->data['methods'][ $method ]['description'];
		}

		/**
		 * Returns the available base parameter information for a specific base URI.
		 *
		 * @since 1.0.0
		 *
		 * @param string $base_uri Base URI.
		 * @return array Array of URI parameters.
		 */
		public function get_base_uri_params( $base_uri ) {
			return $this->structure->get_base_uri_params_by_uri( $base_uri );
		}

		/**
		 * Returns the available parameter information for a specific method.
		 *
		 * @since 1.0.0
		 *
		 * @param string $method      Either 'GET', 'POST', 'PUT', 'PATCH' or 'DELETE'.
		 * @param bool   $include_all Optional. Whether to also include primary and global parameters.
		 *                            Default true.
		 * @return array Array of method parameters, or empty array if method not supported.
		 */
		public function get_method_params( $method, $include_all = true ) {
			if ( ! $this->is_method_supported( $method ) ) {
				return array();
			}

			if ( ! $include_all ) {
				return $this->data['methods'][ $method ]['params'];
			}

			return array_merge( $this->primary_params, $this->data['methods'][ $method ]['params'], $this->structure->get_global_params() );
		}

		/**
		 * Returns the available primary parameter information.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of primary parameters.
		 */
		public function get_primary_params() {
			return $this->primary_params;
		}

		/**
		 * Checks whether a specific method supports custom parameters.
		 *
		 * @since 1.0.0
		 *
		 * @param string $method Either 'GET', 'POST', 'PUT', 'PATCH' or 'DELETE'.
		 * @return bool Whether custom parameters are supported, or false if method not supported.
		 */
		public function method_supports_custom_params( $method ) {
			if ( ! $this->is_method_supported( $method ) ) {
				return false;
			}

			return $this->data['methods'][ $method ]['supports_custom_params'];
		}

		/**
		 * Checks whether a specific method requires the request data as JSON.
		 *
		 * @since 1.0.0
		 *
		 * @param string $method Either 'GET', 'POST', 'PUT', 'PATCH' or 'DELETE'.
		 * @return bool Whether request data is used as JSON, or false if method not supported.
		 */
		public function method_uses_json_request( $method ) {
			if ( ! $this->is_method_supported( $method ) ) {
				return false;
			}

			return 'json' === $this->data['methods'][ $method ]['request_data_type'];
		}

		/**
		 * Checks whether a specific method needs authentication.
		 *
		 * @since 1.0.0
		 *
		 * @param string $method Either 'GET', 'POST', 'PUT', 'PATCH' or 'DELETE'.
		 * @return bool Whether authentication is needed, or false if method not supported.
		 */
		public function method_needs_authentication( $method ) {
			if ( ! $this->is_method_supported( $method ) ) {
				return false;
			}

			return $this->data['methods'][ $method ]['needs_authentication'];
		}

		/**
		 * Checks whether a specific method is supported.
		 *
		 * @since 1.0.0
		 *
		 * @param string $method Either 'GET', 'POST', 'PUT', 'PATCH' or 'DELETE'.
		 * @return bool True if the method is supported, otherwise false.
		 */
		public function is_method_supported( $method ) {
			return isset( $this->data['methods'][ $method ] );
		}

		/**
		 * Returns all supported methods.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of methods.
		 */
		public function get_supported_methods() {
			return array_keys( $this->data['methods'] );
		}

		/**
		 * Creates a request object based on parameters.
		 *
		 * @since 1.0.0
		 *
		 * @param string $route_uri           Route URI for the request.
		 * @param string $method              Optional. Either 'GET', 'POST', 'PUT', 'PATCH' or 'DELETE'.
		 *                                    Default 'GET'.
		 * @param string $mode                Optional. API mode to use for the request. Available values
		 *                                    depend on the API structure. Default empty string.
		 * @param string $authenticator       Optional. Authenticator name. Default empty string.
		 * @param array  $authentication_data Optional. Authentication data to pass to the authenticator.
		 *                                    Default empty array.
		 * @return Route_Request Request object.
		 *
		 * @throws Invalid_Request_Method_Exception Thrown when the request method is not supported by the route.
		 */
		public function create_request_object( $route_uri, $method = Method::GET, $mode = '', $authenticator = '', array $authentication_data = array() ) {
			if ( ! $this->is_method_supported( $method ) ) {
				throw new Invalid_Request_Method_Exception( sprintf( 'The method %1$s is not supported in the route %2$s.', $method, $this->get_uri() ) );
			}

			$class_name = $this->data['methods'][ $method ]['request_class'];

			$authentication_data = Util::parse_args( $authentication_data, $this->structure->get_authentication_data_defaults( $mode ) );

			return new $class_name( $this->structure->get_base_uri( $mode ), $method, $this, $route_uri, $authenticator, $authentication_data );
		}

		/**
		 * Creates a response object based on parameters.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $response_data Response array containing keys 'headers', 'body', and 'response'.
		 *                              Not necessarily all of these are included though.
		 * @param string $method        Optional. Either 'GET', 'POST', 'PUT', 'PATCH' or 'DELETE'.
		 *                              Default 'GET'.
		 * @return Route_Response Response object.
		 *
		 * @throws Invalid_Request_Method_Exception Thrown when the request method is not supported by the route.
		 */
		public function create_response_object( array $response_data, $method = Method::GET ) {
			if ( ! $this->is_method_supported( $method ) ) {
				throw new Invalid_Request_Method_Exception( sprintf( 'The method %1$s is not supported in the route %2$s.', $method, $this->get_uri() ) );
			}

			$class_name = $this->data['methods'][ $method ]['response_class'];

			return $this->structure->process_response( new $class_name( $response_data, $method, $this ) );
		}

		/**
		 * Sets the primary parameters depending on the route's base URI.
		 *
		 * Primary parameters are regular expression parts of the URI.
		 *
		 * @since 1.0.0
		 */
		private function set_primary_params() {
			preg_match_all( '@(\/|^)\(\?P\<([A-Za-z_]+)\>\[(.+)\]\+\)@U', $this->uri, $matches );

			$this->primary_params = array();
			for ( $i = 0; $i < count( $matches[0] ); $i++ ) {
				$type = '\d' === $matches[3][ $i ] ? 'integer' : 'string';

				$description = '';
				$default     = null;
				if ( isset( $this->data['primary_params'][ $matches[2][ $i ] ] ) ) {
					if ( isset( $this->data['primary_params'][ $matches[2][ $i ] ]['description'] ) ) {
						$description = $this->data['primary_params'][ $matches[2][ $i ] ]['description'];
					}
					if ( isset( $this->data['primary_params'][ $matches[2][ $i ] ]['default'] ) ) {
						$default = $this->data['primary_params'][ $matches[2][ $i ] ]['default'];
					}
					if ( isset( $this->data['primary_params'][ $matches[2][ $i ] ]['type'] ) ) {
						$type = $this->data['primary_params'][ $matches[2][ $i ] ]['type'];
					}
				}

				$this->primary_params[ $matches[2][ $i ] ] = array(
					'required'    => true,
					'description' => $description,
					'type'        => $type,
					'enum'        => array(),
					'default'     => $default,
					'location'    => 'path',
					'primary'     => true,
				);
			}

			unset( $this->data['primary_params'] );
		}

		/**
		 * Parses route data.
		 *
		 * @since 1.0.0
		 *
		 * @param array $data Route data.
		 * @return array Parsed route data.
		 */
		private function parse_data( array $data ) {
			$data = Util::parse_args( $data, array(
				'primary_params' => array(),
				'methods'        => array(),
			) );

			$data['primary_params'] = $this->parse_param_data( $data['primary_params'] );
			$data['methods']        = $this->parse_method_data( $data['methods'] );

			return $data;
		}

		/**
		 * Parses method data.
		 *
		 * @since 1.0.0
		 *
		 * @param array $method_data Method data.
		 * @return array Parsed method data.
		 */
		private function parse_method_data( $method_data ) {
			$method_data = array_intersect_key( $method_data, array_flip( array( Method::GET, Method::POST, Method::PUT, Method::PATCH, Method::DELETE ) ) );

			foreach ( $method_data as $method => &$data ) {
				$data = Util::parse_args( $data, array(
					'description'            => '',
					'params'                 => array(),
					'supports_custom_params' => false,
					'request_data_type'      => 'raw',
					'needs_authentication'   => false,
					'request_class'          => Route_Request::class,
					'response_class'         => Route_Response::class,
				), true );

				$data['params'] = $this->parse_param_data( $data['params'] );
			}

			return $method_data;
		}

		/**
		 * Parses param data.
		 *
		 * @since 1.0.0
		 *
		 * @param array $param_data Param data.
		 * @return array Parsed param data.
		 */
		private function parse_param_data( $param_data ) {
			return Util::parse_params_data( $param_data );
		}
	}

}
