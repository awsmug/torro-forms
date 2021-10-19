<?php
/**
 * Shortcut functions to access the main objects.
 *
 * @package APIAPI\Core
 * @since 1.0.0
 */

if ( ! function_exists( 'apiapi_manager' ) ) {

	/**
	 * Returns the API-API manager instance.
	 *
	 * @since 1.0.0
	 *
	 * @return APIAPI\Core\Manager The API-API manager instance.
	 */
	function apiapi_manager() {
		return APIAPI\Core\Manager::instance();
	}

}

if ( ! function_exists( 'apiapi' ) ) {

	/**
	 * Returns a specific API-API instance.
	 *
	 * @since 1.0.0
	 *
	 * @param string                        $name  Unique slug of the instance.
	 * @param APIAPI\Core\Config|array|bool $force Optional. Whether to create the instance if it does not exist.
	 *                                             Can also be a configuration object or array to fill the set up
	 *                                             the new instance with this configuration. Default false.
	 * @return APIAPI\Core\APIAPI|null The API-API instance, or null if it does not exist.
	 */
	function apiapi( $name, $force = false ) {
		return apiapi_manager()->get_instance( $name, $force );
	}

}

if ( ! function_exists( 'apiapi_load_extensions_from_global' ) ) {

	/**
	 * Loads extensions stored in a global.
	 *
	 * When an extension is loaded before the API-API Core, it cannot register itself and instead stores
	 * its values in a global. This function registers any modules stored in one of the globals and unsets
	 * them afterwards.
	 *
	 * @since 1.0.0
	 */
	function apiapi_load_extensions_from_global() {
		if ( isset( $GLOBALS['_apiapi_storages_loader'] ) ) {
			foreach ( $GLOBALS['_apiapi_storages_loader'] as $name => $storage ) {
				apiapi_manager()->storages()->register( $name, $storage );
			}

			unset( $GLOBALS['_apiapi_storages_loader'] );
		}

		if ( isset( $GLOBALS['_apiapi_transporters_loader'] ) ) {
			foreach ( $GLOBALS['_apiapi_transporters_loader'] as $name => $transporter ) {
				apiapi_manager()->transporters()->register( $name, $transporter );
			}

			unset( $GLOBALS['_apiapi_transporters_loader'] );
		}

		if ( isset( $GLOBALS['_apiapi_authenticators_loader'] ) ) {
			foreach ( $GLOBALS['_apiapi_authenticators_loader'] as $name => $authenticator ) {
				apiapi_manager()->authenticators()->register( $name, $authenticator );
			}

			unset( $GLOBALS['_apiapi_authenticators_loader'] );
		}

		if ( isset( $GLOBALS['_apiapi_structures_loader'] ) ) {
			foreach ( $GLOBALS['_apiapi_structures_loader'] as $name => $structure ) {
				apiapi_manager()->structures()->register( $name, $structure );
			}

			unset( $GLOBALS['_apiapi_structures_loader'] );
		}
	}

	apiapi_load_extensions_from_global();

}
