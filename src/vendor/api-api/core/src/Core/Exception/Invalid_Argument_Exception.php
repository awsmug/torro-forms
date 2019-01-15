<?php
/**
 * API-API Invalid_Argument_Exception class
 *
 * @package APIAPI\Core\Exception
 * @since 1.0.0
 */

namespace APIAPI\Core\Exception;

use APIAPI\Core\Exception;

if ( ! class_exists( 'APIAPI\Core\Exception\Invalid_Argument_Exception' ) ) {

	/**
	 * Invalid_Argument_Exception class.
	 *
	 * Thrown when a method argument is invalid.
	 *
	 * @since 1.0.0
	 */
	class Invalid_Argument_Exception extends Exception {

	}

}
