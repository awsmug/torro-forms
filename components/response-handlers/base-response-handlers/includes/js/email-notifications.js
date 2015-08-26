(function ($) {
    "use strict";
    $( function () {
        var questions_response_handlers_email_notifications = function(){
            $( "#questions-email-notifications .notifications" ).accordion({
                collapsible: true,
                active: false,
                header: "h4",
                heightStyle: "content"
            });
        }
        questions_response_handlers_email_notifications();

        $( '#questions_add_email_notification').click( function(){
            var data = {
                action: 'get_email_notification_html',
            };

            $.post( ajaxurl, data, function( response ) {
                response = jQuery.parseJSON( response );

                $( '#questions-email-notifications .notifications' ).prepend( response.html );

                $( "#questions-email-notifications .notifications" ).accordion( "destroy" );
                questions_response_handlers_email_notifications();

                // Initializing HTML & Text-Editors
                tinyMCE.init( tinyMCEPreInit.mceInit[ response.editor_id ] );
                try { quicktags( tinyMCEPreInit.qtInit[ response.editor_id ] ); } catch(e){ console.log( "error" ); }

                console.log( tinyMCEPreInit.qtInit );
            });
        })
    });
}(jQuery));