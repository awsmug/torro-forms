( function( $, ajaxurl ) {
	'use strict';

	$( '.torro-evaluations-tab' ).on( 'click', function( e ) {
		var $this = $( this );
		var $all  = $this.parent().children( '.torro-evaluations-tab' );

		e.preventDefault();

		if ( 'true' === $this.attr( 'aria-selected' ) ) {
			return;
		}

		$all.each( function() {
			$( this ).attr( 'aria-selected', 'false' );
			$( '#' + $( this ).attr( 'aria-controls' ) ).attr( 'aria-hidden', 'true' );
		});

		$this.attr( 'aria-selected', 'true' );
		$( '#' + $this.attr( 'aria-controls' ) ).attr( 'aria-hidden', 'false' ).find( '.torro-evaluations-results' ).each( function() {
			$( this ).trigger( 'refresh' );
		});
	});

	$( '.torro-evaluations-subtab' ).on( 'click', function( e ) {
		var $this = $( this );
		var $all  = $this.parent().children( '.torro-evaluations-subtab' );

		e.preventDefault();

		if ( 'true' === $this.attr( 'aria-selected' ) ) {
			return;
		}

		$all.each( function() {
			$( this ).attr( 'aria-selected', 'false' );
			$( '#' + $( this ).attr( 'aria-controls' ) ).attr( 'aria-hidden', 'true' );
		});

		$this.attr( 'aria-selected', 'true' );
		$( '#' + $this.attr( 'aria-controls' ) ).attr( 'aria-hidden', 'false' );
	});

	$( '.handlediv' ).on( 'click', function() {
		var $button = $( this );
		var $postbox = $button.parent( '.postbox' );
		var closed, hidden;

		$postbox.toggleClass( 'closed' );

		$button.attr( 'aria-expanded', ! $postbox.hasClass( 'closed' ) );

		closed = $( '.postbox' ).filter( '.closed' ).map( function() {
			return this.id;
		}).get().join( ',' );

		hidden = $( '.postbox' ).filter( ':hidden' ).map( function() {
			return this.id;
		}).get().join( ',' );

		$.post( ajaxurl, {
			action: 'closed-postboxes',
			closed: closed,
			hidden: hidden,
			closedpostboxesnonce: $( '#closedpostboxesnonce' ).val(),
			page: $( '#closedpostboxespage' ).val()
		});
	});

}( window.jQuery, window.ajaxurl ) );
