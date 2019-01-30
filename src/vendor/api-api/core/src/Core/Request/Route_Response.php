<?php
/**
 * API-API class for a scoped response
 *
 * @package APIAPI\Core\Request
 * @since 1.0.0
 */

namespace APIAPI\Core\Request;

use APIAPI\Core\Structures\Route;

if ( ! class_exists( 'APIAPI\Core\Request\Route_Response' ) ) {

	/**
	 * Response class for the API-API.
	 *
	 * Represents an API response, scoped for an API-API instance.
	 *
	 * @since 1.0.0
	 */
	class Route_Response extends Response {
		/**
		 * The route object for this response.
		 *
		 * @since 1.0.0
		 * @var Route
		 */
		protected $route;

		/**
		 * The method that was used to get the response.
		 * Either 'GET', 'POST', 'PUT', 'PATCH' or 'DELETE'.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $request_method = '';

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $response_data  Response array containing keys 'headers', 'body', and 'response'.
		 *                               Not necessarily all of these are included though.
		 * @param string $request_method Either 'GET', 'POST', 'PUT', 'PATCH' or 'DELETE'.
		 * @param Route  $route          Route object for the response.
		 */
		public function __construct( array $response_data, $request_method, Route $route ) {
			$this->route          = $route;
			$this->request_method = $request_method;

			parent::__construct( $response_data );
		}

		/**
		 * Returns the route object.
		 *
		 * @since 1.0.0
		 *
		 * @return Route Route object.
		 */
		public function get_route_object() {
			return $this->route;
		}
	}

}
