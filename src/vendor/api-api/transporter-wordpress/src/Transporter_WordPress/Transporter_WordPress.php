<?php
/**
 * Transporter_WordPress class
 *
 * @package APIAPI\Transporter_WordPress
 * @since 1.0.0
 */

namespace APIAPI\Transporter_WordPress;

use APIAPI\Core\Transporters\Transporter;
use APIAPI\Core\Request\Request;
use APIAPI\Core\Request\Method;
use APIAPI\Core\Exception\Request_Transport_Exception;

if ( ! class_exists( 'APIAPI\Transporter_WordPress\Transporter_WordPress' ) ) {

	/**
	 * Transporter implementation for WordPress.
	 *
	 * @since 1.0.0
	 */
	class Transporter_WordPress extends Transporter {
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
		public function send_request( Request $request ) {
			$url = $request->get_uri();

			$args = array(
				'method'  => $request->get_method(),
				'headers' => array(),
			);

			foreach ( $request->get_headers( true ) as $header_name => $header_values ) {
				foreach ( $header_values as $header_value ) {
					$args['headers'][] = $header_name . ': ' . $header_value;
				}
			}

			$params = $request->get_params();
			if ( ! empty( $params ) ) {
				if ( Method::GET === $args['method'] ) {
					$url = add_query_arg( $params, $url );
				} elseif ( 0 === strpos( $request->get_header( 'content-type' ), 'application/json' ) ) {
					$args['body'] = wp_json_encode( $params );
					if ( ! $args['body'] ) {
						throw new Request_Transport_Exception( sprintf( 'The request to %s could not be sent as the data could not be JSON-encoded.', $url ) );
					}
				} else {
					$args['body'] = http_build_query( $params, null, '&' );
				}
			}

			$response_data = wp_remote_request( $url, $args );
			if ( is_wp_error( $response_data ) ) {
				throw new Request_Transport_Exception( sprintf( 'The request to %1$s could not be sent: %2$s', $url, $response_data->get_error_message() ) );
			}

			// Cookies are not supported at this point.
			if ( isset( $response_data['cookies'] ) ) {
				unset( $response_data['cookies'] );
			}

			return $response_data;
		}
	}

}
