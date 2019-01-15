<?php
/**
 * API-API Structure interface
 *
 * @package APIAPI\Core\Structures
 * @since 1.0.0
 */

namespace APIAPI\Core\Structures;

use APIAPI\Core\APIAPI;
use APIAPI\Core\Request\API;
use APIAPI\Core\Request\Route_Request;
use APIAPI\Core\Request\Route_Response;
use APIAPI\Core\Request\Method;
use APIAPI\Core\Exception\Invalid_Route_Exception;

if ( ! interface_exists( 'APIAPI\Core\Structures\Structure_Interface' ) ) {

	/**
	 * Structure interface for the API-API.
	 *
	 * Represents a specific API structure.
	 *
	 * @since 1.0.0
	 */
	interface Structure_Interface {
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
		public function get_api_object( APIAPI $apiapi );

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
		public function get_request_object( APIAPI $apiapi, $route_uri, $method = Method::GET );

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
		public function get_route_object( $route_uri );

		/**
		 * Checks whether a specific route exists.
		 *
		 * @since 1.0.0
		 *
		 * @param string $route_uri URI of the route.
		 * @return bool True if the route exists, otherwise false.
		 */
		public function has_route( $route_uri );

		/**
		 * Returns all available routes.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of route objects.
		 */
		public function get_route_objects();

		/**
		 * Returns the authenticator name for the API.
		 *
		 * @since 1.0.0
		 *
		 * @return string Authenticator name, or empty string if not set.
		 */
		public function get_authenticator();

		/**
		 * Checks whether the API should use an authenticator.
		 *
		 * @since 1.0.0
		 *
		 * @return bool True if the API should use an authenticator, otherwise false.
		 */
		public function has_authenticator();

		/**
		 * Returns this API's base URI for a specific mode.
		 *
		 * @since 1.0.0
		 *
		 * @param string $mode Optional. Mode for which to get the base URI. Default empty.
		 * @return string Base URI.
		 */
		public function get_base_uri( $mode = '' );

		/**
		 * Returns required parameters that are part of this API's base URI for a specific mode.
		 *
		 * @since 1.0.0
		 *
		 * @param string $mode Optional. Mode for which to get the base URI parameters. Default empty.
		 * @return array Base URI parameters.
		 */
		public function get_base_uri_params( $mode = '' );

		/**
		 * Returns required parameters that are part of a given base URI.
		 *
		 * @since 1.0.0
		 *
		 * @param string $base_uri Base URI.
		 * @return array Base URI parameters.
		 */
		public function get_base_uri_params_by_uri( $base_uri );

		/**
		 * Returns the config key.
		 *
		 * This identifies the configuration array where values for this API are stored in.
		 *
		 * @since 1.0.0
		 *
		 * @return string The config key.
		 */
		public function get_config_key();

		/**
		 * Returns the API title.
		 *
		 * @since 1.0.0
		 *
		 * @return string The title.
		 */
		public function get_title();

		/**
		 * Returns the API description.
		 *
		 * @since 1.0.0
		 *
		 * @return string The description.
		 */
		public function get_description();

		/**
		 * Processes the response.
		 *
		 * This method can contain API-specific logic to verify the response is correct.
		 * It should either return the passed $response object in its original state or
		 * throw an exception.
		 *
		 * @since 1.0.0
		 *
		 * @param Route_Response $response Response object.
		 * @return Route_Response Response object.
		 */
		public function process_response( Route_Response $response );
	}

}
