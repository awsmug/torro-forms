( function( $ ) {
	'use strict';

	$( document ).ready( function() {
		/**
		 * Initializing participants restrictions option
		 */
		$( '#form-restrictions-option' ).change( function() {
			form_restrictions_show_hide_boxes();
		});

		var form_restrictions_show_hide_boxes = function() {
			var form_restrictions_select = $( '#form-restrictions-option' ).val(); // Getting selected box

			$( '.form-restrictions-content' ).hide(); // Hiding all boxes
			$( '#form-restrictions-content-' +  form_restrictions_select ).show(); // Showing selected box
		}

		form_restrictions_show_hide_boxes();
	});
}( jQuery ) );
