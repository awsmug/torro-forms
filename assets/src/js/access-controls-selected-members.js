( function( exports, wp, $, translations ) {
	'use strict';

	function Access_Control_Selected_Members( translations ) {
		this.translations = translations;

		this.selectors = {
			participants: '#form-participants',
			participants_counter: '#form-participants-count',
			participants_status: '#form-participants-status p',
			participants_list: '#form-participants-list',
			participant_sub: '.participant',
			invite_button: '#torro-invite-participants-button',
			reinvite_button: '#torro-reinvite-participants-button',
			invite_email: '#torro-invite-email',
			invite_email_input_from_name: '#torro-invite-email input[name=invite_from_name]',
			invite_email_input_from: '#torro-invite-email input[name=invite_from]',
			invite_email_input_subject: '#torro-invite-email input[name=invite_subject]',
			invite_email_input_text: '#invite_text',
			reinvite_email: '#torro-reinvite-email',
			reinvite_email_input_from_name: '#torro-reinvite-email input[name=reinvite_from_name]',
			reinvite_email_input_from: '#torro-reinvite-email input[name=reinvite_from]',
			reinvite_email_input_subject: '#torro-reinvite-email input[name=reinvite_subject]',
			reinvite_email_input_text: '#reinvite_text',
			invite_send: '#torro-send-invitations-button',
			delete_participant_button: '.form-delete-participant',
			add_all_members_button: '#form-add-participants-allmembers-button',
			remove_all_members_button: '.form-remove-all-participants',
			nothing_found: '.no-users-found'
		};
	}

	Access_Control_Selected_Members.prototype = {
		init: function() {
			this.init_invitations();

			this.refresh_nothing_found();

			var self = this;

			$( document ).on( 'click', this.selectors.add_all_members_button, function(){
				var $button = $( this );
				$button.addClass( 'button-loading' );

				wp.ajax.post( 'torro_add_participants_allmembers', {
					nonce: self.translations.nonce_add_participants_allmembers
				}).done( function( response ) {
					var form_participants = $( self.selectors.participants ).val();
					form_participants = form_participants.split( ',' );

					var count_added_participants = 0;

					$.each( response, function( i, object ) {
						var found = false;

						if ( -1 < form_participants.indexOf( object.id ) ) {
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

							$( self.selectors.participants_list ).find( 'tbody' ).append( '<tr class="participant participant-user-' + object.id + ' just-added"><td>' + object.id + '</td><td>' + object.user_nicename + '</td><td>' + object.display_name + '</td><td>' + object.user_email + '</td><td>' + self.translations.just_added + '</td><td><a class="button form-delete-participant" rel="' + object.id +  '">' + self.translations.delete + '</a></td></tr>' );
							count_added_participants++;
						}
					});

					var count_participants = parseInt( $( self.selectors.participants_counter ).val(), 10 ) + count_added_participants;

					$( self.selectors.participants ).val( form_participants );

					self.set_participants_counter( count_participants );

					$( self.selectors.participants_list ).show();

					self.refresh_nothing_found();

					$button.removeClass( 'button-loading' );
				}).fail( function( message ) {
					console.log( message );
				});
			});

			$( document ).on( 'click', this.selectors.remove_all_members_button, function() {
				$( self.selectors.participants ).val( '' );
				self.set_participants_counter( 0 );

				$( self.selectors.participants_list ).find( self.selectors.participant_sub ).remove();
				self.refresh_nothing_found();
			});

			$( document ).on( 'click', this.selectors.delete_participant_button, function() {
				var delete_user_id = $( this ).attr( 'rel' );

				var form_participants_new = '';

				var form_participants = $( self.selectors.participants ).val();
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

				$( self.selectors.participants ).val( form_participants_new );
				self.set_participants_counter( parseInt( $( self.selectors.participants_counter ).val(), 10 ) - 1 );

				$( '.participant-user-' + delete_user_id ).remove();
				self.refresh_nothing_found();
			});

			// Invitations - keep this here, but currently not used
			/*
			$( '#torro-invite-participants-button' ).on( 'click', function() {
				var $button = $( this )

				if ( $button.hasClass( 'button-primary' ) ) {
					$button.addClass( 'button-loading' );

					wp.ajax.post( 'torro_invite_participants', {
						nonce: self.translations.nonce_invite_participants,
						invitation_type: 'invite',
						form_id: $( '#post_ID' ).val(),
						subject_template: $( '#form-invite-subject' ).val(),
						text_template: $( '#form-invite-text' ).val()
					}).done( function( response ) {
						if( response.sent ) {
							$( '#form-invite-subject' ).fadeOut( 200 );
							$( '#form-invite-text' ).fadeOut( 200 );
							$( '#form-invite-text' ).after( '<p class="form-reinvitations-sent">' + self.translations.invitations_sent_successfully + '</p>' );
						} else {
							$( '#form-invite-subject' ).fadeOut( 200 );
							$( '#form-invite-text' ).fadeOut( 200 );
							$( '#form-invite-text' ).after( '<p class="form-reinvitations-sent">' + self.translations.invitations_sent_not_successfully + '</p>' );
						}
						$button.removeClass( 'button-loading' );

						$( '.form-reinvitations-sent' ).fadeOut( 4000 );
						$( '#form-invite-button' ).removeClass( 'button-primary' );
						$( '#form-invite-text' ).fadeOut( 200 );
						$( '#form-invite-button-cancel' ).fadeOut( 200 );
					}).fail( function( message ) {
						console.log( message );
					});
				} else {
					$button.addClass( 'button-primary' );
					$( '#form-invite-subject' ).fadeIn( 200 );
					$( '#form-invite-text' ).fadeIn( 200 );
					$( '#form-invite-button-cancel' ).fadeIn( 200 );
				}
			});

			$( '#form-invite-button-cancel' ).on( 'click', function(){
				$( '#form-invite-button' ).removeClass( 'button-primary' );
				$( '#form-invite-subject' ).fadeOut( 200 );
				$( '#form-invite-text' ).fadeOut( 200 );
				$( '#form-invite-button-cancel' ).fadeOut( 200 );
			});

			$( '#form-reinvite-button' ).on( 'click', function() {
				var $button = $( this )

				if ( $button.hasClass( 'button-primary' ) ) {
					$button.addClass( 'button-loading' );

					wp.ajax.post( 'torro_invite_participants', {
						nonce: self.translations.nonce_invite_participants,
						invitation_type: 'reinvite',
						form_id: $( '#post_ID' ).val(),
						subject_template: $( '#form-reinvite-subject' ).val(),
						text_template: $( '#form-reinvite-text' ).val()
					}).done( function( response ) {
						if ( response.sent ) {
							$( '#form-reinvite-subject' ).fadeOut( 200 );
							$( '#form-reinvite-text' ).fadeOut( 200 );
							$( '#form-reinvite-text' ).after( '<p class="form-reinvitations-sent">' + self.translations.reinvitations_sent_successfully + '</p>' );
							$button.removeClass( 'button-loading' );
							$( '.form-reinvitations-sent' ).fadeOut( 4000 );
						} else {
							$( '#form-reinvite-subject' ).fadeOut( 200 );
							$( '#form-reinvite-text' ).fadeOut( 200 );
							$( '#form-reinvite-text' ).after( '<p class="form-reinvitations-sent">' + self.translations.reinvitations_sent_not_successfully + '</p>' );
						}
						$button.removeClass( 'button-loading' );
						$( '.form-reinvitations-sent' ).fadeOut( 4000 );
						$( '#form-reinvite-button' ).removeClass( 'button-primary' );
						$( '#form-reinvite-text' ).fadeOut( 200 );
						$( '#form-reinvite-button-cancel' ).fadeOut( 200 );
					}).fail( function( message ) {
						console.log( message );
					});
				} else {
					$button.addClass( 'button-primary' );
					$( '#form-reinvite-subject' ).fadeIn( 200 );
					$( '#form-reinvite-text' ).fadeIn( 200 );
					$( '#form-reinvite-button-cancel' ).fadeIn( 200 );
				}
			});

			$( '#form-reinvite-button-cancel' ).on( 'click', function(){
				$( '#form-reinvite-button' ).removeClass( 'button-primary' );
				$( '#form-reinvite-subject' ).fadeOut( 200 );
				$( '#form-reinvite-text' ).fadeOut( 200 );
				$( '#form-reinvite-button-cancel' ).fadeOut( 200 );
			});*/
		},

		init_invitations: function(){
			var self = this;
			var selected = 'none';

			$( self.selectors.invite_button ).on( 'click', function(){
				if( selected == 'none'  || selected == 'reinvite' ) {
					selected = 'invite';

					$( self.selectors.reinvite_button ).removeClass( 'active' );
					$( self.selectors.invite_button ).addClass( 'active' );

					wp.ajax.post( 'torro_get_invite_text', {
						nonce: self.translations.nonce_get_invite_text,
						invite_type: selected
					}).done( function( invite_data ) {

						if( $( self.selectors.invite_email_input_from_name ).val() == '' &&
							$( self.selectors.invite_email_input_from ).val() == '' &&
							$( self.selectors.invite_email_input_subject ).val() == '' &&
							$( self.selectors.invite_email_input_text ).val() == '' )
						{
							$(self.selectors.invite_email_input_from_name).val(invite_data.invite_from_name);
							$(self.selectors.invite_email_input_from).val(invite_data.invite_from);
							$(self.selectors.invite_email_input_subject).val(invite_data.invite_subject);

							var editor = tinymce.get('invite_text');
							if (editor && editor instanceof tinymce.Editor) {
								editor.setContent(invite_data.invite_text.replace(/\r?\n/g, '<br />'));
							}
						}

					}).fail( function( message ) {
						console.error( message );
					});

					$(self.selectors.reinvite_email).hide();
					$(self.selectors.invite_email).show();
					$(self.selectors.invite_send).show();
				}else{
					selected = 'none';

					$( self.selectors.invite_button ).removeClass( 'active' );
					$(self.selectors.invite_email).hide();
					$(self.selectors.invite_send).hide();
				}
			});

			$( self.selectors.reinvite_button ).on( 'click', function(){
				if( selected == 'none' || selected == 'invite' ) {
					selected = 'reinvite';

					$( self.selectors.invite_button ).removeClass( 'active' );
					$( self.selectors.reinvite_button ).addClass( 'active' );

					wp.ajax.post( 'torro_get_invite_text', {
						nonce: self.translations.nonce_get_invite_text,
						invite_type: selected
					}).done( function( invite_data ) {

						if( $( self.selectors.reinvite_email_input_from_name ).val() == '' &&
							$( self.selectors.reinvite_email_input_from ).val() == '' &&
							$( self.selectors.reinvite_email_input_subject ).val() == '' &&
							$( self.selectors.reinvite_email_input_text ).val() == '')
						{
							$(self.selectors.reinvite_email_input_from_name).val(invite_data.invite_from_name);
							$(self.selectors.reinvite_email_input_from).val(invite_data.invite_from);
							$(self.selectors.reinvite_email_input_subject).val(invite_data.invite_subject);

							var editor = tinymce.get('reinvite_text');
							if (editor && editor instanceof tinymce.Editor) {
								editor.setContent(invite_data.invite_text.replace(/\r?\n/g, '<br />'));
							}
						}

					}).fail( function( message ) {
						console.error( message );
					});

					$(self.selectors.invite_email).hide();
					$(self.selectors.reinvite_email).show();
					$(self.selectors.invite_send).show();
				}else{
					selected = 'none';

					$( self.selectors.reinvite_button ).removeClass( 'active' );
					$(self.selectors.reinvite_email).hide();
					$(self.selectors.invite_send).hide();
				}
			});
		},

		set_participants_counter: function( number ) {
			var text = number + ' ' + this.translations.added_participants;
			$( this.selectors.participants_status ).html( text );
			$( this.selectors.participants_counter ).val( number );
		},

		refresh_nothing_found: function() {
			if ( 0 < parseInt( $( this.participants_counter ).val() ) ) {
				$( this.selectors.nothing_found ).hide();
			} else {
				$( this.selectors.nothing_found ).show();
			}
		}
	};

	exports.add_extension( 'access_control_selected_members', new Access_Control_Selected_Members( translations ) );
}( form_builder, wp, jQuery, translation_sm ) );
