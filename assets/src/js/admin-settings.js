( function( $ ) {
	'use strict';

	var $rewriteSlugPreview = $( '#torro-rewrite-slug-preview' );
	if ( $rewriteSlugPreview.length ) {
		$rewriteSlugPreview.parent().prev().on( 'keyup', function() {
			$rewriteSlugPreview.text( $( this ).val() );
		});
	}

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

}( window.jQuery ) );
