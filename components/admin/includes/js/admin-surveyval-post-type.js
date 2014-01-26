(function ($) {
	"use strict";
	$(function () {
		$( ".surveyval-draggable" ).draggable( { 
			appendTo: "body",
			helper: "clone",
			cursor: "move"
		});
		
		$( "#drag-drop-area" ).droppable({
			accept: ".surveyval-draggable",
			drop: function( event, ui ) {
				var helper = $( ".drag-drop-inside" ).html();
				var new_helper = '<div class="drag-drop-inside">' + helper + '</div>';
				var draggable_content =  '<div class="widget">' + ui.draggable.html() + '</div>';				
				
				$( ".drag-drop-inside" ).remove();
				$( "#drag-drop-area" ).html( $( "#drag-drop-area" ).html() + draggable_content + new_helper );
			}
		}).sortable({
			sort: function() {
			}
		});
	});
}(jQuery));