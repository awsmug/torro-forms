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
				
				// Replacing ##nr## for getting unique ids & setting up container ID
				var nr = surveyval_rand();
				var draggable_content =  ui.draggable.html();
				draggable_content = draggable_content.replace( /##nr##/g, nr );
				
				// Counting elements
				var i = 0;
				$('#drag-drop-area .widget').each( function( e ) { i++; });
				
              	var droppable_helper = $( this ).find( ".drag-drop-inside" ).html();
              	$( this ).find( ".drag-drop-inside" ).remove();
				$( draggable_content ).appendTo( this );
				$( '<div class="drag-drop-inside">' + droppable_helper + '</div>' ).appendTo( this );
				
				// Adding sorting number
				var input_name = 'input[name="surveyval\[widget_question_' + nr +'\]\[sort\]"]';
              	$( input_name ).val( i ) ;
              	
              	answersortable();
              	surveyval_rewriteheadline();
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
			},
			items:'.widget'
		});
		
		var answersortable = function (){
			$( "#drag-drop-area .answers" ).sortable({
				update: function(  event, ui ){
					console.log( $( this ) );
	
					var element_id = $( this ).closest( '.widget' ).attr('id');
					var order = []; 
					
					console.log( element_id );
					
					$( this ).find( '.answer' ).each( function( e ) {
						var nr = $( this ).attr( 'id' );
						nr = nr.split( '_' );
						nr = nr[1];
						
						var input_name = 'input[name="surveyval\[' + element_id + '\]\[answers\]\[id_' + nr + '\]\[sort\]"]';
						var index = $( this ).index();
	              		$( input_name ).val( index ) ;
	              	});
				},
				items:'.answer'
			});
		}
		answersortable();
		
		var surveyval_rewriteheadline = function(){
			$( ".surveyval-question" ).on( 'input', function(){
				var element_id = $( this ).closest( '.widget' ).attr('id');
				$( "#" +element_id + " .widget-title h4" ).text( $( this ).val() );
			});
		}
		surveyval_rewriteheadline();
		
		
		$( "#drag-drop-area" ).on( 'click', '.add-answer', function(){
			var element_id = $( this ).attr( 'rel' );
			var nr = surveyval_rand();
			
			var answer_content = '<div class="answer" id="answer_##nr##"><p><input type="text" name="surveyval[' + element_id + '][answers][id_##nr##][answer]" /></p><input type="hidden" name="surveyval[' + element_id + '][answers][id_##nr##][id]" /><input type="hidden" name="surveyval[' + element_id + '][answers][id_##nr##][sort]" /></div>';
			answer_content = answer_content.replace( /##nr##/g, nr );
			
			var order = 0;
			$( this ).parent().find( '.answer' ).each( function( e ) { order++; });
			
			$( answer_content ).appendTo( "#" + element_id + " .answers" );
			
			// Adding sorting number
			var input_name = 'input[name="surveyval\[' + element_id + '\]\[answers\]\[id_' + nr + '\]\[sort\]"]';
          	$( input_name ).val( order ) ;
		});
		
			
		function surveyval_rand(){
			var now = new Date();
			var random = Math.floor(Math.random() * ( 10000 - 10 + 1)) + 10;
			return random * now.getTime();
		}
		
		// $( ".drag-drop-inside" ).disableSelection();
	});
}(jQuery));