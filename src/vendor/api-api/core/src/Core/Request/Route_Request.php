<?php
/**
 * API-API class for a scoped external request
 *
 * @package APIAPI\Core\Request
 * @since 1.0.0
 */

namespace APIAPI\Core\Request;

use APIAPI\Core\Exception\Invalid_Argument_Exception;
use APIAPI\Core\Exception\Invalid_Request_Parameter_Exception;
use APIAPI\Core\Structures\Route;

if ( ! class_exists( 'APIAPI\Core\Request\Route_Request' ) ) {

	/**
	 * Request class for the API-API.
	 *
	 * Represents a external API request, scoped for an API-API instance.
	 *
	 * @since 1.0.0
	 */
	class Route_Request extends Request {
		/**
		 * The route object for this request.
		 *
		 * @since 1.0.0
		 * @var Route
		 */
		protected $route;

		/**
		 * Route URI for this request.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $route_uri = '';

		/**
		 * Authenticator name.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $authenticator = '';

		/**
		 * Authentication data.
		 *
		 * Only needed if $authenticator is used.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $authentication_data = array();

		/**
		 * URI parameters which are part of the base URI.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $base_params = array();

		/**
		 * URI parameters which are part of the route URI path.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $uri_params = array();

		/**
		 * Query parameters. Only used if explicitly declared when the method is
		 * different from 'GET'.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $query_params = array();

		/**
		 * Custom request parameters.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $custom_params = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $base_uri            Base URI for the request.
		 * @param string $method              Either 'GET', 'POST', 'PUT', 'PATCH' or 'DELETE'.
		 * @param Route  $route               Route object for the request.
		 * @param string $route_uri           Route URI for the request.
		 * @param string $authenticator       Optional. Authenticator name. Default empty string.
		 * @param array  $authentication_data Optional. Authentication data to pass to the authenticator.
		 *                                    Default empty array.
		 */
		public function __construct( $base_uri, $method, Route $route, $route_uri, $authenticator = '', array $authentication_data = array() ) {
			parent::__construct( $base_uri, $method );

			$this->route               = $route;
			$this->route_uri           = $route_uri;
			$this->authenticator       = $authenticator;
			$this->authentication_data = $authentication_data;
		}

		/**
		 * Returns the full URI this request should be sent to.
		 *
		 * @since 1.0.0
		 *
		 * @return string The full request URI.
		 */
		public function get_uri() {
			$base_uri = parent::get_uri();
			if ( '/' !== substr( $base_uri, -1 ) ) {
				$base_uri .= '/';
			}

			$search  = array();
			$replace = array();
			foreach ( $this->base_params as $param => $value ) {
				$search[]  = '{' . $param . '}';
				$replace[] = $value;
			}

			if ( ! empty( $search ) ) {
				$base_uri = str_replace( $search, $replace, $base_uri );
			}

			$route_uri = $this->route_uri;

			foreach ( $this->uri_params as $param => $value ) {
				$route_uri = preg_replace( '@\/\(\?P\<' . $param . '\>\[(.+)\]\+\)@U', '/' . $value, $route_uri );
			}

			if ( ! empty( $this->query_params ) ) {
				$query_args = array();
				foreach ( $this->query_params as $param => $value ) {
					$query_args[] = $param . '=' . rawurlencode( $value );
				}

				if ( preg_match( '/\?([A-Za-z0-9_\-]+)=/', $route_uri ) ) {
					$route_uri .= '&' . implode( '&', $query_args );
				} else {
					$route_uri .= '?' . implode( '&', $query_args );
				}
			}

			return $base_uri . ltrim( $route_uri, '/' );
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
			$params = $this->route->get_base_uri_params( $this->uri );
			if ( isset( $params[ $param ] ) ) {
				$this->set_base_param( $param, $value, $params[ $param ] );
				return;
			}

			$params = $this->route->get_method_params( $this->method );

			if ( ! isset( $params[ $param ] ) ) {
				$this->set_custom_param( $param, $value );
			} elseif ( isset( $params[ $param ]['primary'] ) ) {
				$this->set_uri_param( $param, $value, $params[ $param ] );
			} elseif ( Method::GET !== $this->method && 'query' === $params[ $param ]['location'] ) {
				$this->set_query_param( $param, $value, $params[ $param ] );
			} else {
				$this->set_regular_param( $param, $value, $params[ $param ] );
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
			$params = $this->route->get_base_uri_params( $this->uri );
			if ( isset( $params[ $param ] ) ) {
				return $this->get_base_param( $param, $params[ $param ] );
			}

			$params = $this->route->get_method_params( $this->method );

			if ( ! isset( $params[ $param ] ) ) {
				return $this->get_custom_param( $param );
			} elseif ( isset( $params[ $param ]['primary'] ) ) {
				return $this->get_uri_param( $param, $params[ $param ] );
			} elseif ( Method::GET !== $this->method && 'query' === $params[ $param ]['location'] ) {
				return $this->get_query_param( $param, $params[ $param ] );
			} else {
				return $this->get_regular_param( $param, $params[ $param ] );
			}
		}

		/**
		 * Sets a sub parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $param_path,... Parameter names up to the parameter that should be set. The last parameter
		 *                              passed should be the value to set, or null to unset it.
		 *
		 * @throws Invalid_Argument_Exception Thrown when the function parameters are invalid.
		 */
		public function set_subparam( ...$param_path ) {
			if ( count( $param_path ) < 3 ) {
				throw new Invalid_Argument_Exception( sprintf( '%s expects at least two parameters and a value to set.', __METHOD__ ) );
			}

			$param = $param_path[0];

			$params = $this->route->get_base_uri_params( $this->uri );
			if ( isset( $params[ $param ] ) ) {
				$param_path[] = $params[ $param ];
				$this->set_base_subparam( ...$param_path );
				return;
			}

			$params = $this->route->get_method_params( $this->method );

			if ( ! isset( $params[ $param ] ) ) {
				$this->set_custom_subparam( ...$param_path );
			} else {
				$param_path[] = $params[ $param ];

				if ( isset( $params[ $param ]['primary'] ) ) {
					$this->set_uri_subparam( ...$param_path );
				} elseif ( Method::GET !== $this->method && 'query' === $params[ $param ]['location'] ) {
					$this->set_query_subparam( ...$param_path );
				} else {
					$this->set_regular_subparam( ...$param_path );
				}
			}
		}

		/**
		 * Gets a sub parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $param_path,... Parameter names up to the parameter to retrieve its value.
		 * @return mixed Parameter value, or null if unset.
		 *
		 * @throws Invalid_Argument_Exception Thrown when the function parameters are invalid.
		 */
		public function get_subparam( ...$param_path ) {
			if ( count( $param_path ) < 2 ) {
				throw new Invalid_Argument_Exception( sprintf( '%s expects at least two parameters.', __METHOD__ ) );
			}

			$param = $param_path[0];

			$params = $this->route->get_base_uri_params( $this->uri );
			if ( isset( $params[ $param ] ) ) {
				$param_path[] = $params[ $param ];
				return $this->get_base_subparam( ...$param_path );
			}

			$params = $this->route->get_method_params( $this->method );

			if ( ! isset( $params[ $param ] ) ) {
				return $this->get_custom_param( $param );
			} else {
				$param_path[] = $params[ $param ];

				if ( isset( $params[ $param ]['primary'] ) ) {
					return $this->get_uri_subparam( $param, $params[ $param ] );
				} elseif ( Method::GET !== $this->method && 'query' === $params[ $param ]['location'] ) {
					return $this->get_query_subparam( $param, $params[ $param ] );
				} else {
					return $this->get_regular_subparam( $param, $params[ $param ] );
				}
			}
		}

		/**
		 * Gets all parameters.
		 *
		 * URI and query parameters are not included as they are part of the URI.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of parameters as `$param_name => $param_value` pairs.
		 */
		public function get_params() {
			$all_params = array();

			$params = $this->route->get_method_params( $this->method );
			foreach ( $params as $param => $param_info ) {
				if ( isset( $param_info['primary'] ) ) {
					continue;
				}

				$value = $this->get_regular_param( $param, $param_info );
				if ( null === $value ) {
					continue;
				}

				$all_params[ $param ] = $value;
			}

			if ( $this->route->method_supports_custom_params( $this->method ) ) {
				$all_params = array_merge( $all_params, $this->custom_params );
			}

			return $all_params;
		}

		/**
		 * Checks whether the request is valid.
		 *
		 * For it to be valid, all required parameters must be filled.
		 *
		 * @since 1.0.0
		 *
		 * @return bool|array True if the request is valid, array of missing parameters otherwise.
		 */
		public function is_valid() {
			$missing_params = array();

			$params = array_merge( $this->route->get_base_uri_params( $this->uri ), $this->route->get_method_params( $this->method ) );
			foreach ( $params as $param => $param_info ) {
				if ( empty( $param_info['required'] ) || ! empty( $param_info['internal'] ) ) {
					continue;
				}

				if ( null !== $this->get_param( $param ) ) {
					continue;
				}

				$missing_params[] = $param;
			}

			if ( ! empty( $missing_params ) ) {
				return $missing_params;
			}

			return true;
		}

		/**
		 * Checks whether the data for this request should be sent as JSON.
		 *
		 * @since 1.0.0
		 *
		 * @return bool True if JSON should be used, otherwise false.
		 */
		public function should_use_json() {
			return $this->route->method_uses_json_request( $this->method );
		}

		/**
		 * Returns the authenticator name.
		 *
		 * @since 1.0.0
		 *
		 * @return string Authenticator name, or empty string if authentication is not required.
		 */
		public function get_authenticator() {
			if ( ! $this->route->method_needs_authentication( $this->method ) ) {
				return '';
			}

			return $this->authenticator;
		}

		/**
		 * Returns the authentication data.
		 *
		 * @since 1.0.0
		 *
		 * @return array Authentication data, or empty array if authentication is not required.
		 */
		public function get_authentication_data() {
			if ( ! $this->get_authenticator() ) {
				return array();
			}

			return $this->authentication_data;
		}

		/**
		 * Returns the route object.
		 *
		 * @since 1.0.0
		 *
		 * @return \APIAPI\Core\Structures\Route Route object.
		 */
		public function get_route_object() {
			return $this->route;
		}

		/**
		 * Sets a regular parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param      Parameter name.
		 * @param mixed  $value      Parameter value.
		 * @param array  $param_info Parameter info.
		 */
		protected function set_regular_param( $param, $value, array $param_info ) {
			$value = $this->parse_param_value( $value, $param_info );

			$this->params[ $param ] = $value;

			$this->maybe_set_default_content_type();
		}

		/**
		 * Gets a regular parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param      Parameter name.
		 * @param array  $param_info Parameter info.
		 * @return mixed Parameter value, or null if unset.
		 */
		protected function get_regular_param( $param, array $param_info ) {
			if ( isset( $this->params[ $param ] ) ) {
				return $this->params[ $param ];
			}

			return $param_info['default'];
		}

		/**
		 * Sets a base URI parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param      Parameter name.
		 * @param mixed  $value      Parameter value.
		 * @param array  $param_info Parameter info.
		 */
		protected function set_base_param( $param, $value, array $param_info ) {
			$value = $this->parse_param_value( $value, $param_info );

			$this->base_params[ $param ] = $value;
		}

		/**
		 * Gets a base URI parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param      Parameter name.
		 * @param array  $param_info Parameter info.
		 * @return mixed Parameter value, or null if unset.
		 */
		protected function get_base_param( $param, array $param_info ) {
			if ( isset( $this->base_params[ $param ] ) ) {
				return $this->base_params[ $param ];
			}

			return $param_info['default'];
		}

		/**
		 * Sets a URI parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param      Parameter name.
		 * @param mixed  $value      Parameter value.
		 * @param array  $param_info Parameter info.
		 */
		protected function set_uri_param( $param, $value, array $param_info ) {
			$value = $this->parse_param_value( $value, $param_info );

			$this->uri_params[ $param ] = $value;
		}

		/**
		 * Gets a URI parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param      Parameter name.
		 * @param array  $param_info Parameter info.
		 * @return mixed Parameter value, or null if unset.
		 */
		protected function get_uri_param( $param, array $param_info ) {
			if ( isset( $this->uri_params[ $param ] ) ) {
				return $this->uri_params[ $param ];
			}

			return $param_info['default'];
		}

		/**
		 * Sets a query parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param      Parameter name.
		 * @param mixed  $value      Parameter value.
		 * @param array  $param_info Parameter info.
		 */
		protected function set_query_param( $param, $value, array $param_info ) {
			$value = $this->parse_param_value( $value, $param_info );

			$this->query_params[ $param ] = $value;
		}

		/**
		 * Gets a query parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param      Parameter name.
		 * @param array  $param_info Parameter info.
		 * @return mixed Parameter value, or null if unset.
		 */
		protected function get_query_param( $param, array $param_info ) {
			if ( isset( $this->query_params[ $param ] ) ) {
				return $this->query_params[ $param ];
			}

			return $param_info['default'];
		}

		/**
		 * Sets a custom parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param Parameter name.
		 * @param mixed  $value Parameter value.
		 *
		 * @throws Invalid_Request_Parameter_Exception Thrown when custom parameters are not supported.
		 */
		protected function set_custom_param( $param, $value ) {
			if ( ! $this->route->method_supports_custom_params( $this->method ) ) {
				throw new Invalid_Request_Parameter_Exception( sprintf( 'Cannot set unsupported parameter %1$s for route %2$s with method %3$s.', $param, $this->route->get_uri(), $this->method ) );
			}

			$this->custom_params[ $param ] = $value;

			$this->maybe_set_default_content_type();
		}

		/**
		 * Gets a custom parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param Parameter name.
		 * @return mixed Parameter value, or null if unset.
		 *
		 * @throws Invalid_Request_Parameter_Exception Thrown when custom parameters are not supported.
		 */
		protected function get_custom_param( $param ) {
			if ( ! $this->route->method_supports_custom_params( $this->method ) ) {
				throw new Invalid_Request_Parameter_Exception( sprintf( 'Cannot get unsupported parameter %1$s for route %2$s with method %3$s.', $param, $this->route->get_uri(), $this->method ) );
			}

			if ( isset( $this->custom_params[ $param ] ) ) {
				return $this->custom_params[ $param ];
			}

			return null;
		}

		/**
		 * Sets a regular sub parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $param_path,... Parameter path, value to set and param data.
		 */
		protected function set_regular_subparam( ...$param_path ) {
			$param_info = array_pop( $param_path );
			$value      = array_pop( $param_path );

			$param_info = $this->get_subparam_info( $param_path, $param_info );

			$value = $this->parse_param_value( $value, $param_info );

			$this->set_subparam_value( $this->params, $param_path, $value );

			$this->maybe_set_default_content_type();
		}



		/**
		 * Gets a regular sub parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $param_path,... Parameter path and param data.
		 * @return mixed Parameter value, or null if unset.
		 */
		protected function get_regular_subparam( ...$param_path ) {
			$param_info = array_pop( $param_path );

			$value = $this->get_subparam_value( $this->params, $param_path );
			if ( null === $value ) {
				$param_info = $this->get_subparam_info( $param_path, $param_info );

				return $param_info['default'];
			}

			return $value;
		}

		/**
		 * Sets a base URI sub parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $param_path,... Parameter path, value to set and param data.
		 *
		 * @throws Invalid_Request_Parameter_Exception Thrown to indicate nesting base URI parameters is not possible.
		 */
		protected function set_base_subparam( ...$param_path ) {
			$param_info = array_pop( $param_path );
			$value      = array_pop( $param_path );

			throw new Invalid_Request_Parameter_Exception( sprintf( 'Cannot set sub parameter %1$s for route %2$s with method %3$s since base URI parameters do not support nesting.', array_pop( $param_path ), $this->route->get_uri(), $this->method ) );
		}

		/**
		 * Gets a base URI sub parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $param_path,... Parameter path and param data.
		 * @return mixed Parameter value, or null if unset.
		 *
		 * @throws Invalid_Request_Parameter_Exception Thrown to indicate nesting base URI parameters is not possible.
		 */
		protected function get_base_subparam( ...$param_path ) {
			$param_info = array_pop( $param_path );

			throw new Invalid_Request_Parameter_Exception( sprintf( 'Cannot get sub parameter %1$s for route %2$s with method %3$s since base URI parameters do not support nesting.', array_pop( $param_path ), $this->route->get_uri(), $this->method ) );
		}

		/**
		 * Sets a URI sub parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $param_path,... Parameter path, value to set and param data.
		 *
		 * @throws Invalid_Request_Parameter_Exception Thrown to indicate nesting URI parameters is not possible.
		 */
		protected function set_uri_subparam( ...$param_path ) {
			$param_info = array_pop( $param_path );
			$value      = array_pop( $param_path );

			throw new Invalid_Request_Parameter_Exception( sprintf( 'Cannot set sub parameter %1$s for route %2$s with method %3$s since URI parameters do not support nesting.', array_pop( $param_path ), $this->route->get_uri(), $this->method ) );
		}

		/**
		 * Gets a URI sub parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $param_path,... Parameter path and param data.
		 * @return mixed Parameter value, or null if unset.
		 *
		 * @throws Invalid_Request_Parameter_Exception Thrown to indicate nesting URI parameters is not possible.
		 */
		protected function get_uri_subparam( ...$param_path ) {
			$param_info = array_pop( $param_path );

			throw new Invalid_Request_Parameter_Exception( sprintf( 'Cannot get sub parameter %1$s for route %2$s with method %3$s since URI parameters do not support nesting.', array_pop( $param_path ), $this->route->get_uri(), $this->method ) );
		}

		/**
		 * Sets a query sub parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $param_path,... Parameter path, value to set and param data.
		 */
		protected function set_query_subparam( ...$param_path ) {
			$param_info = array_pop( $param_path );
			$value      = array_pop( $param_path );

			$param_info = $this->get_subparam_info( $param_path, $param_info );

			$value = $this->parse_param_value( $value, $param_info );

			$this->set_subparam_value( $this->query_params, $param_path, $value );
		}

		/**
		 * Gets a query sub parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $param_path,... Parameter path and param data.
		 * @return mixed Parameter value, or null if unset.
		 */
		protected function get_query_subparam( ...$param_path ) {
			$param_info = array_pop( $param_path );

			$value = $this->get_subparam_value( $this->query_params, $param_path );
			if ( null === $value ) {
				$param_info = $this->get_subparam_info( $param_path, $param_info );

				return $param_info['default'];
			}

			return $value;
		}

		/**
		 * Sets a custom sub parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $param_path,... Parameter path and value to set.
		 *
		 * @throws Invalid_Request_Parameter_Exception Thrown when custom parameters are not supported.
		 */
		protected function set_custom_subparam( ...$param_path ) {
			$value = array_pop( $param_path );

			if ( ! $this->route->method_supports_custom_params( $this->method ) ) {
				throw new Invalid_Request_Parameter_Exception( sprintf( 'Cannot set unsupported sub parameter %1$s for route %2$s with method %3$s.', array_pop( $param_path ), $this->route->get_uri(), $this->method ) );
			}

			$this->set_subparam_value( $this->custom_params, $param_path, $value );
		}

		/**
		 * Gets a custom sub parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $param_path,... Parameter path.
		 * @return mixed Parameter value, or null if unset.
		 *
		 * @throws Invalid_Request_Parameter_Exception Thrown when custom parameters are not supported.
		 */
		protected function get_custom_subparam( ...$param_path ) {
			if ( ! $this->route->method_supports_custom_params( $this->method ) ) {
				throw new Invalid_Request_Parameter_Exception( sprintf( 'Cannot get unsupported sub parameter %1$s for route %2$s with method %3$s.', array_pop( $param_path ), $this->route->get_uri(), $this->method ) );
			}

			return $this->get_subparam_value( $this->custom_params, $param_path );
		}

		/**
		 * Internal utility function to get nested sub parameter info.
		 *
		 * @since 1.0.0
		 *
		 * @param array $param_path Parameter path.
		 * @param array $param_info Param data for the first param.
		 * @return array Param data for the last param in the path.
		 *
		 * @throws Invalid_Request_Parameter_Exception Thrown if a subparam is not valid sub property.
		 */
		protected function get_subparam_info( array $param_path, array $param_info ) {
			$first_param = array_shift( $param_path );

			foreach ( $param_path as $param ) {
				if ( ! isset( $param_info['properties'][ $param ] ) ) {
					throw new Invalid_Request_Parameter_Exception( sprintf( 'The subparam %1$s is not a valid sub property of the param %2$s.', $param, $first_param ) );
				}

				$param_info = $param_info['properties'][ $param ];
			}

			return $param_info;
		}

		/**
		 * Parses a parameter value.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $value      The input value.
		 * @param array $param_info Parameter info.
		 * @return mixed The parsed value.
		 *
		 * @throws Invalid_Request_Parameter_Exception Thrown when the value does not validate against the parameter data schema.
		 */
		protected function parse_param_value( $value, array $param_info ) {
			switch ( $param_info['type'] ) {
				case 'boolean':
					$value = (bool) $value;
					break;
				case 'float':
				case 'number':
					$value = (float) $value;
					break;
				case 'integer':
					$value = (int) $value;

					if ( isset( $param_info['minimum'] ) && $value < $param_info['minimum'] ) {
						throw new Invalid_Request_Parameter_Exception( sprintf( 'The value %1$s is smaller than the minimum allowed value of %2$s.', $value, $param_info['minimum'] ) );
					}

					if ( isset( $param_info['maximum'] ) && $value > $param_info['maximum'] ) {
						throw new Invalid_Request_Parameter_Exception( sprintf( 'The value %1$s is greater than the maximum allowed value of %2$s.', $value, $param_info['maximum'] ) );
					}

					break;
				case 'string':
					$value = (string) $value;

					if ( ! empty( $param_info['enum'] ) && ! in_array( $value, $param_info['enum'], true ) ) {
						throw new Invalid_Request_Parameter_Exception( sprintf( 'The value %1$s is not within the allowed values of %2$s.', $value, implode( ', ', $param_info['enum'] ) ) );
					}

					break;
				case 'array':
					$value = (array) $value;

					if ( ! empty( $param_info['items'] ) ) {
						$values = $value;
						$value  = array();

						foreach ( $values as $val ) {
							$value[] = $this->parse_param_value( $value, $param_info['items'] );
						}
					}
					break;
				case 'object':
					$value = (array) $value;

					if ( ! empty( $param_info['properties'] ) ) {
						$values = $value;
						$value  = array();

						foreach ( $values as $key => $val ) {
							if ( ! isset( $param_info['properties'][ $key ] ) ) {
								throw new Invalid_Request_Parameter_Exception( sprintf( 'The object property %s is not supported.', $key ) );
							}

							$value[ $key ] = $this->parse_param_value( $val, $param_info['properties'][ $key ] );
						}
					}
					break;
			}

			return $value;
		}

		/**
		 * Sets the default content type if none has been set yet.
		 *
		 * @since 1.0.0
		 */
		protected function maybe_set_default_content_type() {
			if ( Method::GET !== $this->method && null === $this->get_header( 'content-type' ) ) {
				if ( $this->should_use_json() ) {
					$this->set_header( 'content-type', 'application/json' );
				} else {
					$this->set_header( 'content-type', 'application/x-www-form-urlencoded' );
				}
			}
		}
	}

}
