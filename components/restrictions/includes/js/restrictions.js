(function ($) {
    "use strict";
    $( function () {

        /**
         * Initializing participiants restrictions option
         */
        $( "#questions-restrictions-option" ).change( function(){
            form_restrictions_show_hide_boxes();
        });

        var form_restrictions_show_hide_boxes = function(){
            var form_restrictions_select = $( "#questions-restrictions-option" ).val(); // Getting selected box

            $( ".questions-restrictions-content" ).hide(); // Hiding all boxes
            $( "#questions-restrictions-content-" +  form_restrictions_select ).show(); // Showing selected box
        }

        form_restrictions_show_hide_boxes();
    });
}(jQuery));