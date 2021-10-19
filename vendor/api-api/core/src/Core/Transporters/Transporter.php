<?php
/**
 * API-API Transporter class
 *
 * @package APIAPI\Core\Transporters
 * @since 1.0.0
 */

namespace APIAPI\Core\Transporters;

use APIAPI\Core\Name_Trait;

if ( ! class_exists( 'APIAPI\Core\Transporters\Transporter' ) ) {

	/**
	 * Transporter class for the API-API.
	 *
	 * Represents a specific transporter method.
	 *
	 * @since 1.0.0
	 */
	abstract class Transporter implements Transporter_Interface {
		use Name_Trait;

		/**
		 * Contains status messages.
		 *
		 * @since 1.0.0
		 * @static
		 * @var array
		 */
		protected static $status_messages = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Slug of the instance.
		 */
		public function __construct( $name ) {
			$this->set_name( $name );

			self::set_status_messages();
		}

		/**
		 * Returns the status message for a given status code.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param int $status_code Status code.
		 * @return string Status message, or empty string if invalid code.
		 */
		public static function get_status_message( $status_code ) {
			if ( ! isset( self::$status_messages[ $status_code ] ) ) {
				return '';
			}

			return self::$status_messages[ $status_code ];
		}

		/**
		 * Sets the available status messages.
		 *
		 * @since 1.0.0
		 * @static
		 */
		protected static function set_status_messages() {
			self::$status_messages = array(
				100 => 'Continue',
				101 => 'Switching Protocols',
				102 => 'Processing',

				200 => 'OK',
				201 => 'Created',
				202 => 'Accepted',
				203 => 'Non-Authoritative Information',
				204 => 'No Content',
				205 => 'Reset Content',
				206 => 'Partial Content',
				207 => 'Multi-Status',
				226 => 'IM Used',

				300 => 'Multiple Choices',
				301 => 'Moved Permanently',
				302 => 'Found',
				303 => 'See Other',
				304 => 'Not Modified',
				305 => 'Use Proxy',
				306 => 'Reserved',
				307 => 'Temporary Redirect',
				308 => 'Permanent Redirect',

				400 => 'Bad Request',
				401 => 'Unauthorized',
				402 => 'Payment Required',
				403 => 'Forbidden',
				404 => 'Not Found',
				405 => 'Method Not Allowed',
				406 => 'Not Acceptable',
				407 => 'Proxy Authentication Required',
				408 => 'Request Timeout',
				409 => 'Conflict',
				410 => 'Gone',
				411 => 'Length Required',
				412 => 'Precondition Failed',
				413 => 'Request Entity Too Large',
				414 => 'Request-URI Too Long',
				415 => 'Unsupported Media Type',
				416 => 'Requested Range Not Satisfiable',
				417 => 'Expectation Failed',
				418 => 'I\'m a teapot',
				421 => 'Misdirected Request',
				422 => 'Unprocessable Entity',
				423 => 'Locked',
				424 => 'Failed Dependency',
				426 => 'Upgrade Required',
				428 => 'Precondition Required',
				429 => 'Too Many Requests',
				431 => 'Request Header Fields Too Large',
				451 => 'Unavailable For Legal Reasons',

				500 => 'Internal Server Error',
				501 => 'Not Implemented',
				502 => 'Bad Gateway',
				503 => 'Service Unavailable',
				504 => 'Gateway Timeout',
				505 => 'HTTP Version Not Supported',
				506 => 'Variant Also Negotiates',
				507 => 'Insufficient Storage',
				510 => 'Not Extended',
				511 => 'Network Authentication Required',
			);
		}
	}

}
