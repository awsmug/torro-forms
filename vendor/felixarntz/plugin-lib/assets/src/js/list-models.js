( function( $, pluginLibData ) {
	'use strict';

	$( '.submitdelete' ).on( 'click', function( e ) {
		if ( ! window.confirm( pluginLibData.i18n.confirm_deletion ) ) {
			e.preventDefault();
		}
	});

}( jQuery, pluginLibListModelsData ) );
