(function ( exports, $ ) {

    /**
     * Form_Builder constructor
     */
    function AF_FB_Entry() {
        this.selectors = {
            show_entry: '.af-show-entry',
            entries_table: '#af-entries-table',
            entry: '#af-entry',
        };
    }

    /**
     * AF_Entry class
     */
    AF_FB_Entry.prototype = {
        init: function () {
            this.init_show_entry();
        },
        /**
         * Shows clicked Entry
         */
        init_show_entry: function () {
            var self = this;

            $(this.selectors.show_entry).on('click', function () {
                var $button = $(this);

                if ($button.hasClass('button')) {

                    var result_id = $button.attr( 'rel' );

                    var data = {
                        action: 'af_show_entry',
                        form_id: self.get_form_id(),
                        result_id: result_id
                    };

                    $button.addClass('button-loading');

                    $.post(ajaxurl, data, function (response) {
                        var html = '<p>' + response + '</p>';

                        console.log( $(self.selectors.entries_table).html() );

                        $( self.selectors.entries_table ).append( html );

                        $button.removeClass('button-loading');

                    });

                } else {
                    $button.addClass('button');
                }
            });
        },
        /**
         * Returns the current form ID
         */
        get_form_id: function() {
            return $( '#post_ID' ).val();
        }
    };

    var af_fb_entry = new AF_FB_Entry();

    $( document ).ready( function() {
        af_fb_entry.init();
    });

}( window, jQuery) );