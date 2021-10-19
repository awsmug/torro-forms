<?php
/**
 * API-API Authenticator interface
 *
 * @package APIAPI\Core\Authenticators
 * @since 1.0.0
 */

namespace APIAPI\Core\Authenticators;

use APIAPI\Core\Request\Route_Request;
use APIAPI\Core\Exception\Request_Authentication_Exception;

if ( ! interface_exists( 'APIAPI\Core\Authenticators\Authenticator_Interface' ) ) {

	/**
	 * Authenticator interface for the API-API.
	 *
	 * Represents a specific authenticator.
	 *
	 * @since 1.0.0
	 */
	interface Authenticator_Interface {
		/**
		 * Authenticates a request.
		 *
		 * This method does not yet actually authenticate the request with the server. It only sets
		 * the required values on the request object.
		 *
		 * @since 1.0.0
		 *
		 * @param Route_Request $request The request to send.
		 *
		 * @throws Request_Authentication_Exception Thrown when the request cannot be authenticated.
		 */
		public function authenticate_request( Route_Request $request );

		/**
		 * Checks whether a request is authenticated.
		 *
		 * This method does not check whether the request was actually authenticated with the server.
		 * It only checks whether authentication data has been properly set on it.
		 *
		 * @since 1.0.0
		 *
		 * @param Route_Request $request The request to check.
		 * @return bool True if the request is authenticated, otherwise false.
		 */
		public function is_authenticated( Route_Request $request );

		/**
		 * Returns the default arguments.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of `$key => $value` pairs.
		 */
		public function get_default_args();
	}

}
