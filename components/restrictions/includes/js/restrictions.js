(function ($) {
    "use strict";
    $( function () {

        /**
         * Initializing participiants restrictions option
         */
        $( "#questions-restrictions-option" ).change( function(){
            questions_restrictions_show_hide_boxes();
        });

        var questions_restrictions_show_hide_boxes = function(){
            var questions_restrictions_select = $( "#questions-restrictions-option" ).val(); // Getting selected box

            $( ".questions-restrictions-content" ).hide(); // Hiding all boxes
            $( "#questions-restrictions-content-" +  questions_restrictions_select ).show(); // Showing selected box
        }

        questions_restrictions_show_hide_boxes();
    });
}(jQuery));