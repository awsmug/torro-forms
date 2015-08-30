(function ($) {
    "use strict";
    $( function () {
        new Fingerprint2().get(function(fngrprnt){
            console.log(fngrprnt);

            var data = {
                action: 'form_restrictions_save_fngrprnt',
                fingerprint: $( fngrprnt ).val(),
            };

            $.post( ajaxurl, data, function( response ) {
                console.log(response);
            });
        });
    });
}(jQuery));