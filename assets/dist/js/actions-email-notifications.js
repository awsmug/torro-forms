/*!
 * Torro Forms Version 1.0.0-beta.2 (http://torro-forms.com)
 * Licensed under GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
(function( exports, wp, $, translations ) {
	'use strict';

	function Action_Email_Notifications( translations ) {
		this.translations = translations;

		this.selectors = {
			notifications: '#form-email-notifications .notifications',
			notifications_counter: '#form-email-notifications .notifications  > div',
			add_notification_button: '#form-add-email-notification',
			delete_notification_button: '.form-delete-email-notification',
			delete_notification_dialog: '#delete-email-notification-dialog',
			nothing_found_sub: '.no-entry-found'
		};
	}

	Action_Email_Notifications.prototype = {
		init: function() {
			this.init_notifications();

			var self = this;

			var email_notification_id;
			var $delete_notification_dialog = $( this.selectors.delete_notification_dialog );

			$delete_notification_dialog.dialog({
				'dialogClass'	: 'wp-dialog',
				'modal'			: true,
				'autoOpen'		: false,
				'closeOnEscape'	: true,
				'minHeight'		: 80,
				'buttons'		: [
					{
						text: this.translations.yes,
						click: function() {
							$( '.notification-' + email_notification_id ).remove();
							$( '.notification-' + email_notification_id + '-content' ).remove();

							self.refresh_nothing_found();

							$( this ).dialog('close');
						}
					},
					{
						text: this.translations.no,
						click: function() {
							$( this ).dialog( 'close' );
						}
					},
				],
			});

			$( document ).on( 'click', this.selectors.delete_notification_button, function( e ){
				email_notification_id = $( this ).attr( 'data-emailnotificationid' );

				e.preventDefault();

				$delete_notification_dialog.dialog( 'open' );
			});

			$( document ).on( 'click', this.selectors.add_notification_button, function( e ) {
				var $button = $(this);
				$button.addClass('button-loading');

				wp.ajax.post( 'torro_get_email_notification_html', {
					nonce: self.translations.nonce_get_email_notification_html
				}).done( function( response ) {
					$( self.selectors.notifications ).prepend( response.html );

					form_builder.init_ajax_editor( response );

					self.init_notifications();

					$( '.notification-' + response.id ).hide().fadeIn( 500 );

					$button.removeClass('button-loading');
				}).fail( function( message ) {
					console.log( 'failed' );
					console.log( message );
				});
			});
		},

		init_notifications: function() {
			var notifications_list = $( this.selectors.notifications );

			if ( notifications_list.hasClass( 'ui-accordion' ) ) {
				notifications_list.accordion( 'destroy' );
			}

			this.refresh_nothing_found();

			notifications_list.accordion({
				collapsible: true,
				active: false,
				header: 'h4',
				heightStyle: 'content'
            });
		},

		refresh_nothing_found: function() {
			if ( 0 === $( this.selectors.notifications_counter ).length ) {
				$( this.selectors.notifications ).find( this.selectors.nothing_found_sub ).show();
			} else {
				$( this.selectors.notifications ).find( this.selectors.nothing_found_sub ).hide();
			}
		}
	};

	exports.add_extension( 'action_email_notifications', new Action_Email_Notifications( translations ) );
}( form_builder, wp, jQuery, translation_email_notifications ) );
