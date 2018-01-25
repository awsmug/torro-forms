( function( $ ) {
	'use strict';

	$( '.has-torro-tooltip-description .content-wrap > .description' ).each( function() {
		$( this )
			.addClass( 'torro-tooltip-description' )
			.wrap( '<div class="torro-tooltip-wrap" />' )
			.before( '<span class="torro-tooltip-button dashicons dashicons-info" aria-hidden="true" />' );
	});

})( window.jQuery );
