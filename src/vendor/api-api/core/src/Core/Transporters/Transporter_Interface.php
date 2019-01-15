<?php
/**
 * API-API Transporter interface
 *
 * @package APIAPI\Core\Transporters
 * @since 1.0.0
 */

namespace APIAPI\Core\Transporters;

use APIAPI\Core\Request\Request;
use APIAPI\Core\Exception\Request_Transport_Exception;

if ( ! interface_exists( 'APIAPI\Core\Transporters\Transporter_Interface' ) ) {

	/**
	 * Transporter interface for the API-API.
	 *
	 * Represents a specific transporter method.
	 *
	 * @since 1.0.0
	 */
	interface Transporter_Interface {
		/**
		 * Sends a request and returns the response.
		 *
		 * @since 1.0.0
		 *
		 * @param Request $request The request to send.
		 * @return array The returned response as an array with 'headers', 'body',
		 *               and 'response' key. The array does not necessarily
		 *               need to include all of these keys.
		 *
		 * @throws Request_Transport_Exception Thrown when the request cannot be sent.
		 */
		public function send_request( Request $request );
	}

}
