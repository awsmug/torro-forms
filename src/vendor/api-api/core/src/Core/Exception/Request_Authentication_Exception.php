<?php
/**
 * API-API Request_Authentication_Exception class
 *
 * @package APIAPI\Core\Exception
 * @since 1.0.0
 */

namespace APIAPI\Core\Exception;

use APIAPI\Core\Exception;

if ( ! class_exists( 'APIAPI\Core\Exception\Request_Authentication_Exception' ) ) {

	/**
	 * Request_Authentication_Exception class.
	 *
	 * Thrown when a request cannot be authenticated.
	 *
	 * @since 1.0.0
	 */
	class Request_Authentication_Exception extends Exception {

	}

}
