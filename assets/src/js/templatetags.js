(function ( exports, wp, $, translations ) {
    'use strict';

    /**
     * Form_Builder constructor
     */
    function Torro_Templatetags( translations ) {
        this.translations = translations;

        this.extensions = {};

        this.selectors = {
        };
    }

    /**
     * Form_Builder class
     */
    Torro_Templatetags.prototype = {
        init: function() {
            this.init_templatetag_buttons();
        },

        /**
         * Handling the Templatetag Button
         */
        init_templatetag_buttons: function() {
            $( 'html' ).on( 'click', function() {
                $( '.torro-templatetag-list' ).hide();
            });

            $( '.torro-templatetag-button' ).on( 'click', function( e ) {
                var $list = $( this ).find( '.torro-templatetag-list' );

                if ( 'none' == $list.css( 'display' ) ) {
                    $list.show();
                } else {
                    $list.hide();
                }

                e.stopPropagation();
            });

            var $template_tag = $( '.torro-templatetag-list .torro-templatetag' );

            $template_tag.unbind();

            $template_tag.on( 'click', function() {
                var tag_name = '{' + $( this ).attr( 'data-tagname' ) + '}';
                var input_id = $( this ).attr( 'data-input-id' );
                var editor = tinymce.get( input_id );

                if (editor && editor instanceof tinymce.Editor) {
                    editor.insertContent( tag_name );
                }else{
                    var $input = $( 'input[name="' + input_id + '"]' );
                    $input.val( $input.val() + tag_name );
                }
            });
        },

        rand: function() {
            var now = new Date();
            var random = Math.floor( Math.random() * ( 10000 - 10 + 1 ) ) + 10;

            random = random * now.getTime();
            random = random.toString();

            return random;
        }
    };

    exports.add_extension( 'templatetags', new Torro_Templatetags( translations ) );
}( form_builder, wp, jQuery, translation_fb ) );