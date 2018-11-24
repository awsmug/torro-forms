<?php
/**
 * API-API Structure class
 *
 * @package APIAPI\Core\Structures
 * @since 1.0.0
 */

namespace APIAPI\Core\Structures;

use APIAPI\Core\Request\API;
use APIAPI\Core\Request\Route_Request;
use APIAPI\Core\Request\Route_Response;
use APIAPI\Core\Request\Method;
use APIAPI\Core\APIAPI;
use APIAPI\Core\Name_Trait;
use APIAPI\Core\Util;
use APIAPI\Core\Exception\Invalid_Route_Exception;

if ( ! class_exists( 'APIAPI\Core\Structures\Structure' ) ) {

	/**
	 * Structure class for the API-API.
	 *
	 * Represents a specific API structure.
	 *
	 * @since 1.0.0
	 */
	abstract class Structure implements Structure_Interface {
		use Name_Trait;

		/**
		 * Key in the main configuration to extract relevant
		 * configuration for this structure.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $config_key = '';

		/**
		 * Title of the API.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $title = '';

		/**
		 * Description of the API.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $description = '';

		/**
		 * Base URI for the API.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $base_uri = '';

		/**
		 * Parameters that are part of the base URI. Some APIs use such.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $base_uri_params = array();

		/**
		 * Advanced URIs for the API, for example a sandbox URI.
		 * Must be an associative array of $mode => $uri pairs.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $advanced_uris = array();

		/**
		 * Parameters that are part of the advanced URIs. Some APIs use such.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $advanced_uri_params = array();

		/**
		 * Route objects as part of this structure.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $routes = array();

		/**
		 * Optional global parameters the API supports.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $global_params = array();

		/**
		 * Name of the authenticator to use for the API.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $authenticator = '';

		/**
		 * Default authentication data to pass to the authenticator.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $authentication_data_defaults = array();

		/**
		 * Default authentication data to pass to the authenticator, for additional
		 * modes. Must be an associative array of $mode => $defaults pairs.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $advanced_authentication_data_defaults = array();

		/**
		 * Whether the class has been setup.
		 *
		 * @since 1.0.0
		 * @var bool
		 */
		protected $setup_loaded = false;

		/**
		 * Container for API-API-specific instances of this API.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		private $api_objects = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Slug of the instance.
		 */
		public function __construct( $name ) {
			$this->set_name( $name );

			// Set some defaults, which can be overridden in the setup() method.
			$this->config_key  = $this->name;
			$this->title       = $this->name;
			$this->description = '';
		}

		/**
		 * Lazily sets up the structure.
		 *
		 * This method is invoked whenever a relevant class method is called.
		 *
		 * @since 1.0.0
		 */
		protected function lazyload_setup() {
			if ( $this->setup_loaded ) {
				return;
			}

			$this->setup_loaded = true;

			$this->setup();

			$this->process_routes();
			$this->process_global_params();
			$this->process_uri_params();
		}

		/**
		 * Returns the general API object for a specific API-API scope.
		 *
		 * The API object will be instantiated if it does not exist yet.
		 *
		 * @since 1.0.0
		 *
		 * @param APIAPI $apiapi The API-API instance to get the API object for.
		 * @return API The API object.
		 */
		public function get_api_object( APIAPI $apiapi ) {
			$this->lazyload_setup();

			$name = $apiapi->get_name();

			if ( ! isset( $this->api_objects[ $name ] ) ) {
				$this->api_objects[ $name ] = new API( $this, $apiapi->config() );
			}

			return $this->api_objects[ $name ];
		}

		/**
		 * Returns a scoped request object for a specific route of this API.
		 *
		 * @since 1.0.0
		 *
		 * @param APIAPI $apiapi    The API-API instance to get the API object for.
		 * @param string $route_uri URI of the route.
		 * @param string $method    Optional. Either 'GET', 'POST', 'PUT', 'PATCH'
		 *                          or 'DELETE'. Default 'GET'.
		 * @return Route_Request Request object for the route.
		 */
		public function get_request_object( APIAPI $apiapi, $route_uri, $method = Method::GET ) {
			return $this->get_api_object( $apiapi )->get_request_object( $route_uri, $method );
		}

		/**
		 * Returns the route object for a specific route.
		 *
		 * @since 1.0.0
		 *
		 * @param string $route_uri URI of the route.
		 * @return Route The route object.
		 *
		 * @throws Invalid_Route_Exception Thrown when the route URI is invalid.
		 */
		public function get_route_object( $route_uri ) {
			$this->lazyload_setup();

			if ( isset( $this->routes[ $route_uri ] ) ) {
				return $this->routes[ $route_uri ];
			}

			foreach ( $this->routes as $route_base_uri => $route ) {
				if ( preg_match( '@^' . $route_base_uri . '$@i', $route_uri ) ) {
					return $route;
				}
			}

			throw new Invalid_Route_Exception( sprintf( 'The API %1$s does not provide a route for %2$s.', $this->name, $route_uri ) );
		}

		/**
		 * Checks whether a specific route exists.
		 *
		 * @since 1.0.0
		 *
		 * @param string $route_uri URI of the route.
		 * @return bool True if the route exists, otherwise false.
		 */
		public function has_route( $route_uri ) {
			try {
				$route = $this->get_route_object( $route_uri );
			} catch ( Exception $e ) {
				return false;
			}

			return ! is_null( $route );
		}

		/**
		 * Returns all available routes.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of route objects.
		 */
		public function get_route_objects() {
			$this->lazyload_setup();

			return $this->routes;
		}

		/**
		 * Returns the available global parameters information.
		 *
		 * @since 1.0.0
		 *
		 * @return array Global parameters as `$param => $param_info` pairs.
		 */
		public function get_global_params() {
			$this->lazyload_setup();

			return $this->global_params;
		}

		/**
		 * Returns the authenticator name for the API.
		 *
		 * @since 1.0.0
		 *
		 * @return string Authenticator name, or empty string if not set.
		 */
		public function get_authenticator() {
			$this->lazyload_setup();

			return $this->authenticator;
		}

		/**
		 * Returns the default data to send to the authenticator.
		 *
		 * @since 1.0.0
		 *
		 * @param string $mode Optional. Mode for which to get the authentication data
		 *                     defaults. Default empty.
		 * @return array Array of default authentication data.
		 */
		public function get_authentication_data_defaults( $mode = '' ) {
			$this->lazyload_setup();

			if ( ! empty( $mode ) && isset( $this->advanced_authentication_data_defaults[ $mode ] ) ) {
				return $this->advanced_authentication_data_defaults[ $mode ];
			}

			return $this->authentication_data_defaults;
		}

		/**
		 * Checks whether the API should use an authenticator.
		 *
		 * @since 1.0.0
		 *
		 * @return bool True if the API should use an authenticator, otherwise false.
		 */
		public function has_authenticator() {
			$this->lazyload_setup();

			return ! empty( $this->authenticator );
		}

		/**
		 * Returns this API's base URI for a specific mode.
		 *
		 * @since 1.0.0
		 *
		 * @param string $mode Optional. Mode for which to get the base URI. Default empty.
		 * @return string Base URI.
		 */
		public function get_base_uri( $mode = '' ) {
			$this->lazyload_setup();

			if ( ! empty( $mode ) && isset( $this->advanced_uris[ $mode ] ) ) {
				return $this->advanced_uris[ $mode ];
			}

			return $this->base_uri;
		}

		/**
		 * Returns required parameters that are part of this API's base URI for a specific mode.
		 *
		 * @since 1.0.0
		 *
		 * @param string $mode Optional. Mode for which to get the base URI parameters. Default empty.
		 * @return array Base URI parameters.
		 */
		public function get_base_uri_params( $mode = '' ) {
			$this->lazyload_setup();

			if ( ! empty( $mode ) && isset( $this->advanced_uri_params[ $mode ] ) ) {
				return $this->advanced_uri_params[ $mode ];
			}

			return $this->base_uri_params;
		}

		/**
		 * Returns required parameters that are part of a given base URI.
		 *
		 * @since 1.0.0
		 *
		 * @param string $base_uri Base URI.
		 * @return array Base URI parameters.
		 */
		public function get_base_uri_params_by_uri( $base_uri ) {
			$this->lazyload_setup();

			if ( $this->base_uri === $base_uri ) {
				return $this->base_uri_params;
			}

			$mode = array_search( $base_uri, $this->advanced_uris, true );
			if ( false !== $mode ) {
				return $this->advanced_uri_params[ $mode ];
			}

			return array();
		}

		/**
		 * Returns the config key.
		 *
		 * This identifies the configuration array where values for this API are stored in.
		 *
		 * @since 1.0.0
		 *
		 * @return string The config key.
		 */
		public function get_config_key() {
			return $this->config_key;
		}

		/**
		 * Returns the API title.
		 *
		 * @since 1.0.0
		 *
		 * @return string The title.
		 */
		public function get_title() {
			return $this->title;
		}

		/**
		 * Returns the API description.
		 *
		 * @since 1.0.0
		 *
		 * @return string The description.
		 */
		public function get_description() {
			return $this->description;
		}

		/**
		 * Processes the response.
		 *
		 * This method can contain API-specific logic to verify the response is correct.
		 * It should either return the passed $response object in its original state or
		 * throw an exception.
		 *
		 * @since 1.0.0
		 *
		 * @param \APIAPI\Core\Request\Route_Response $response Response object.
		 * @return \APIAPI\Core\Request\Route_Response Response object.
		 */
		public function process_response( Route_Response $response ) {
			$this->lazyload_setup();

			return $response;
		}

		/**
		 * Sets up the API structure.
		 *
		 * This method should populate the routes array, and can also be used to
		 * handle further initialization functionality, like setting the authenticator
		 * class and default authentication data.
		 *
		 * @since 1.0.0
		 */
		protected abstract function setup();

		/**
		 * Ensures that all routes are real route objects instead of plain arrays.
		 *
		 * @since 1.0.0
		 */
		protected function process_routes() {
			$route_objects = array();

			foreach ( $this->routes as $uri => $data ) {
				if ( is_a( $data, Route::class ) ) {
					$route_objects[ $uri ] = $data;
				} else {
					$route_objects[ $uri ] = new Route( $uri, $data, $this );
				}
			}

			$this->routes = $route_objects;
		}

		/**
		 * Ensures that all global parameters contain the necessary data.
		 *
		 * @since 1.0.0
		 */
		protected function process_global_params() {
			$this->global_params = Util::parse_params_data( $this->global_params, array(
				'internal' => false,
			) );
		}

		/**
		 * Ensures that all URI parameters contain the necessary data.
		 *
		 * @since 1.0.0
		 */
		protected function process_uri_params() {
			$this->base_uri_params = $this->process_uri_params_set( $this->base_uri, $this->base_uri_params );

			foreach ( $this->advanced_uris as $mode => $advanced_uri ) {
				if ( ! isset( $this->advanced_uri_params[ $mode ] ) ) {
					$this->advanced_uri_params[ $mode ] = array();
				}

				$this->advanced_uri_params[ $mode ] = $this->process_uri_params_set( $advanced_uri, $this->advanced_uri_params[ $mode ] );
			}
		}

		/**
		 * Processes a single set of URI and its params.
		 *
		 * @since 1.0.0
		 *
		 * @param string $uri    URI to extract params from.
		 * @param array  $params Parameter definition, if already provided.
		 * @return array Processed set of parameters.
		 */
		protected function process_uri_params_set( $uri, array $params ) {
			if ( ! preg_match_all( '#\{([A-Za-z0-9_]+)\}#', $uri, $matches ) ) {
				return array();
			}

			$processed_params = array();

			foreach ( $matches[1] as $uri_param ) {
				$processed_params[ $uri_param ] = array(
					'required'    => true,
					'description' => '',
					'type'        => 'string',
					'default'     => null,
					'location'    => '',
					'enum'        => array(),
					'internal'    => false,
					'base'        => true,
				);

				foreach ( array( 'description', 'enum', 'internal' ) as $field ) {
					if ( isset( $params[ $uri_param ][ $field ] ) ) {
						$processed_params[ $uri_param ][ $field ] = $params[ $uri_param ][ $field ];
					}
				}

				$processed_params[ $uri_param ] = Util::parse_param_data( $processed_params[ $uri_param ], array(
					'internal' => false,
					'base'     => false,
				) );
			}

			return $processed_params;
		}
	}

}
