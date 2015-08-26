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
                // response = jQuery.parseJSON( response );


                $( '#questions-email-notifications .notifications' ).prepend( response );

                $( "#questions-email-notifications .notifications" ).accordion( "destroy" );
                questions_response_handlers_email_notifications();
            });
        })
    });
}(jQuery));