( function( $ ) {
	'use strict';
	$( function() {
		/**
		 * Initializing adding participants option
		 */
		$( '#form-add-participants-option' ).change( function() {
			form_add_participants_show_hide_boxes();
		});

		var form_add_participants_show_hide_boxes = function() {
			var form_add_participants_option = $( '#form-add-participants-option' ).val(); // Getting selected box

			$( '.form-add-participants-content' ).hide(); // Hiding all boxes
			$( '#form-add-participants-content-' +  form_add_participants_option ).show(); // Showing selected box
		}

		form_add_participants_show_hide_boxes();

		/**
		 * Setup 'Not found'
		 */
		var form_setup_not_found_message = function() {
			var count_participants = parseInt( $( '#form-participants-count' ).val());

			if ( 0 < count_participants ) {
				$( '.no-users-found' ).hide();
			} else {
				$( '.no-users-found' ).show();
			}
		}

		form_setup_not_found_message();

		/**
		 * Members - Adding Participiants
		 */
		$.form_add_participants = function( response ) {
			var form_participants = $( '#form-participants' ).val();
			form_participants = form_participants.split( ',' );

			var count_added_participants = 0;

			$.each( response, function( i, object ) {
				var found = false;

				if ( in_array( object.id, form_participants ) ) {
					found = true;
				}

				// If there where found participants
				if ( false == found ){
					// Adding participants
					if ( '' === form_participants ) {
						form_participants = object.id;
					} else {
						form_participants = form_participants + ',' + object.id;
					}

					$( '#form-participants-list tbody' ).append( '<tr class="participant participant-user-' + object.id + ' just-added"><td>' + object.id + '</td><td>' + object.user_nicename + '</td><td>' + object.display_name + '</td><td>' + object.user_email + '</td><td>' + translation_sm.just_added + '</td><td><a class="button form-delete-participant" rel="' + object.id +  '">' + translation_sm.delete + '</a></td></tr>' );
					count_added_participants++;
				}
			});

			var count_participants = parseInt( $( '#form-participants-count' ).val() ) + count_added_participants;

			$( '#form-participants' ).val( form_participants );
			$.form_participants_counter( count_participants );

			$( '#form-participants-list' ).show();
			$.form_delete_participant();

			form_setup_not_found_message();
		}

		$( '#form-add-participants-allmembers-button' ).click( function(){
			var data = {
				action: 'form_add_participants_allmembers'
			};

			var button = $( this );
			button.addClass( 'button-loading' );

			$.post( ajaxurl, data, function( response ) {
				response = jQuery.parseJSON( response );
				$.form_add_participants( response );
				button.removeClass( 'button-loading' );
			});
		});

		/**
		 * Counting participants
		 */
		$.form_participants_counter = function( number ) {
			var text = number + ' ' + translation_sm.added_participants;
			$( '#form-participants-status p').html( text );
			$( '#form-participants-count' ).val( number );
		};

		/**
		 * Removing participant from list
		 */
		$.form_delete_participant = function() {
			$( '.form-delete-participant' ).click( function() {
				var delete_user_id = $( this ).attr( 'rel' );

				var form_participants_new = '';

				var form_participants = $( '#form-participants' ).val();
				form_participants = form_participants.split( ',' );

				$.each( form_participants, function( key, value ) {
					if ( value != delete_user_id ){
						if ( '' === form_participants_new ){
							form_participants_new = value;
						} else {
							form_participants_new = form_participants_new + ',' + value;
						}
					}
				});

				$( '#form-participants' ).val( form_participants_new );
				$.form_participants_counter( $( '#form-participants-count' ).val() - 1 );
				$( '.participant-user-' + delete_user_id ).remove();

				form_setup_not_found_message();
			});
		}
		$.form_delete_participant();

		/**
		 * Removing all Participiants from list
		 */
		$( '.form-remove-all-participants' ).click( function() {
			$( '#form-participants' ).val( '' );
			$( '#form-participants-count' ).val( 0 );
			$.form_participants_counter( 0 );

			$( '#form-participants-list tbody .participant' ).remove();

			form_setup_not_found_message();
		});

		/**
		 * Invite participants
		 */
		$( '#form-invite-button' ).click( function() {
			var button = $( this )

			if ( button.hasClass( 'button-primary' ) ) {
				var data = {
					action: 'form_invite_participants',
					invitation_type: 'invite',
					form_id: $( '#post_ID' ).val(),
					subject_template: $( '#form-invite-subject' ).val(),
					text_template: $( '#form-invite-text' ).val()
				};

				button.addClass( 'button-loading' );

				$.post( ajaxurl, data, function( response ) {
					response = jQuery.parseJSON( response );
					if( response.sent ) {
						$( '#form-invite-subject' ).fadeOut( 200 );
						$( '#form-invite-text' ).fadeOut( 200 );
						$( '#form-invite-text' ).after( '<p class="form-reinvitations-sent">' + translation_sm.invitations_sent_successfully + '</p>' );
					} else {
						$( '#form-invite-subject' ).fadeOut( 200 );
						$( '#form-invite-text' ).fadeOut( 200 );
						$( '#form-invite-text' ).after( '<p class="form-reinvitations-sent">' + translation_sm.invitations_sent_not_successfully + '</p>' );
					}
					button.removeClass( 'button-loading' );

					$( '.form-reinvitations-sent' ).fadeOut( 4000 );
					$( '#form-invite-button' ).removeClass( 'button-primary' );
					$( '#form-invite-text' ).fadeOut( 200 );
					$( '#form-invite-button-cancel' ).fadeOut( 200 );
				});
			} else {
				button.addClass( 'button-primary' );
				$( '#form-invite-subject' ).fadeIn( 200 );
				$( '#form-invite-text' ).fadeIn( 200 );
				$( '#form-invite-button-cancel' ).fadeIn( 200 );
			}
		});

		$( '#form-invite-button-cancel' ).click( function(){
			$( '#form-invite-button' ).removeClass( 'button-primary' );
			$( '#form-invite-subject' ).fadeOut( 200 );
			$( '#form-invite-text' ).fadeOut( 200 );
			$( '#form-invite-button-cancel' ).fadeOut( 200 );
		});

		$( '#form-reinvite-button' ).click( function() {
			var button = $( this )

			if ( button.hasClass( 'button-primary' ) ) {
				var data = {
					action: 'form_invite_participants',
					invitation_type: 'reinvite',
					form_id: $( '#post_ID' ).val(),
					subject_template: $( '#form-reinvite-subject' ).val(),
					text_template: $( '#form-reinvite-text' ).val()
				};

				button.addClass( 'button-loading' );

				$.post( ajaxurl, data, function( response ) {
					response = jQuery.parseJSON( response );
					if ( response.sent ) {
						$( '#form-reinvite-subject' ).fadeOut( 200 );
						$( '#form-reinvite-text' ).fadeOut( 200 );
						$( '#form-reinvite-text' ).after( '<p class="form-reinvitations-sent">' + translation_sm.reinvitations_sent_successfully + '</p>' );
						button.removeClass( 'button-loading' );
						$( '.form-reinvitations-sent' ).fadeOut( 4000 );
					} else {
						$( '#form-reinvite-subject' ).fadeOut( 200 );
						$( '#form-reinvite-text' ).fadeOut( 200 );
						$( '#form-reinvite-text' ).after( '<p class="form-reinvitations-sent">' + translation_sm.reinvitations_sent_not_successfully + '</p>' );
					}
					button.removeClass( 'button-loading' );
					$( '.form-reinvitations-sent' ).fadeOut( 4000 );
					$( '#form-reinvite-button' ).removeClass( 'button-primary' );
					$( '#form-reinvite-text' ).fadeOut( 200 );
					$( '#form-reinvite-button-cancel' ).fadeOut( 200 );
				});
			} else {
				button.addClass( 'button-primary' );
				$( '#form-reinvite-subject' ).fadeIn( 200 );
				$( '#form-reinvite-text' ).fadeIn( 200 );
				$( '#form-reinvite-button-cancel' ).fadeIn( 200 );
			}
		});

		$( '#form-reinvite-button-cancel' ).click( function(){
			$( '#form-reinvite-button' ).removeClass( 'button-primary' );
			$( '#form-reinvite-subject' ).fadeOut( 200 );
			$( '#form-reinvite-text' ).fadeOut( 200 );
			$( '#form-reinvite-button-cancel' ).fadeOut( 200 );
		});

		/**
		 * Helper function - Getting a random number
		 */
		function torro_rand() {
			var now = new Date();
			var random = Math.floor(Math.random() * ( 10000 - 10 + 1)) + 10;
			random = random * now.getTime();
			random = random.toString().substring( 0, 5 );

			return random;
		}

		/**
		 * Helper function - JS recreating of PHP in_array function
		 */
		function in_array( needle, haystack, strict ) {
			var length = haystack.length;
			if ( strict ) {
				for ( var i = 0; i < length; i++ ) {
					if( haystack[ i ] === needle ) {
						return true;
					}
				}
			} else {
				for ( var i = 0; i < length; i++ ) {
					if( haystack[ i ] == needle ) {
						return true;
					}
				}
			}
			return false;
		}
	});
}( jQuery ) );
