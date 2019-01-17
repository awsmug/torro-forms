<?php
/**
 * Authenticator_X class
 *
 * @package APIAPI\Authenticator_X
 * @since 1.0.0
 */

namespace APIAPI\Authenticator_X;

use APIAPI\Core\Authenticators\Authenticator;
use APIAPI\Core\Request\Route_Request;
use APIAPI\Core\Exception\Request_Authentication_Exception;

if ( ! class_exists( 'APIAPI\Authenticator_X\Authenticator_X' ) ) {

	/**
	 * Authenticator implementation for X header tokens.
	 *
	 * @since 1.0.0
	 */
	class Authenticator_X extends Authenticator {
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
		public function authenticate_request( Route_Request $request ) {
			$data = $this->parse_authentication_data( $request );

			if ( empty( $data['header_name'] ) ) {
				$data['header_name'] = 'X-Authorization';
			} elseif ( 0 !== strpos( $data['header_name'], 'X-' ) ) {
				$data['header_name'] = 'X-' . $data['header_name'];
			}

			if ( empty( $data['token'] ) ) {
				throw new Request_Authentication_Exception( sprintf( 'The request to %s could not be authenticated as a token has not been passed.', $request->get_uri() ) );
			}

			$request->set_header( $data['header_name'], $data['token'] );
		}

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
		public function is_authenticated( Route_Request $request ) {
			$data = $this->parse_authentication_data( $request );

			if ( empty( $data['header_name'] ) ) {
				$data['header_name'] = 'X-Authorization';
			} elseif ( 0 !== strpos( $data['header_name'], 'X-' ) ) {
				$data['header_name'] = 'X-' . $data['header_name'];
			}

			$header_value = $request->get_header( $data['header_name'] );

			return null !== $header_value;
		}

		/**
		 * Sets the default authentication arguments.
		 *
		 * @since 1.0.0
		 */
		protected function set_default_args() {
			$this->default_args = array(
				'header_name' => 'Authorization',
				'token'       => '',
			);
		}
	}

}
