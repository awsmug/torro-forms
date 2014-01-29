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
				var now = new Date();
				var helper = $( ".drag-drop-inside" ).html();
				var new_helper = '<div class="drag-drop-inside">' + helper + '</div>';
				
				// Replacing numbers for getting unique ids & setting up container ID
				var random = Math.floor(Math.random() * ( 10000 - 10 + 1)) + 10;
				var nr = random * now.getTime();
				var draggable_content =  ui.draggable.html();
				draggable_content = draggable_content.replace( /##nr##/g, nr );
				
				// Counting elements
				var i = 0;
				$('#drag-drop-area .widget').each( function( e ) { i++; });
              	
				$( ".drag-drop-inside" ).remove();
				$( "#drag-drop-area" ).html( $( "#drag-drop-area" ).html() + draggable_content + new_helper );
				
				// Adding sorting number
				var input_name = 'input[name="surveyval\[widget_question_' + nr +'\]\[sort\]"]';
              	$( input_name ).val( i ) ;
			}
		}).sortable({
			update: function( event, ui ) {
				var order = []; 
				$('#drag-drop-area .widget').each( function( e ) {
					var element_id = $( this ).attr('id') ;
					var input_name = 'input[name="surveyval\[' + element_id +'\]\[sort\]"]';
					var index = $( this ).index();
              		$( input_name ).val( index ) ;
              	});
			}
		});
	});
}(jQuery));