/*!
 * Torro Forms Version 1.0.0-beta.5 (http://torro-forms.com)
 * Licensed under GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
( function( exports, wp, $, translations ) {
	'use strict';

	function Access_Control_Selected_Members( translations ) {
		this.translations = translations;

		this.selectors = {
			participants: '#form-participants',
			participants_counter: '#form-participants-count',
			participants_status: '#form-participants-status p',
			participants_list: '#torro-participants',
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
			invite_request_text: '#invites-send-request-text',
			invite_close: '#invite-close',
			participants_start: '#participants-start',
			participants_length: '#participants-length',
			participants_num_results: '#participants-num-results',
			participants_slider: '#torro-participants .torro-slider',
			participants_slider_middle: '#torro-participants .torro-slider-middle',
			participants_slider_left: '#torro-participants .torro-slider-left',
			participants_slider_right: '#torro-participants .torro-slider-right',
			participants_slider_navigation: '#torro-participants .torro-slider-navigation',
			participants_slider_navigation_prev: '#torro-participants .torro-slider-navigation .torro-nav-prev-link .torro-nav-button',
			participants_slider_navigation_next: '#torro-participants .torro-slider-navigation .torro-nav-next-link .torro-nav-button',
			add_participant_option: '#form-add-participants-option',
			add_participant_button: '#form-add-participants-button',
			remove_participant_button: '.form-delete-participant',
			remove_all_participants_button: '.form-remove-all-participants',
			remove_participant_text: '#participant-delete-text',
			remove_all_participants_text: '#participants-delete-all-text',
			nothing_found: '.no-users-found'
		};
	}

	Access_Control_Selected_Members.prototype = {
		init: function() {
			this.init_invitations();
			this.init_add_members();
			this.init_remove_members();
			this.init_nav_link();
			this.refresh_nothing_found();
		},

		init_nav_link: function(){
			var self = this;

			$( self.selectors.participants_slider_navigation_next ).on( 'click', self.participants, function( e ){
				e.preventDefault();

				var $button = $( this );
				$button.addClass( 'button-loading' );

				var url = $( this ).attr( 'href' );

				wp.ajax.post( 'torro_get_participants_list', {
					nonce: self.translations.nonce_get_participants_list,
					form_id: self.get_form_id(),
					start: self.get_url_param_value( url, 'torro-start' ),
					length: self.get_url_param_value( url, 'torro-length' ),
					num_results: self.get_url_param_value( url, 'torro-num-results' )
				}).done( function( response ) {
					$( self.selectors.participants_slider_middle + ', ' + self.selectors.participants_slider_navigation ).fadeOut( 500, function() {
						$( self.selectors.participants_slider_middle ).html( response.table );
						$( self.selectors.participants_slider_middle ).fadeIn( 500 );

						$( self.selectors.participants_slider_navigation ).html( response.navi );
						$( self.selectors.participants_slider_navigation ).fadeIn( 500 );
						self.init_nav_link();
					});
					$button.removeClass('button-loading');
				}).fail( function( message ) {
					$button.removeClass('button-loading');
					console.log( message );
				});

			});

			$( self.selectors.participants_slider_navigation_prev ).on( 'click', self.participants, function( e ){
				e.preventDefault();

				var $button = $( this );
				$button.addClass( 'button-loading' );

				var url = $( this ).attr( 'href' );

				wp.ajax.post( 'torro_get_participants_list', {
					nonce: self.translations.nonce_get_participants_list,
					form_id: self.get_form_id(),
					start: self.get_url_param_value( url, 'torro-start' ),
					length: self.get_url_param_value( url, 'torro-length' ),
					num_results: self.get_url_param_value( url, 'torro-num-results' )
				}).done( function( response ) {
					$( self.selectors.participants_slider_middle + ', ' + self.selectors.participants_slider_navigation ).fadeOut( 500, function() {
						$( self.selectors.participants_slider_middle ).html( response.table );
						$( self.selectors.participants_slider_middle ).fadeIn( 500 );

						$( self.selectors.participants_slider_navigation ).html( response.navi );
						$( self.selectors.participants_slider_navigation ).fadeIn( 500 );
						self.init_nav_link();
					});
					$button.removeClass('button-loading');
				}).fail( function( message ) {
					$button.removeClass('button-loading');
					console.log( message );
				});

			});
		},

		init_add_members: function() {
			var self = this;

			$( document ).on( 'click', this.selectors.add_participant_button, function(){
				var $button = $( this );
				$button.addClass( 'button-loading' );

				var option = $( self.selectors.add_participant_option ).val();

				if( option == 'allmembers' ) {
					wp.ajax.post('torro_add_participants_allmembers', {
						nonce: self.translations.nonce_add_participants_allmembers,
						form_id: self.get_form_id()
					}).done(function (response) {
						$( self.selectors.participants_list ).html( response.html );
						console.log( response.html );
						self.init_nav_link();
						$button.removeClass('button-loading');
					}).fail(function (message) {
						console.log(message);
					});
				}
			});
		},

		init_remove_members: function(){
			var self = this;

			$( document ).on( 'click', self.selectors.remove_all_participants_button, function() {
				var $remove_participants_dialog = $( self.selectors.remove_all_participants_text );

				$remove_participants_dialog.dialog({
					'dialogClass'	: 'wp-dialog',
					'modal'			: true,
					'autoOpen'		: false,
					'closeOnEscape'	: true,
					'minHeight'		: 80,
					'buttons'		: [
						{
							text: self.translations.yes,
							click: function() {
								wp.ajax.post( 'torro_delete_all_participants', {
									nonce: self.translations.nonce_delete_all_participants,
									form_id: self.get_form_id(),
								}).done(function (response) {
									$( self.selectors.participants_slider_middle ).html( response.table );
									$( self.selectors.participants_slider_navigation ).html( response.navi );
								}).fail(function (message) {
									console.log(message);
								});
								$( this ).dialog( "close" );
							}
						},
						{
							text: self.translations.no,
							click: function() {
								$( this ).dialog( "close" );
							}
						},
					],
				});
				$remove_participants_dialog.dialog( 'open' );
			});

			$( document ).on( 'click', self.selectors.remove_participant_button, function() {
				var user_id = $( this ).attr( 'data-user-id' );
				var start = $( self.selectors.participants_start ).val();
				var length = $( self.selectors.participants_length ).val();
				var num_results = $( self.selectors.participants_num_results ).val();

				var $button = $( this );
				$button.addClass( 'button-loading' );

				wp.ajax.post( 'torro_delete_participant', {
					nonce: self.translations.nonce_delete_participant,
					form_id: self.get_form_id(),
					user_id: user_id,
					start: start,
					length: length,
					num_results: num_results
				}).done(function (response) {
					$( self.selectors.participants_num_results ).val( num_results - 1 );
					$( self.selectors.participants_slider_middle ).html( response.table );
					$( self.selectors.participants_slider_navigation ).html( response.navi );
					$button.removeClass( 'button-loading' );
				}).fail(function (message) {
					console.log(message);
					$button.removeClass( 'button-loading' );
				});
			});

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
					$(self.selectors.invite_close).show();
				}else{
					selected = 'none';

					$( self.selectors.invite_button ).removeClass( 'active' );
					$(self.selectors.invite_email).hide();
					$(self.selectors.invite_send).hide();
					$(self.selectors.invite_close).hide();
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
					$(self.selectors.invite_close).show();
				}else{
					selected = 'none';

					$(self.selectors.reinvite_button).removeClass( 'active' );
					$(self.selectors.reinvite_email).hide();
					$(self.selectors.invite_send).hide();
					$(self.selectors.invite_close).hide();
				}
			});

			$( self.selectors.invite_close ).on( 'click', function() {
				$(self.selectors.invite_button).removeClass( 'active' );
				$(self.selectors.reinvite_button).removeClass( 'active' );
				$(self.selectors.invite_email).hide();
				$(self.selectors.reinvite_email).hide();
				$(self.selectors.invite_send).hide();
				$(self.selectors.invite_close).hide();
			});

			$( self.selectors.invite_send ).on( 'click', function(){
				var $button = $( this );
				var $send_invites_dialog = $( self.selectors.invite_request_text );

				$send_invites_dialog.dialog({
					'dialogClass'	: 'wp-dialog',
					'modal'			: true,
					'autoOpen'		: false,
					'closeOnEscape'	: true,
					'minHeight'		: 80,
					'buttons'		: [
						{
							text: self.translations.yes,
							click: function() {
								$( this ).dialog('close');
								$button.addClass( 'button-loading' );

								var invitation_type = 'invite';

								if( $( self.selectors.invite_button).hasClass( 'active' ) ){
									var from_name = $(self.selectors.invite_email_input_from_name).val();
									var from = $(self.selectors.invite_email_input_from).val();
									var subject = $(self.selectors.invite_email_input_subject).val();
									var text = $(self.selectors.invite_email_input_text).val();
								}else{
									invitation_type = 'reinvite';
									var from_name = $(self.selectors.reinvite_email_input_from_name).val();
									var from = $(self.selectors.reinvite_email_input_from).val();
									var subject = $(self.selectors.reinvite_email_input_subject).val();
									var text = $(self.selectors.reinvite_email_input_text).val();
								}

								wp.ajax.post( 'torro_invite_participants', {
									nonce: self.translations.nonce_invite_participants,
									form_id: self.get_form_id(),
									from_name: from_name,
									from: from,
									subject: subject,
									text: text,
									invitation_type: invitation_type
								}).done( function( response ) {

									$( '#form-functions-notices').html( self.translations.deleted_results_successfully );
									$( '#form-functions-notices').show();

									$button.removeClass( 'button-loading' );

									$( '#form-functions-notices' ).fadeOut( 5000 );
								}).fail( function( message ) {
									console.error( message );
									$button.removeClass( 'button-loading' );
								});
							}
						},
						{
							text: self.translations.no,
							click: function() {
								$( this ).dialog( "close" );
							}
						},
					],
				});
				$send_invites_dialog.dialog( 'open' );
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
		},

		/**
		 * Gets URL Param
		 */
		get_url_param_value: function( url, param ) {
			var variables = url.split( '?' );
			variables = variables[1];
			variables = variables.split( '&' );

			for ( var i = 0; i < variables.length; i++ ) {
				var param_name = variables[ i ].split( '=' );

				if ( param_name[0] == param ) {
					return param_name[1];
				}
			}
		},

		/**
		 * Returns the current form ID
		 */
		get_form_id: function() {
			return $( '#post_ID' ).val();
		},
	};

	exports.add_extension( 'access_control_selected_members', new Access_Control_Selected_Members( translations ) );
}( form_builder, wp, jQuery, translation_sm ) );
