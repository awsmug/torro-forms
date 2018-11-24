<?php
/**
 * API-API Module_Not_Registered_Exception class
 *
 * @package APIAPI\Core\Exception
 * @since 1.0.0
 */

namespace APIAPI\Core\Exception;

use APIAPI\Core\Exception;

if ( ! class_exists( 'APIAPI\Core\Exception\Module_Not_Registered_Exception' ) ) {

	/**
	 * Module_Not_Registered_Exception class.
	 *
	 * Thrown when an unregistered module is accessed.
	 *
	 * @since 1.0.0
	 */
	class Module_Not_Registered_Exception extends Exception {

	}

}
