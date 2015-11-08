(function ( exports, $ ) {

    /**
     * Form_Builder constructor
     */
    function AF_FB_Entry() {
        this.selectors = {
            show_entry: '.af-show-entry',
            hide_entry: '.af-hide-entry',
            entries_table: '#af-entries-table',
            entry: '#af-entry',
            entries_slider: '.af-entries-slider',
            entries_slider_start_content: '.af-slider-start-content',
            entries_slider_right: '.af-slider-right',
            entries_nav: '.af-entries-nav',
        };
    }

    /**
     * AF_Entry class
     */
    AF_FB_Entry.prototype = {
        init: function () {
            this.init_show_entry();
            this.init_hide_entry();
            this.init_nav_link();
        },
        /**
         * Shows clicked Entry
         */
        init_show_entry: function () {
            var self = this;

            $(self.selectors.show_entry).on('click', function () {
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
                        var html = response;

                        $( self.selectors.entries_slider_right ).html( html );
                        $( self.selectors.entries_slider ).animate({marginLeft: "-100%"});

                        $button.removeClass('button-loading');

                        self.init_hide_entry();
                    });

                } else {
                    $button.addClass('button');
                }
            });
        },
        init_hide_entry: function () {
            var self = this;

            $(self.selectors.hide_entry).on('click', function () {
                console.log( self.selectors.hide_entry );
                $( self.selectors.entries_slider ).animate({marginLeft: "0"});
            });
        },
        init_nav_link: function () {
            var self = this;

            $(self.selectors.entries_nav).on('click', function () {
                var $button = $(this);
                $button.addClass('button-loading');

                var url = $( this ).attr( 'href' );
                var start = self.get_url_param_value( url, 'af-entries-start' );
                var length = self.get_url_param_value( url, 'af-entries-length' );

                var data = {
                    action: 'af_show_entries',
                    form_id: self.get_form_id(),
                    start: start,
                    length: length
                }

                $.post(ajaxurl, data, function (response) {
                    var html = response;
                    $button.removeClass('button-loading');

                    $(self.selectors.entries_slider_start_content).fadeOut( 500, function(){
                        $(self.selectors.entries_slider_start_content).html( html );
                        $(self.selectors.entries_slider_start_content).fadeIn( 500 );
                    });
                });

                return false;
            });
        },
        /**
         * Returns the current form ID
         */
        get_form_id: function() {
            return $( '#post_ID' ).val();
        },
        /**
         * Gets URL Param
         */
        get_url_param_value: function( url, param ) {

            var variables = url.split('?');
            variables = variables[1];
            variables = variables.split('&');

            for (var i = 0; i < variables.length; i++)
            {
                var param_name = variables[i].split('=');

                if ( param_name[0] == param )
                {
                    return param_name[1];
                }
            }
        }
    };

    var af_fb_entry = new AF_FB_Entry();

    $( document ).ready( function() {
        af_fb_entry.init();
    });

}( window, jQuery) );