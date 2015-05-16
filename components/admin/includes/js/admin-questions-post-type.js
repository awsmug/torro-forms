(function ($) {
	"use strict";
	$( function () {
	    
	    /**
	     * Making elements draggable
	     */
		$( "#survey-elements .surveyelement" ).draggable( { 
			helper: 'clone',
			cursor: "move",
			connectToSortable: "#drag-drop-inside",
			addClasses: false,
			start: function( event, ui ) {
		        ui.helper.css( 'height', 'auto' ).css( 'width', '100px' );
		    },
		    stop: function( event, ui ) {
		        ui.helper.css( 'width', '100%' ).css( 'height', 'auto' );
		    }
		});
		
		/**
		 * Setting up droppable and sortable areas
		 */
		$( "#drag-drop-inside" ).droppable({
			accept: "#survey-elements .surveyelement",
			drop: function( event, ui ) {
			}
		}).sortable({
			placeholder: 'survey-element-placeholder',
			items:'.surveyelement',
			receive: function( event, ui ){
				var nr = questions_rand();
				
				ui.helper.attr( 'id', 'widget_surveyelement_' + nr );
				ui.helper.html( ui.helper.html().replace( /##nr##/g, nr ) );
				
				var i = 0;
				$( '#drag-drop-inside .surveyelement' ).each( function( e ) { i++; });
				
				var input_name = 'input[name="questions\[widget_surveyelement_' + nr +'\]\[sort\]"]';
              	$( input_name ).val( i ) ;
				
				questions_answersortable();
              	questions_delete_surveyelement();
              	questions_deleteanswer();
              	questions_rewriteheadline();
              	questions_survey_element_tabs();
			},
			update: function( event, ui ) {
				var order = []; 
				$( '#drag-drop-inside .surveyelement' ).each( function( e ) {
					var element_id = $( this ).attr('id') ;
					var input_name = 'input[name="questions\[' + element_id +'\]\[sort\]"]';
					var index = $( this ).index();
              		$( input_name ).val( index ) ;
              	});
			}
		});
		
		/**
         * Deleting survey element
         */
        var questions_delete_surveyelement = function(){
            var questions_delete_surveyelement_dialog = $( '#delete_surveyelement_dialog' );
            var surveyelement_id;
            var deleted_surveyelements;
            
            questions_delete_surveyelement_dialog.dialog({                   
                'dialogClass'   : 'wp-dialog',           
                'modal'         : true,
                'autoOpen'      : false, 
                'closeOnEscape' : true,
                'minHeight'     : 80,
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
                questions_delete_surveyelement_dialog.dialog( 'open' );
            });
        }
        questions_delete_surveyelement();
		
		/**
		 * Making answers in questions sortable
		 */
		var questions_answersortable = function (){
			$( "#drag-drop-inside .answers" ).sortable({
				update: function(  event, ui ){
	
					var element_id = $( this ).closest( '.widget' ).attr('id');
					var order = []; 
					
					$( this ).find( '.answer' ).each( function( e ) {
						var nr = $( this ).attr( 'id' );
						nr = nr.split( '_' );
						nr = nr[1];
						
						var input_name = 'input[name="questions\[' + element_id + '\]\[answers\]\[id_' + nr + '\]\[sort\]"]';
						var index = $( this ).index();
	              		$( input_name ).val( index ) ;
	              	});
				},
				items:'.answer'
			});
		}
		questions_answersortable();
		
		
		
		
		var questions_deleteanswer = function(){
			var questions_deletanswerdialog = $( '#delete_answer_dialog' );
			var answer_id;
			var deleted_answers;
			
			questions_deletanswerdialog.dialog({                   
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
		        questions_deletanswerdialog.dialog( 'open' );
			});
		}
		questions_deleteanswer();
		
		/**
		 * Adding answer to element
		 */
		var questions_add_answer = function(){
			$( "#drag-drop-inside" ).on( 'click', '.add-answer', function(){
				
				var element_id = $( this ).attr( 'rel' );
				var nr = questions_rand();
				
				var preset_is_multiple = 'input[name="questions\[' + element_id + '\]\[preset_is_multiple\]"]';
				var preset_is_multiple = $( preset_is_multiple ).val();
				
				var multiple_class = '';
				if( preset_is_multiple == 'yes' ) multiple_class = ' preset_is_multiple';
				
				var sections = 'input[name="questions\[' + element_id + '\]\[sections\]"]';
				var sections = $( sections ).val();
				
				var answer_content = '';
				answer_content = '<div class="answer' + multiple_class + '" id="answer_##nr##">';
				answer_content = answer_content + '<p><input type="text" id="answer_##nr##_input" name="questions[' + element_id + '][answers][id_##nr##][answer]" /></p>';
				answer_content = answer_content + '<input type="hidden" name="questions[' + element_id + '][answers][id_##nr##][id]" /><input type="hidden" name="questions[' + element_id + '][answers][id_##nr##][sort]" />';
				
				if( 'yes' == sections ){
					var section_key = $( this ).parent().find( 'input[name="section_key"]' ).val();
					answer_content = answer_content + '<input type="hidden" name="questions[' + element_id + '][answers][id_##nr##][section]" value="' + section_key + '" />';
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
				
				var answer_input = $( "#answer_" + nr + "_input" );
				answer_input.focus();
				
				// Adding sorting number
				var input_name = 'input[name="questions\[' + element_id + '\]\[answers\]\[id_' + nr + '\]\[sort\]"]';
	          	$( input_name ).val( order ) ;
	          	
	          	questions_deleteanswer();
			});
		}
		questions_add_answer();
		
		/**
		 * Members - Participiants restrictions select
		 */
		var questions_participiants_restrictions_select = $( "#questions-participiants-restrictions-select" ).val();
		$( "#questions_selected_members" ).hide();
		
		if( 'selected_members' == questions_participiants_restrictions_select ){ $( "#questions_selected_members" ).show(); }
		
		$( "#questions-participiants-restrictions-select" ).change( function(){
			questions_participiants_select = $( "#questions-participiants-restrictions-select" ).val();
			
			if( 'selected_members' == questions_participiants_select ){
				$( "#questions_selected_members" ).show();
			}else{
				$( "#questions_selected_members" ).hide();
			}
		});
		
		/**
		 * Members - Member select
		 */
		var questions_participiants_select = $( "#questions-participiants-select" ).val();
		
		if( 'all_members' != questions_participiants_select ){
			$( "#questions-participiants-standard-options" ).hide();
		}
		
		$( "#questions-participiants-select" ).change( function(){
			questions_participiants_select = $( "#questions-participiants-select" ).val();
			
			if( 'all_members' == questions_participiants_select ){
				$( "#questions-participiants-standard-options" ).show();
			}else{
				$( "#questions-participiants-standard-options" ).hide();
			}
		});
		
		var questions_participiants = $( "#questions-participiants" ).val();
		
		if( '' == questions_participiants ){
			$( "#questions-participiants-list" ).hide();
		}
		
		/**
		 * Members - Adding Participiants
		 */
		$.questions_add_participiants = function( response ){
			var questions_participiants_old = $( "#questions-participiants" ).val();
			questions_participiants_old = questions_participiants_old.split( ',' );
			var questions_participiants = questions_participiants_old;
			var count_added_participiants = 0;
			
			$.each( response, function( i, object ) {
				var found = false;
				
				if( in_array( object.id, questions_participiants_old ) ){
					found = true;
				}
				
				if( false == found ){
					if( '' == questions_participiants ){
						questions_participiants =  object.id;
					}else{
						questions_participiants = questions_participiants + ',' + object.id;
					}
					$( "#questions-participiants-list tbody" ).append( '<tr class="participiant participiant-user-' + object.id + ' just-added"><td>' + object.id + '</td><td>' + object.user_nicename + '</td><td>' + object.display_name + '</td><td>' + object.user_email + '</td><td>' + translation_admin.just_added + '</td><td><a class="button questions-delete-participiant" rel="' + object.id +  '">' + translation_admin.delete + '</a></td></tr>' );
					count_added_participiants++;
				}
			});
			
			var count_participiants = parseInt( $( "#questions-participiants-count" ).val() ) + count_added_participiants;
			
			$( "#questions-participiants" ).val( questions_participiants );
			$.questions_participiants_counter( count_participiants );
			$( "#questions-participiants-list" ).show();
			$.questions_delete_participiant();	
		}
		
		/**
         * Adding all existing members to participiants list
         */
        $( "#questions-add-members-standard" ).click( function(){
            
            var data = {
                action: 'questions_add_members_standard'
            };
            
            var button = $( this )
            button.addClass( 'button-loading' );
        
            $.post( ajaxurl, data, function( response ) {
                response = jQuery.parseJSON( response );
                $.questions_add_participiants( response );
                button.removeClass( 'button-loading' );
            });
        });
		
		/**
		 * Counting participiants
		 */
		$.questions_participiants_counter = function( number ){
			var text = number + ' ' + translation_admin.added_participiants;
			$( "#questions-participiants-status p").html( text );
			$( "#questions-participiants-count" ).val( number );
		}
		
		/**
		 * Removing participiant from list
		 */
		$.questions_delete_participiant = function(){
			$( ".questions-delete-participiant" ).click( function(){
				var delete_user_id = $( this ).attr( 'rel' );
				
				var questions_participiants_new = '';
				
				var questions_participiants = $( "#questions-participiants" ).val();
				questions_participiants = questions_participiants.split( "," );
				
				$.each( questions_participiants, function( key, value ) {
					if( value != delete_user_id ){
						if( '' == questions_participiants_new ){
							questions_participiants_new = value;
						}else{
							questions_participiants_new = questions_participiants_new + ',' + value;
						}
					}
				});
				
				if( '' == questions_participiants_new ){
			    	$( "#questions-participiants-list" ).hide();
			    }
				
				$( "#questions-participiants" ).val( questions_participiants_new );
				$.questions_participiants_counter( $( "#questions-participiants-count" ).val() - 1 );
				$( ".participiant-user-" + delete_user_id ).remove();
			});
		}
		$.questions_delete_participiant();
		
		/**
		 * Removing all Participiants from list
		 */
		$( ".questions-remove-all-participiants" ).click( function(){
			$( "#questions-participiants" ).val( '' );
			$( "#questions-participiants-list tbody tr" ).remove();
		});
		
		/**
		 * Invite participiants
		 */
		$( '#questions-invite-button' ).click( function(){
			
			var button = $( this )
			
			if( button.hasClass( 'button-primary' ) ){
				var data = {
					action: 'questions_invite_participiants',
					invitation_type: 'invite',
					survey_id: $( '#post_ID' ).val(),
					subject_template: $( '#questions-invite-subject' ).val(),
					text_template: $( '#questions-invite-text' ).val()
				};
				
				button.addClass( 'button-loading' );
				
				$.post( ajaxurl, data, function( response ) {
					response = jQuery.parseJSON( response );
					if( response.sent ){
						$( '#questions-invite-subject' ).fadeOut( 200 );
						$( '#questions-invite-text' ).fadeOut( 200 );
						$( '#questions-invite-text' ).after( '<p class="survey-reinvitations-sent">' + translation_admin.invitations_sent_successfully + '</p>' );
					}else{
						$( '#questions-invite-subject' ).fadeOut( 200 );
						$( '#questions-invite-text' ).fadeOut( 200 );
						$( '#questions-invite-text' ).after( '<p class="survey-reinvitations-sent">' + translation_admin.invitations_sent_not_successfully + '</p>' );
					}
					button.removeClass( 'button-loading' );

					$( '.survey-reinvitations-sent' ).fadeOut( 4000 );
					$( '#questions-invite-button' ).removeClass( 'button-primary' );
					$( '#questions-invite-text' ).fadeOut( 200 );
					$( '#questions-invite-button-cancel' ).fadeOut( 200 );
				});
				
			}else{
				button.addClass( 'button-primary' );
				$( '#questions-invite-subject' ).fadeIn( 200 );
				$( '#questions-invite-text' ).fadeIn( 200 );
				$( '#questions-invite-button-cancel' ).fadeIn( 200 );
			}
		});
		
		$( '#questions-invite-button-cancel' ).click( function(){
			$( '#questions-invite-button' ).removeClass( 'button-primary' );
			$( '#questions-invite-subject' ).fadeOut( 200 );
			$( '#questions-invite-text' ).fadeOut( 200 );
			$( '#questions-invite-button-cancel' ).fadeOut( 200 );
		});
		
		$( '#questions-reinvite-button' ).click( function(){
			var button = $( this )
			
			if( button.hasClass( 'button-primary' ) ){
				var data = {
					action: 'questions_invite_participiants',
					invitation_type: 'reinvite',
					survey_id: $( '#post_ID' ).val(),
					subject_template: $( '#questions-reinvite-subject' ).val(),
					text_template: $( '#questions-reinvite-text' ).val()
				};
				
				button.addClass( 'button-loading' );
				
				$.post( ajaxurl, data, function( response ) {
					response = jQuery.parseJSON( response );
					if( response.sent ){
						$( '#questions-reinvite-subject' ).fadeOut( 200 );
						$( '#questions-reinvite-text' ).fadeOut( 200 );
						$( '#questions-reinvite-text' ).after( '<p class="survey-reinvitations-sent">' + translation_admin.reinvitations_sent_successfully + '</p>' );
						button.removeClass( 'button-loading' );
						$( '.survey-reinvitations-sent' ).fadeOut( 4000 );
					}else{
						$( '#questions-reinvite-subject' ).fadeOut( 200 );
						$( '#questions-reinvite-text' ).fadeOut( 200 );
						$( '#questions-reinvite-text' ).after( '<p class="survey-reinvitations-sent">' + translation_admin.reinvitations_sent_not_successfully + '</p>' );
						
					}
					button.removeClass( 'button-loading' );
					$( '.survey-reinvitations-sent' ).fadeOut( 4000 );
					$( '#questions-reinvite-button' ).removeClass( 'button-primary' );
					$( '#questions-reinvite-text' ).fadeOut( 200 );
					$( '#questions-reinvite-button-cancel' ).fadeOut( 200 );
				});
				
			}else{
				button.addClass( 'button-primary' );
				$( '#questions-reinvite-subject' ).fadeIn( 200 );
				$( '#questions-reinvite-text' ).fadeIn( 200 )
				$( '#questions-reinvite-button-cancel' ).fadeIn( 200 )
			}
		});
		
		$( '#questions-reinvite-button-cancel' ).click( function(){
			$( '#questions-reinvite-button' ).removeClass( 'button-primary' );
			$( '#questions-reinvite-subject' ).fadeOut( 200 );
			$( '#questions-reinvite-text' ).fadeOut( 200 );
			$( '#questions-reinvite-button-cancel' ).fadeOut( 200 );
		});
		
		/**
		 * Dublicate survey
		 */
		$( '#questions-duplicate-button' ).click( function(){
			var button = $( this )
			
			if( button.hasClass( 'button' ) ){
				var data = {
					action: 'questions_duplicate_survey',
					survey_id: $( '#post_ID' ).val(),
				};
				
				button.addClass( 'button-loading' );
				
				$.post( ajaxurl, data, function( response ) {
					response = jQuery.parseJSON( response );
					
					var response_text = translation_admin.duplicate_survey_successfully + '<br /><a href="' + response.admin_url + '">' + translation_admin.edit_survey + '</a>';
					button.after( '<p class="survey-duplicated-survey">' + response_text + '</p>' );
					button.removeClass( 'button-loading' );
					
					$( '.survey-duplicated-survey' ).fadeOut( 20000 );
				});
				
			}else{
				button.addClass( 'button' );
			}
		});
		
		/**
		 * Counting all input fields of a selected container
		 */
		var count_form_elements = function( selector ){
		  var count_inputs = $( selector ).find( 'input' ).length;
		  var count_textareas = $( selector ).find( 'textarea' ).length;
		  var count_select = $( selector ).find( 'select' ).length;
		  
		  var count_all = count_inputs + count_textareas + count_select;
		  
		  return count_all;
		}
		
		/**
		 * Counting form input vars and showing 
		 */
		var check_max_input_vars = function(){
		    var max_input_vars =  parseInt( $( "#max_input_vars" ).val() );
		    var input_vars = parseInt( count_form_elements( '#post' ) );
		    var alert_zone = 50; // The alert will start the alert X before max_input_vars have been reached 
		    
		    var msg_near_limit = '<strong>' + translation_admin.max_fields_near_limit + '</strong><br /> ' + translation_admin.max_fields_todo;
		    var msg_over_limit = '<strong>' + translation_admin.max_fields_over_limit + '</strong><br /> ' + translation_admin.max_fields_todo;
		    
		    console.log( 'Input vars: ' + input_vars );
		    console.log( 'Max input vars: ' + max_input_vars );
		    
		    if( input_vars + alert_zone >= max_input_vars ){
		        $( "#questions-messages" )
		          .removeClass( 'notice error updated' )
		          .addClass( 'notice' )
		          .html( '<p>' +  msg_near_limit + '</p>' )
		          .show();
            }
            
            if( input_vars >= max_input_vars ){
                $( "#questions-messages" )
                  .removeClass( 'notice error updated' )
                  .addClass( 'error' )
                  .html( '<p>' +  msg_over_limit + '</p>' )
                  .show();
            }
		}
		check_max_input_vars();
		
		/**
         * Initializing jquery tabs in elements
         */
        var questions_survey_element_tabs = function(){
            $( ".survey_element_tabs" ).tabs({ active: 0 });
        }
        questions_survey_element_tabs();
		
		/**
		 * Live typing of element headline
		 */
		var questions_rewriteheadline = function(){
            $( ".questions-question" ).on( 'input', function(){
                var element_id = $( this ).closest( '.widget' ).attr('id');
                $( "#" +element_id + " .widget-title h4" ).text( $( this ).val() );
            });
        }
        questions_rewriteheadline();
		
		/**
		 * Helper function - Getting a random number
		 */
		function questions_rand(){
			var now = new Date();
			var random = Math.floor(Math.random() * ( 10000 - 10 + 1)) + 10;
			return random * now.getTime();
		}
		
		/**
         * Helper function - JS recreating of PHP in_array function
         */
		function in_array( needle, haystack ) {
		    var length = haystack.length;
		    for(var i = 0; i < length; i++) {
		        if(haystack[i] == needle) return true;
		    }
		    return false;
		}
	});
}(jQuery));