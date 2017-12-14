/*!
 * Torro Forms Version 1.0.0-beta.8 (http://torro-forms.com)
 * Licensed under GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
( function( $ ) {
	'use strict';

	$( '.has-torro-tooltip-description .content-wrap > .description' ).each( function() {
		$( this )
			.addClass( 'torro-tooltip-description' )
			.wrap( '<div class="torro-tooltip-wrap" />' )
			.before( '<span class="torro-tooltip-button dashicons dashicons-info" aria-hidden="true" />' );
	});

})( window.jQuery );
