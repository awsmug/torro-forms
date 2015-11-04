(function ($)
{
    "use strict";
    $( function ()
    {
        var af_actions_init_email_notifications = function(){
            var notifications_list = $( "#form-email-notifications .notifications" );
            var notifications_list_count = $( '#form-email-notifications .notifications  > div' ).length;

            if( notifications_list.hasClass( 'ui-accordion' ) )
            {
                notifications_list.accordion( 'destroy' );
            }

            notifications_list.accordion({
                collapsible: true,
                active: false,
                header: "h4",
                heightStyle: "content"
            });

            if( notifications_list_count == 0 ){
                $( '#form-email-notifications .notifications .no-entry-found' ).show();
            }else{
                $( '#form-email-notifications .notifications .no-entry-found' ).hide();
            }

            $.af_templatetag_buttons();

            var form_deletemailnotification_dialog = $( '#delete_email_notification_dialog' );
            var email_notification_id;

            form_deletemailnotification_dialog.dialog({
                'dialogClass'   : 'wp-dialog',
                'modal'         : true,
                'autoOpen'      : false,
                'closeOnEscape' : true,
                'minHeight'		: 80,
                'buttons'       : [{
                    text: translation_email_notifications.yes,
                    click: function() {
                        $( '.notification-' + email_notification_id ).remove();
                        $( '.notification-' + email_notification_id + '-content' ).remove();

                        $( this ).dialog('close');
                    }
                },
                    {
                        text: translation_email_notifications.no,
                        click: function() {
                            $( this ).dialog( "close" );
                        }
                    },
                ],

            });

            $( '.form-delete-email-notification' ).click( function( event ){
                email_notification_id = $( this ).attr( 'data-emailnotificationid' );

                event.preventDefault();

                form_deletemailnotification_dialog.dialog( 'open' );
            });
        }
        af_actions_init_email_notifications();

        $( '#form_add_email_notification').click( function(){
            var data = {
                action: 'get_email_notification_html',
            };

            $.post( ajaxurl, data, function( response ) {
                response = jQuery.parseJSON( response );

                $( '#form-email-notifications .notifications' ).prepend( response.html );
                af_actions_init_email_notifications();

                $( ".notification-" + response.id ).hide().fadeIn(2500);
            });
        })
    });
}(jQuery));