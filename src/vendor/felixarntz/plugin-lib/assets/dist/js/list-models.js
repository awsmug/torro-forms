/*!
 * plugin-lib (https://github.com/felixarntz/plugin-lib)
 * By Felix Arntz (https://leaves-and-love.net)
 * Licensed under GPL-2.0-or-later
 */
( function( $, pluginLibData ) {
	'use strict';

	$( '.submitdelete' ).on( 'click', function( e ) {
		if ( ! window.confirm( pluginLibData.i18n.confirm_deletion ) ) {
			e.preventDefault();
		}
	});

}( jQuery, pluginLibListModelsData ) );
