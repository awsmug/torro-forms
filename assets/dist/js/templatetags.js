/*!
 * Torro Forms Version 1.0.0alpha1 (http://torro-forms.com)
 * Licensed under GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
(function( $ ) {
	'use strict';

	/**
	 * Form_Builder constructor
	 */
	function Torro_Templatetags() {
		this.selectors = {
			button: '.torro-templatetag-button .button',
			button_list: '.torro-templatetag-list',
			tag_sub: '.torro-templatetag',
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
			var self = this;

			$( 'html' ).on( 'click', function() {
				$( self.selectors.button_list ).hide();
			} );

			$( document ).on( 'click', this.selectors.button, function( e ) {
				$( this ).parent().find( self.selectors.button_list ).toggle();

				e.stopPropagation();
				e.preventDefault();
			} );

			$( document ).on( 'click', this.selectors.button_list + ' ' + this.selectors.tag_sub, function() {
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

	if( typeof form_builder == 'undefined' ){
		$( document ).ready( function(){
			var templatetags = new Torro_Templatetags();
			templatetags.init();
		});
	} else {
		form_builder.add_extension( 'templatetags', new Torro_Templatetags() );
	}

}( jQuery ) );
