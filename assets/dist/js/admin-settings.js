/*!
 * Torro Forms Version 1.0.0-beta.8 (http://torro-forms.com)
 * Licensed under GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
( function( $ ) {
	'use strict';

	$( '.torro-subtab' ).on( 'click', function( e ) {
		var $this = $( this );
		var $all  = $this.parent().children( '.torro-subtab' );

		e.preventDefault();

		if ( 'true' === $this.attr( 'aria-selected' ) ) {
			return;
		}

		$all.each( function() {
			$( this ).attr( 'aria-selected', 'false' );
			$( '#' + $( this ).attr( 'aria-controls' ) ).attr( 'aria-hidden', 'true' );
		});

		$this.attr( 'aria-selected', 'true' );
		$( '#' + $this.attr( 'aria-controls' ) ).attr( 'aria-hidden', 'false' ).find( '.plugin-lib-map-control' ).each( function() {
			$( this ).wpMapPicker( 'refresh' );
		});
	});

}( jQuery ) );
