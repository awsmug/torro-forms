<?php
/**
 * API-API Authenticator class
 *
 * @package APIAPI\Core\Authenticators
 * @since 1.0.0
 */

namespace APIAPI\Core\Authenticators;

use APIAPI\Core\Util;
use APIAPI\Core\Name_Trait;
use APIAPI\Core\Request\Route_Request;

if ( ! class_exists( 'APIAPI\Core\Authenticators\Authenticator' ) ) {

	/**
	 * Authenticator class for the API-API.
	 *
	 * Represents a specific authenticator.
	 *
	 * @since 1.0.0
	 */
	abstract class Authenticator implements Authenticator_Interface {
		use Name_Trait;

		/**
		 * Default authentication arguments.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $default_args = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Slug of the instance.
		 */
		public function __construct( $name ) {
			$this->set_name( $name );
			$this->set_default_args();
		}

		/**
		 * Returns the default arguments.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of `$key => $value` pairs.
		 */
		public function get_default_args() {
			return $this->default_args;
		}

		/**
		 * Parses request authentication data.
		 *
		 * @since 1.0.0
		 *
		 * @param Route_Request $request The request to send.
		 * @return array Parsed authentication data for the request.
		 */
		protected function parse_authentication_data( Route_Request $request ) {
			return Util::parse_args( $request->get_authentication_data(), $this->default_args );
		}

		/**
		 * Sets the default authentication arguments.
		 *
		 * @since 1.0.0
		 */
		protected abstract function set_default_args();
	}

}
