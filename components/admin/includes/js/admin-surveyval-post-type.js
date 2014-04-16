(function ($) {
	"use strict";
	$( function () {
		$( ".surveyval-draggable" ).draggable( { 
			helper: "clone",
			cursor: "move",
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
				var input_name = 'input[name="surveyval\[widget_surveyelement_' + nr +'\]\[sort\]"]';
              	$( input_name ).val( i ) ;
              	
              	surveyval_answersortable();
              	surveyval_delete_surveyelement()
              	surveyval_deleteanswer();
              	surveyval_rewriteheadline();
              	surveyval_survey_element_tabs();
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
		
		var surveyval_answersortable = function (){
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
		surveyval_answersortable();
		
		
		var surveyval_delete_surveyelement = function(){
			var surveyval_delete_surveyelement_dialog = $( '#delete_surveyelement_dialog' );
			var surveyelement_id;
			var deleted_surveyelements;
			
			surveyval_delete_surveyelement_dialog.dialog({                   
		        'dialogClass'   : 'wp-dialog',           
		        'modal'         : true,
		        'autoOpen'      : false, 
		        'closeOnEscape' : true,
		        'minHeight'		: 80,
		        'buttons'       : [{
						text: translation_admin.yes,
						click: function() {
								surveyelement_id = surveyelement_id.split( '_' );
								surveyelement_id = surveyelement_id[2];
								
								deleted_surveyelements = $( '#deleted_surveyelements' ).val();
								
								if( '' == deleted_surveyelements )
									deleted_surveyelements += surveyelement_id;
								else
									deleted_surveyelements += ',' + surveyelement_id;
									
								$( '#deleted_surveyelements' ).val( deleted_surveyelements );
								$( '#widget_surveyelement_' + surveyelement_id ).remove();
								
				                $( this ).dialog('close');
							}
						},
						{
						text: translation_admin.no,
						click: function() {
							
							$( this ).dialog( "close" );
							}
						},
					],
					
		    });
			
			$( '.delete_survey_element' ).click( function( event ){
				surveyelement_id = $( this ).closest( '.surveyelement' ).attr('id');
		        event.preventDefault();
		        surveyval_delete_surveyelement_dialog.dialog( 'open' );
			});
		}
		surveyval_delete_surveyelement();
		
		var surveyval_deleteanswer = function(){
			var surveyval_deletanswerdialog = $( '#delete_answer_dialog' );
			var answer_id;
			var deleted_answers;
			
			surveyval_deletanswerdialog.dialog({                   
		        'dialogClass'   : 'wp-dialog',           
		        'modal'         : true,
		        'autoOpen'      : false, 
		        'closeOnEscape' : true,
		        'minHeight'		: 80,
		        'buttons'       : [{
						text: translation_admin.yes,
						click: function() {
								answer_id = answer_id.split( '_' );
								answer_id = answer_id[1];
								
								deleted_answers = $( '#deleted_answers' ).val();
								
								if( '' == deleted_answers )
									deleted_answers += answer_id;
								else
									deleted_answers += ',' + answer_id;
									
								$( '#deleted_answers' ).val( deleted_answers );
								$( '#answer_' + answer_id ).remove();
								
				                $( this ).dialog('close');
							}
						},
						{
						text: translation_admin.no,
						click: function() {
							
							$( this ).dialog( "close" );
							}
						},
					],
					
		    });
			
			$( '.delete_answer' ).click( function( event ){
				answer_id = $( this ).closest( '.answer' ).attr('id');
		        event.preventDefault();
		        surveyval_deletanswerdialog.dialog( 'open' );
			});
		}
		surveyval_deleteanswer();
		
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
			
			var preset_is_multiple = 'input[name="surveyval\[' + element_id + '\]\[preset_is_multiple\]"]';
			var preset_is_multiple = $( preset_is_multiple ).val();
			
			var multiple_class = '';
			if( preset_is_multiple == 'yes' ) multiple_class = ' preset_is_multiple';
			
			var sections = 'input[name="surveyval\[' + element_id + '\]\[sections\]"]';
			var sections = $( sections ).val();
			
			var answer_content = '';
			answer_content = '<div class="answer' + multiple_class + '" id="answer_##nr##">';
			answer_content = answer_content + '<p><input type="text" name="surveyval[' + element_id + '][answers][id_##nr##][answer]" /></p>';
			answer_content = answer_content + '<input type="hidden" name="surveyval[' + element_id + '][answers][id_##nr##][id]" /><input type="hidden" name="surveyval[' + element_id + '][answers][id_##nr##][sort]" />';
			
			console.log( element_id );
			console.log( sections );
			
			if( 'yes' == sections ){
				var section_key = $( this ).parent().find( 'input[name="section_key"]' ).val();
				answer_content = answer_content + '<input type="hidden" name="surveyval[' + element_id + '][answers][id_##nr##][section]" value="' + section_key + '" />';
			}
			answer_content = answer_content + ' <input type="button" value="' + translation_admin.delete + '" class="delete_answer button answer_action"></div>';
							
			answer_content = answer_content.replace( /##nr##/g, nr );
			
			var order = 0;
			$( this ).parent().find( '.answer' ).each( function( e ) { order++; });
			
			if( 'yes' == sections ){
				$( answer_content ).appendTo( "#" + element_id + " #section_" + section_key + " .answers" );
			}else{
				$( answer_content ).appendTo( "#" + element_id + " .answers" );
			}
			
			// Adding sorting number
			var input_name = 'input[name="surveyval\[' + element_id + '\]\[answers\]\[id_' + nr + '\]\[sort\]"]';
          	$( input_name ).val( order ) ;
          	
          	surveyval_deleteanswer();
		});
		
		var surveyval_survey_element_tabs = function(){
			$( ".survey_element_tabs" ).tabs({ active: 0 });
		}
		surveyval_survey_element_tabs();
		
		var surveyval_participiants_select = $( "#surveyval-participiants-select" ).val();
		
		if( 'all_members' != surveyval_participiants_select ){
			$( "#surveyval-participiants-standard-options" ).hide();
		}
		
		$( "#surveyval-participiants-select" ).change( function(){
			surveyval_participiants_select = $( "#surveyval-participiants-select" ).val();
			console.log( surveyval_participiants_select );
			
			if( 'all_members' == surveyval_participiants_select ){
				$( "#surveyval-participiants-standard-options" ).show();
			}else{
				$( "#surveyval-participiants-standard-options" ).hide();
			}
		});
		
		var surveyval_participiants = $( "#surveyval-participiants" ).val();
		
		if( '' == surveyval_participiants ){
			$( "#surveyval-participiants-list" ).hide();
		}
		
		$.surveyval_add_participiants = function( response ){
			
			var surveyval_participiants_old = $( "#surveyval-participiants" ).val();
			surveyval_participiants_old = surveyval_participiants_old.split( ',' );
			
			$.each( response, function( i, object ) {
				var found = false;
				
				$.each( surveyval_participiants_old, function( key, value ) {
					if( value == object.id ){
						found = true;
					}
				});
				
				if( !found ){
					if( '' == surveyval_participiants ){
						surveyval_participiants =  object.id;
					}else{
						surveyval_participiants = surveyval_participiants + ',' + object.id;
					}
					$( "#surveyval-participiants-list tbody" ).append( '<tr class="participiant participiant-user-' + object.id + ' just-added"><td>' + object.id + '</td><td>' + object.user_nicename + '</td><td>' + object.display_name + '</td><td>' + object.user_email + '</td><td>' + translation_admin.just_added + '</td><td><a class="button surveyval-delete-participiant" rel="' + object.id +  '">' + translation_admin.delete + '</a></td></tr>' );
				}	
				
			    $( "#surveyval-participiants" ).val( surveyval_participiants );
			    
				$.surveyval_delete_participiant();
			});
			
			$( "#surveyval-participiants-list" ).show();			
		}
		
		$.surveyval_delete_participiant = function(){
			$( ".surveyval-delete-participiant" ).click( function(){
				var delete_user_id = $( this ).attr( 'rel' );
				
				var surveyval_participiants_new = '';
				
				var surveyval_participiants = $( "#surveyval-participiants" ).val();
				surveyval_participiants = surveyval_participiants.split( "," );
				
				$.each( surveyval_participiants, function( key, value ) {
					if( value != delete_user_id ){
						if( '' == surveyval_participiants_new ){
							surveyval_participiants_new = value;
						}else{
							surveyval_participiants_new = surveyval_participiants_new + ',' + value;
						}
					}
				});
				
				if( '' == surveyval_participiants_new ){
			    	$( "#surveyval-participiants-list" ).hide();
			    }
				
				$( "#surveyval-participiants" ).val( surveyval_participiants_new );
				$( ".participiant-user-" + delete_user_id ).remove();
			});
		}
		$.surveyval_delete_participiant();
		
		$( "#surveyval-add-members-standard" ).click( function(){
			
			var data = {
				action: 'surveyval_add_members_standard'
			};
		
			$.post( ajaxurl, data, function( response ) {
				response = jQuery.parseJSON( response );
				$.surveyval_add_participiants( response );
			});
		});
		
		
		$( ".surveyval-remove-all-participiants" ).click( function(){
			$( "#surveyval-participiants" ).val( '' );
			$( "#surveyval-participiants-list tbody tr" ).remove();
		});
		
		$( '#surveyval-invite-button' ).click( function(){
			
			var button = $( this )
			
			if( button.hasClass( 'button-primary' ) ){
				var data = {
					action: 'surveyval_invite_participiants',
					invitation_type: 'invite',
					survey_id: $( '#post_ID' ).val(),
					text_template: $( '#surveyval-invite-text' ).val()
				};
				
				button.addClass( 'button-loading' );
				
				$.post( ajaxurl, data, function( response ) {
					response = jQuery.parseJSON( response );
					if( response.sent ){
						$( '#surveyval-invite-text' ).fadeOut( 200 );
						$( '#surveyval-invite-text' ).after( '<p class="survey-reinvitations-sent">' + translation_admin.invitations_sent_successfully + '</p>' );
					}else{
						$( '#surveyval-invite-text' ).fadeOut( 200 );
						$( '#surveyval-invite-text' ).after( '<p class="survey-reinvitations-sent">' + translation_admin.invitations_sent_not_successfully + '</p>' );
					}
					button.removeClass( 'button-loading' );
					$( '.survey-reinvitations-sent' ).fadeOut( 4000 );
					$( '#surveyval-invite-button' ).removeClass( 'button-primary' );
					$( '#surveyval-invite-text' ).fadeOut( 200 );
					$( '#surveyval-invite-button-cancel' ).fadeOut( 200 );
				});
				
			}else{
				button.addClass( 'button-primary' );
				$( '#surveyval-invite-text' ).fadeIn( 200 );
				$( '#surveyval-invite-button-cancel' ).fadeIn( 200 );
			}
		});
		
		$( '#surveyval-invite-button-cancel' ).click( function(){
			$( '#surveyval-invite-button' ).removeClass( 'button-primary' );
			$( '#surveyval-invite-text' ).fadeOut( 200 );
			$( '#surveyval-invite-button-cancel' ).fadeOut( 200 );
		});
		
		$( '#surveyval-reinvite-button' ).click( function(){
			var button = $( this )
			
			if( button.hasClass( 'button-primary' ) ){
				var data = {
					action: 'surveyval_invite_participiants',
					invitation_type: 'reinvite',
					survey_id: $( '#post_ID' ).val(),
					text_template: $( '#surveyval-reinvite-text' ).val()
				};
				
				button.addClass( 'button-loading' );
				
				$.post( ajaxurl, data, function( response ) {
					response = jQuery.parseJSON( response );
					if( response.sent ){
						$( '#surveyval-reinvite-text' ).fadeOut( 200 );
						$( '#surveyval-reinvite-text' ).after( '<p class="survey-reinvitations-sent">' + translation_admin.reinvitations_sent_successfully + '</p>' );
						button.removeClass( 'button-loading' );
						$( '.survey-reinvitations-sent' ).fadeOut( 4000 );
					}else{
						$( '#surveyval-reinvite-text' ).fadeOut( 200 );
						$( '#surveyval-reinvite-text' ).after( '<p class="survey-reinvitations-sent">' + translation_admin.reinvitations_sent_not_successfully + '</p>' );
						
					}
					button.removeClass( 'button-loading' );
					$( '.survey-reinvitations-sent' ).fadeOut( 4000 );
					$( '#surveyval-reinvite-button' ).removeClass( 'button-primary' );
					$( '#surveyval-reinvite-text' ).fadeOut( 200 );
					$( '#surveyval-reinvite-button-cancel' ).fadeOut( 200 );
				});
				
			}else{
				button.addClass( 'button-primary' );
				$( '#surveyval-reinvite-text' ).fadeIn( 200 )
				$( '#surveyval-reinvite-button-cancel' ).fadeIn( 200 )
			}
			
		});
		
		$( '#surveyval-reinvite-button-cancel' ).click( function(){
			$( '#surveyval-reinvite-button' ).removeClass( 'button-primary' );
			$( '#surveyval-reinvite-text' ).fadeOut( 200 );
			$( '#surveyval-reinvite-button-cancel' ).fadeOut( 200 );
		});
			
		function surveyval_rand(){
			var now = new Date();
			var random = Math.floor(Math.random() * ( 10000 - 10 + 1)) + 10;
			return random * now.getTime();
		}
		
		// $( ".drag-drop-inside" ).disableSelection();
	});
}(jQuery));