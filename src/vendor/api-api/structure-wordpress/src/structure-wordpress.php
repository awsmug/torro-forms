<?php
/**
 * Structure loader.
 *
 * @package APIAPI\Structure_WordPress
 * @since 1.0.0
 */

if ( ! function_exists( 'apiapi_register_structure_wordpress' ) ) {

	/**
	 * Registers the structure for the API of any WordPress site.
	 *
	 * It is stored in a global if the API-API has not yet been loaded.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name     Unique slug for the site's API structure.
	 * @param string $base_uri Base URI for accessing the API.
	 */
	function apiapi_register_structure_wordpress( $name, $base_uri ) {
		$structure = new APIAPI\Structure_WordPress\Structure_WordPress( $name, $base_uri );

		if ( function_exists( 'apiapi_manager' ) ) {
			apiapi_manager()->structures()->register( $name, $structure );
		} else {
			if ( ! isset( $GLOBALS['_apiapi_structures_loader'] ) ) {
				$GLOBALS['_apiapi_structures_loader'] = array();
			}

			$GLOBALS['_apiapi_structures_loader'][ $name ] = $structure;
		}
	}

	apiapi_register_structure_wordpress( 'wordpress-com', 'https://public-api.wordpress.com/wp/v2/' );
	apiapi_register_structure_wordpress( 'wordpress-org', 'https://wordpress.org/wp-json/' );

}
