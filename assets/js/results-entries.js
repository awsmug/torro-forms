(function ( exports, $ ) {

    /**
     * Form_Builder constructor
     */
    function Torro_FB_Entry() {
        this.selectors = {
            show_entry: '.torro-show-entry',
            hide_entry: '.torro-hide-entry',
            entries: '#torro-entries',
            entries_table: '#torro-entries-table',
            entry: '#torro-entry',
            entries_slider: '.torro-entries-slider',
            entries_slider_start_content: '.torro-slider-start-content',
            entries_slider_right: '.torro-slider-right',
            entries_nav: '.torro-entries-nav',
        };
    }

    /**
     * Torro_Entry class
     */
    Torro_FB_Entry.prototype = {
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

            $( this.selectors.entries ).on( 'click', this.selectors.show_entry, function ( e ) {
                var $button = $( this );

                e.preventDefault();

                if ($button.hasClass('button')) {

                    var result_id = $button.attr( 'rel' );

                    var data = {
                        action: 'torro_show_entry',
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

            $( this.selectors.entries ).on( 'click', this.selectors.hide_entry, function ( e ) {
                e.preventDefault();

                $( self.selectors.entries_slider ).animate({marginLeft: "0"});
            });
        },

        init_nav_link: function () {
            var self = this;

            $( this.selectors.entries ).on( 'click', this.selectors.entries_nav, function ( e ) {
                var $button = $(this);
                $button.addClass('button-loading');

                e.preventDefault();

                var url = $( this ).attr( 'href' );
                var start = self.get_url_param_value( url, 'torro-entries-start' );
                var length = self.get_url_param_value( url, 'torro-entries-length' );

                var data = {
                    action: 'torro_show_entries',
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

    var torro_fb_entry = new Torro_FB_Entry();

    $( document ).ready( function() {
        torro_fb_entry.init();
    });

}( window, jQuery) );
