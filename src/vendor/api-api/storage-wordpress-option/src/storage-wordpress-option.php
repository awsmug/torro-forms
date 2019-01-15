<?php
/**
 * Storage loader.
 *
 * @package APIAPI\Storage_WordPress_Option
 * @since 1.0.0
 */

if ( ! function_exists( 'apiapi_register_storage_wordpress_option' ) ) {

	/**
	 * Registers the storage using WordPress options.
	 *
	 * It is stored in a global if the API-API has not yet been loaded.
	 *
	 * @since 1.0.0
	 */
	function apiapi_register_storage_wordpress_option() {
		if ( function_exists( 'apiapi_manager' ) ) {
			apiapi_manager()->storages()->register( 'wordpress-option', 'APIAPI\Storage_WordPress_Option\Storage_WordPress_Option' );
		} else {
			if ( ! isset( $GLOBALS['_apiapi_storages_loader'] ) ) {
				$GLOBALS['_apiapi_storages_loader'] = array();
			}

			$GLOBALS['_apiapi_storages_loader']['wordpress-option'] = 'APIAPI\Storage_WordPress_Option\Storage_WordPress_Option';
		}
	}

	apiapi_register_storage_wordpress_option();

}
