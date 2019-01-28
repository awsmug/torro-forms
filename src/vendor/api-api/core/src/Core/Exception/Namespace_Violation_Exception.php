<?php
/**
 * API-API Namespace_Violation_Exception class
 *
 * @package APIAPI\Core\Exception
 * @since 1.0.0
 */

namespace APIAPI\Core\Exception;

use APIAPI\Core\Exception;

if ( ! class_exists( 'APIAPI\Core\Exception\Namespace_Violation_Exception' ) ) {

	/**
	 * Namespace_Violation_Exception class.
	 *
	 * Thrown when an object is passed into a different APIAPI instance than it belongs to.
	 *
	 * @since 1.0.0
	 */
	class Namespace_Violation_Exception extends Exception {

	}

}
