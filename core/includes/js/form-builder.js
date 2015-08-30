(function ($) {
	"use strict";
	$( function () {
	    
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
            
            var msg_near_limit = '<strong>' + translation_fb.max_fields_near_limit + '</strong> (' + input_vars + ' ' + translation_fb.of + ' ' + max_input_vars + ')<br /> ' + translation_fb.max_fields_todo;
            var msg_over_limit = '<strong>' + translation_fb.max_fields_over_limit + '</strong> (' + input_vars + ' ' + translation_fb.of + ' ' + max_input_vars + ')<br /> ' + translation_fb.max_fields_todo;
            
            // console.log( 'Max input vars: ' + max_input_vars );
            // console.log( 'Input vars: ' + input_vars );
            
            if( input_vars + alert_zone >= max_input_vars ){
                $( "#form-messages" )
                  .removeClass( 'notice error updated' )
                  .addClass( 'notice' )
                  .html( '<p>' +  msg_near_limit + '</p>' )
                  .show();
            }
            
            if( input_vars >= max_input_vars ){
                $( "#form-messages" )
                  .removeClass( 'notice error updated' )
                  .addClass( 'error' )
                  .html( '<p>' +  msg_over_limit + '</p>' )
                  .show();
            }
        }
        check_max_input_vars();
	    
	    /**
	     * Making elements draggable
	     */
		$( "#form-elements .formelement" ).draggable( {
			helper: 'clone',
			cursor: "move",
			connectToSortable: "#drag-drop-inside",
			addClasses: false,
			start: function( event, ui ) {
		        ui.helper.css( 'height', 'auto' ).css( 'width', '100px' );
		    },
		    stop: function( event, ui ) {
		        ui.helper.css( 'width', '100%' ).css( 'height', 'auto' );
				ui.helper.addClass( 'widget' );
		    }
		});
		
		/**
		 * Setting up droppable and sortable areas
		 */
		$( "#drag-drop-inside" ).droppable({
			accept: "#form-elements .formelement",
			drop: function( event, ui ) {
			}
		}).sortable({
			placeholder: 'form-element-placeholder',
			items:'.formelement',
			receive: function( event, ui ){
				var nr = af_rand();
				
				ui.helper.attr( 'id', 'widget_formelement_' + nr );
				ui.helper.html( ui.helper.html().replace( /XXnrXX/g, nr ) );
				
				var i = 0;
				$( '#drag-drop-inside .formelement' ).each( function( e ) { i++; });
				
				var input_name = 'input[name="elements\[widget_formelement_' + nr +'\]\[sort\]"]';
              	$( input_name ).val( i ) ;
				
				af_answersortable();
              	af_delete_formelement();
              	af_deleteanswer();
              	af_rewriteheadline();
              	af_element_tabs();
              	check_max_input_vars();
			},
			update: function( event, ui ) {
				var order = []; 
				$( '#drag-drop-inside .formelement' ).each( function( e ) {
					var element_id = $( this ).attr('id') ;
					var input_name = 'input[name="elements\[' + element_id +'\]\[sort\]"]';
					var index = $( this ).index();
              		$( input_name ).val( index ) ;
              	});
			}
		});
		
		/**
         * Deleting form element
         */
        var af_delete_formelement = function(){
            var af_delete_formelement_dialog = $( '#delete_formelement_dialog' );
            var formelement_id;
            var deleted_formelements;
            
            af_delete_formelement_dialog.dialog({
                'dialogClass'   : 'wp-dialog',           
                'modal'         : true,
                'autoOpen'      : false, 
                'closeOnEscape' : true,
                'minHeight'     : 80,
                'buttons'       : [{
                        text: translation_fb.yes,
                        click: function() {
                                formelement_id = formelement_id.split( '_' );
                                formelement_id = formelement_id[2];
                                
                                deleted_formelements = $( '#deleted_formelements' ).val();
                                
                                if( '' == deleted_formelements )
                                    deleted_formelements += formelement_id;
                                else
                                    deleted_formelements += ',' + formelement_id;
                                    
                                $( '#deleted_formelements' ).val( deleted_formelements );
                                $( '#widget_formelement_' + formelement_id ).remove();
                                
                                $( this ).dialog('close');
                            }
                        },
                        {
                        text: translation_fb.no,
                        click: function() {
                            
                            $( this ).dialog( "close" );
                            }
                        },
                    ],
                    
            });
            
            $( '.delete_form_element' ).click( function( event ){
                formelement_id = $( this ).closest( '.formelement' ).attr('id');
                event.preventDefault();
                af_delete_formelement_dialog.dialog( 'open' );
            });
            check_max_input_vars();
        }
        af_delete_formelement();
		
		/**
		 * Making answers in form sortable
		 */
		var af_answersortable = function (){
			$( "#drag-drop-inside .answers" ).sortable({
				update: function(  event, ui ){
	
					var element_id = $( this ).closest( '.widget' ).attr('id');
					var order = []; 
					
					$( this ).find( '.answer' ).each( function( e ) {
						var nr = $( this ).attr( 'id' );
						nr = nr.split( '_' );
						nr = nr[1];
						
						var input_name = 'input[name="elements\[' + element_id + '\]\[answers\]\[id_' + nr + '\]\[sort\]"]';
						var index = $( this ).index();
	              		$( input_name ).val( index ) ;
	              	});
				},
				items:'.answer'
			});
		}
		af_answersortable();
		
		
		/**
		 * Deleting answer
		 */
		var af_deleteanswer = function(){
			var form_deletanswer_dialog = $( '#delete_answer_dialog' );
			var answer_id;
			var deleted_answers;
			
			form_deletanswer_dialog.dialog({
		        'dialogClass'   : 'wp-dialog',           
		        'modal'         : true,
		        'autoOpen'      : false, 
		        'closeOnEscape' : true,
		        'minHeight'		: 80,
		        'buttons'       : [{
						text: translation_fb.yes,
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
						text: translation_fb.no,
						click: function() {
							
							$( this ).dialog( "close" );
							}
						},
					],
					
		    });
			
			$( '.delete_answer' ).click( function( event ){
				answer_id = $( this ).closest( '.answer' ).attr('id');
		        event.preventDefault();
		        form_deletanswer_dialog.dialog( 'open' );
			});
			check_max_input_vars();
		}
		af_deleteanswer();
		
		/**
		 * Adding answer to element
		 */
		var af_add_answer_button = function(){
			$( "#drag-drop-inside" ).on( 'click', '.add-answer', function(){
				var element_id = $( this ).attr( 'rel' );
				af_add_answer( element_id, this );
			});
			
			check_max_input_vars();
		}
		af_add_answer_button();
		
		$( ".element-answer" ).keypress( function( e ) {
            if( e.which == 13 ) {
                e.preventDefault();
                var add_answer = $( this ).parent().find( '.add_answer ');
                
            }
        });
        
        /**
         * Adding empty answer field
         */
        var af_add_answer = function ( element_id, clicked_container ){
            var nr = af_rand();

            var sections = 'input[name="elements\[' + element_id + '\]\[sections\]"]';
            var sections = $( sections ).val();
            
            // Setting up new answer HTML
            var answer_content = '';
            answer_content = '<div class="answer" id="answer_XXnrXX">';
            answer_content = answer_content + '<p><input type="text" id="answer_XXnrXX_input" name="elements[' + element_id + '][answers][id_XXnrXX][answer]" /></p>';
            answer_content = answer_content + '<input type="hidden" name="elements[' + element_id + '][answers][id_XXnrXX][id]" /><input type="hidden" name="elements[' + element_id + '][answers][id_XXnrXX][sort]" />';
            if( 'yes' == sections ){
                var section_key = $( clicked_container ).parent().find( 'input[name="section_key"]' ).val();
                answer_content = answer_content + '<input type="hidden" name="elements[' + element_id + '][answers][id_XXnrXX][section]" value="' + section_key + '" />';
            }
            answer_content = answer_content + ' <input type="button" value="' + translation_fb.delete + '" class="delete_answer button answer_action"></div>';
            answer_content = answer_content.replace( /XXnrXX/g, nr );
            
            // Getting order number for new answer
            var order = 0;
            $( clicked_container ).parent().find( '.answer' ).each( function( e ) { order++; });
            
            // Adding Content
            if( 'yes' == sections ){
                $( answer_content ).appendTo( "#" + element_id + " #section_" + section_key + " .answers" );
            }else{
                $( answer_content ).appendTo( "#" + element_id + " .answers" );
            }
            
            var answer_input = $( "#answer_" + nr + "_input" );
            answer_input.focus();
            
            // Adding sorting number
            var input_name = 'input[name="elements\[' + element_id + '\]\[answers\]\[id_' + nr + '\]\[sort\]"]';
            $( input_name ).val( order ) ;
            
            af_deleteanswer();
        };
		
		/**
		 * Dublicate form
		 */
		$( '#form-duplicate-button' ).click( function(){
			var button = $( this )
			
			if( button.hasClass( 'button' ) ){
				var data = {
					action: 'af_duplicate_form',
					form_id: $( '#post_ID' ).val(),
				};
				
				button.addClass( 'button-loading' );
				
				$.post( ajaxurl, data, function( response ) {
					response = jQuery.parseJSON( response );

					var response_text = translation_fb.duplicated_form_successfully + ' <a href="' + response.admin_url + '">' + translation_fb.edit_form + '</a>';

					$( '#form-functions-notices').html( response_text );
					$( '#form-functions-notices').show();

					button.removeClass( 'button-loading' );
					
					$( '#form-functions-notices' ).fadeOut( 5000 );
				});
				
			}else{
				button.addClass( 'button' );
			}
		});

        /**
         * Delete form results
         */
        $( '#form-delete-results-button' ).click( function(){
            var button = $( this );

            if( button.hasClass( 'button' ) ){

                var form_deletresults_dialog = $( '#delete_responses_dialog' );

                form_deletresults_dialog.dialog({
                    'dialogClass'   : 'wp-dialog',
                    'modal'         : true,
                    'autoOpen'      : false,
                    'closeOnEscape' : true,
                    'minHeight'		: 80,
                    'buttons'       : [{
                        text: translation_fb.yes,
                        click: function() {

                            $( this ).dialog('close');
                            button.addClass( 'button-loading' );

                            var data = {
                                action: 'af_delete_responses',
                                form_id: $( '#post_ID' ).val()
                            };

                            $.post( ajaxurl, data, function( response ) {
                                response = jQuery.parseJSON( response );

                                var response_text = translation_fb.deleted_results_successfully;

								$( '#form-functions-notices').html( response_text );
								$( '#form-functions-notices').show();

                                button.removeClass( 'button-loading' );

                                $( '#form-functions-notices' ).fadeOut( 5000 );
                            });
                        }
                    },
                        {
                            text: translation_fb.no,
                            click: function() {

                                $( this ).dialog( "close" );
                            }
                        },
                    ],

                });

                form_deletresults_dialog.dialog( 'open' );

            }else{
                button.addClass( 'button' );
            }
        });

		/**
         * Initializing jquery tabs in elements
         */
        var af_element_tabs = function(){
            $( ".form_element_tabs" ).tabs({ active: 0 });
        }
        af_element_tabs();
		
		/**
		 * Live typing of element headline
		 */
		var af_rewriteheadline = function(){
            $( ".form-label" ).on( 'input', function(){
                var element_id = $( this ).closest( '.widget' ).attr('id');
                $( "#" +element_id + " .widget-title h4" ).text( $( this ).val() );
            });
        }
		af_rewriteheadline();


		/**
		 * Handling the Templatetag Button
		 */
		$.af_templatetag_buttons = function(){
			var button = $( '.af-templatetag-button');

			$('html').click(function() {
				$( '.af-templatetag-list').hide();
			});

			button.click( function( event ){
				var templatetag_list = $( this ).find( '.af-templatetag-list' );

				if( templatetag_list.css( 'display' ) == 'none' ){
					templatetag_list.show();
				}else{
					templatetag_list.hide();
				}
				event.stopPropagation();
			});

			var template_tag = $( '.af-templatetag-list .af-templatetag' );

			template_tag.unbind();

			template_tag.click( function()
			{
				var tag_name = $( this ).attr( 'data-tagname' );
				var value = '{' + tag_name + '}';

				var input_name = $( this ).attr( 'rel' );
				var input_selector = 'input[name="' + $( this ).attr( 'rel' ) + '"]';

				$( input_selector ).val( $( input_selector ).val() + value );

				tinymce.editors[ input_name ].execCommand( 'mceInsertContent', false, value )
			});

		}
		$.af_templatetag_buttons();

		/**
		 * Helper function - Getting a random number
		 */
		function af_rand(){
			var now = new Date();
			var random = Math.floor(Math.random() * ( 10000 - 10 + 1)) + 10;
			random = random * now.getTime();
			random = random.toString().substring( 0, 5 );
			console.log( random ); 
			return random;
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