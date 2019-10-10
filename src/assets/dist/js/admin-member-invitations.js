/*!
 * Torro Forms Version 1.0.8 (https://torro-forms.com)
 * Licensed under GNU General Public License v2 (or later) (http://www.gnu.org/licenses/gpl-2.0.html)
 */
( function( $, wp, data ) {
	'use strict';

	function prependTemporaryFeedback( $wrap, $feedback ) {
		$wrap.prepend( $feedback );

		setTimeout( function() {
			$feedback.fadeOut( 'slow', function() {
				$feedback.remove();
			});
		}, 2000 );
	}

	$( '.torro-send-invitation' ).each( function() {
		var $input = $( this ).prev();
		if ( $input.val() ) {
			$( this ).prop( 'disabled', false );
		}
	});

	$( document ).on( 'change', '.torro-member-invitation-input + input[type="hidden"]', function() {
		var $this   = $( this );
		var $button = $this.next( '.torro-send-invitation' );

		if ( $button.length ) {
			if ( $this.val() ) {
				$button.prop( 'disabled', false );
			} else {
				$button.prop( 'disabled', true );
			}
		}
	});

	$( document ).on( 'click', '.torro-send-invitation', function( e ) {
		var $button = $( this );

		var userId = parseInt( $button.prev().val(), 10 );
		var formId = parseInt( $( '#post_ID' ).val(), 10 );

		e.preventDefault();

		wp.ajax.post( data.ajaxPrefix + 'invite_member', {
			nonce: data.ajaxInviteMemberNonce,
			userId: userId,
			formId: formId
		}).done( function( response ) {
			var $feedback = $( '<div />' );

			$feedback.addClass( 'notice notice-success' );
			$feedback.text( response.message );

			prependTemporaryFeedback( $button.parents( '.plugin-lib-repeatable-item' ), $feedback );
		}).fail( function( message ) {
			var $feedback = $( '<div />' );

			$feedback.addClass( 'notice notice-error' );
			$feedback.text( message );

			prependTemporaryFeedback( $button.parents( '.plugin-lib-repeatable-item' ), $feedback );
		});
	});

}( window.jQuery, window.wp, window.torroMemberInvitations ) );
