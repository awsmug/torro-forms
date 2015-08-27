(function ($)
{
    "use strict";
    $( function ()
    {
        var questions_response_handlers_init_email_notifications = function(){
            var notifications_list = $( "#questions-email-notifications .notifications" );
            var notifications_list_count = $( '#questions-email-notifications .notifications  > div' ).length;

            if( notifications_list.hasClass( 'ui-accordion' ) )
            {
                notifications_list.accordion( "destroy" );
            }

            notifications_list.accordion({
                collapsible: true,
                active: false,
                header: "h4",
                heightStyle: "content"
            });
            console.log( 'Test: ' + notifications_list_count );

            console.log( tinyMCEPreInit );

            if( notifications_list_count == 0 ){
                $( '#questions-email-notifications .notifications .no-entry-found' ).show();
            }else{
                $( '#questions-email-notifications .notifications .no-entry-found' ).hide();
            }
        }
        questions_response_handlers_init_email_notifications();

        $( '#questions_add_email_notification').click( function(){
            var data = {
                action: 'get_email_notification_html',
            };

            $.post( ajaxurl, data, function( response ) {
                response = jQuery.parseJSON( response );

                $( '#questions-email-notifications .notifications' ).prepend( response.html );

                questions_response_handlers_init_email_notifications();

                $( ".notification-" + response.id ).hide().fadeIn(2500);

                // Initializing HTML & Text-Editors
                tinyMCE.init( tinyMCEPreInit.mceInit[ response.editor_id ] );
                try { quicktags( tinyMCEPreInit.qtInit[ response.editor_id ] ); } catch(e){ console.log( "error" ); }

                // console.log( tinyMCEPreInit.qtInit );
            });
        })
    });
}(jQuery));