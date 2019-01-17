<?php
/**
 * Authenticator loader.
 *
 * @package APIAPI\Authenticator_OAuth1
 * @since 1.0.0
 */

if ( ! function_exists( 'apiapi_register_authenticator_oauth1' ) ) {

	/**
	 * Registers the authenticator for OAuth 1.0.
	 *
	 * It is stored in a global if the API-API has not yet been loaded.
	 *
	 * @since 1.0.0
	 */
	function apiapi_register_authenticator_oauth1() {
		if ( function_exists( 'apiapi_manager' ) ) {
			apiapi_manager()->authenticators()->register( 'oauth1', 'APIAPI\Authenticator_OAuth1\Authenticator_OAuth1' );
		} else {
			if ( ! isset( $GLOBALS['_apiapi_authenticators_loader'] ) ) {
				$GLOBALS['_apiapi_authenticators_loader'] = array();
			}

			$GLOBALS['_apiapi_authenticators_loader']['oauth1'] = 'APIAPI\Authenticator_OAuth1\Authenticator_OAuth1';
		}
	}

	apiapi_register_authenticator_oauth1();

}
