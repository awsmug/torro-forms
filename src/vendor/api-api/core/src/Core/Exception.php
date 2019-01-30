<?php
/**
 * API-API Exception class
 *
 * @package APIAPI\Core
 * @since 1.0.0
 */

namespace APIAPI\Core;

use Exception as BaseException;

if ( ! class_exists( 'APIAPI\Core\Exception' ) ) {

	/**
	 * Exception class for the API-API.
	 *
	 * @since 1.0.0
	 */
	class Exception extends BaseException {
		/**
		 * Additional data for the exception.
		 *
		 * @since 1.0.0
		 * @var mixed
		 */
		protected $data = null;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $message Optional. Message to print out. Default empty.
		 * @param int    $code    Optional. Code for the exception. Default 0.
		 * @param mixed  $data    Optional. Additional data related to the exception. Default null.
		 */
		public function __construct( $message = '', $code = 0, $data = null ) {
			parent::__construct( $message, $code );

			$this->data = $data;
		}

		/**
		 * Returns additional data for the exception.
		 *
		 * If no additional data is provided, null will be returned.
		 *
		 * @since 1.0.0
		 *
		 * @return mixed Additional data for the exception.
		 */
		public function getData() {
			return $this->data;
		}
	}

}
