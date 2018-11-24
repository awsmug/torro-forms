<?php
/**
 * Transporter loader.
 *
 * @package APIAPI\Transporter_WordPress
 * @since 1.0.0
 */

if ( ! function_exists( 'apiapi_register_transporter_wordpress' ) ) {

	/**
	 * Registers the transporter for WordPress.
	 *
	 * It is stored in a global if the API-API has not yet been loaded.
	 *
	 * @since 1.0.0
	 */
	function apiapi_register_transporter_wordpress() {
		if ( function_exists( 'apiapi_manager' ) ) {
			apiapi_manager()->transporters()->register( 'wordpress', 'APIAPI\Transporter_WordPress\Transporter_WordPress' );
		} else {
			if ( ! isset( $GLOBALS['_apiapi_transporters_loader'] ) ) {
				$GLOBALS['_apiapi_transporters_loader'] = array();
			}

			$GLOBALS['_apiapi_transporters_loader']['wordpress'] = 'APIAPI\Transporter_WordPress\Transporter_WordPress';
		}
	}

	apiapi_register_transporter_wordpress();

}
