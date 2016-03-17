(function( $ ) {
	'use strict';

	/**
	 * Form_Builder constructor
	 */
	function Torro_Templatetags() {
		this.selectors = {};
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
			} );

			$( '.torro-templatetag-button' ).on( 'click', function( e ) {
				var $list = $( this ).find( '.torro-templatetag-list' );

				if ( 'none' == $list.css( 'display' ) ) {
					$list.show();
				} else {
					$list.hide();
				}

				e.stopPropagation();
			} );

			var $template_tag = $( '.torro-templatetag-list .torro-templatetag' );

			$template_tag.unbind();

			$template_tag.on( 'click', function() {
				var tag_name = '{' + $( this ).attr( 'data-tagname' ) + '}';
				var input_id = $( this ).attr( 'data-input-id' );

				if ( !tinymce ) {
					var $input = $( 'input[name="' + input_id + '"]' );
					$input.val( $input.val() + tag_name );
				} else {
					var editor = tinymce.get( input_id );

					if ( editor && editor instanceof tinymce.Editor ) {
						editor.insertContent( tag_name );
					} else {
						var $input = $( 'input[name="' + input_id + '"]' );
						$input.val( $input.val() + tag_name );
					}
				}
			} );
		},
	};

	if( ! form_builder ){
		$( document ).ready( function(){
			var templatetags = new Torro_Templatetags();
			templatetags.init();
		});
	} else {
		form_builder.add_extension( 'templatetags', new Torro_Templatetags() );
	}

}( jQuery ) );
