<?php
/**
 * API-API Invalid_Request_Exception class
 *
 * @package APIAPI\Core\Exception
 * @since 1.0.0
 */

namespace APIAPI\Core\Exception;

use APIAPI\Core\Exception;

if ( ! class_exists( 'APIAPI\Core\Exception\Invalid_Request_Exception' ) ) {

	/**
	 * Invalid_Request_Exception class.
	 *
	 * Thrown when a request is invalid.
	 *
	 * @since 1.0.0
	 */
	class Invalid_Request_Exception extends Exception {

	}

}
