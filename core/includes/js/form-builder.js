(function ( exports, $, translations ) {
	"use strict";

	/**
	 * Helper function - Getting a random number
	 */
	function af_rand(){
		var now = new Date();
		var random = Math.floor( Math.random() * ( 10000 - 10 + 1 ) ) + 10;

		random = random * now.getTime();
		random = random.toString().substring( 0, 5 );

		return random;
	}

	/**
     * Helper function - JS recreating of PHP in_array function
     */
	function in_array( needle, haystack ) {
		var length = haystack.length;

		for ( var i = 0; i < length; i++ ) {
			if ( haystack[i] == needle ) return true;
		}

		return false;
	}

	/**
	 * Form_Builder constructor
	 */
	function Form_Builder( translations ) {
		this.translations = translations;

		this.selectors = {
			draggable_item: '#form-elements .formelement',
			droppable_area: '#drag-drop-inside',
			dropped_item_sub: '.formelement',
			drop_elements_here: '#af-drop-elements-here',
			delete_element_button: '.delete_form_element',
			delete_element_dialog: '#delete_formelement_dialog',
			deleted_elements: '#deleted_formelements',
			answers_sub: '.answers',
			answer_sub: '.answer',
			add_answer_button: '.add-answer',
			delete_answer_button: '.delete_answer',
			delete_answer_dialog: '#delete_answer_dialog',
			deleted_answers: '#deleted_answers',
			element_tabs_sub: '.form_element_tabs',
			element_form_label: '.form-label',
			duplicate_form_button: '#form-duplicate-button',
			delete_results_button: '#form-delete-results',
			delete_results_dialog: '#delete_results_dialog'
		};
	}

	/**
	 * Form_Builder class
	 */
	Form_Builder.prototype = {
		init: function() {
			this.init_drag_and_drop();

			this.init_formelement_deletion();

			this.init_sortable_answers();

			this.init_answer_addition();

			this.init_answer_deletion();

			this.init_tab_handling();

			this.init_element_title_rewrite();

			this.init_form_duplication();

			this.init_results_deletion();

			this.init_clipboard();

			this.handle_templatetag_buttons();

			this.check_max_input_vars();

			var self = this;
			$( this.selectors.droppable_area ).on( 'elementDropped', function( event, data ) {
				self.check_max_input_vars();
			});

			//TODO: Adding new Answer after hitting Enter Button
			$( '.element-answer' ).keypress( function( e ) {
				if ( e.which == 13 ) {
					e.preventDefault();
					var $add_answer = $( this ).parent().find( '.add_answer ');
				}
			});
		},

		/**
		 * Initializing the overall drag and drop behavior
		 */
		init_drag_and_drop: function() {
			var self = this;

			// init draggable
			$( this.selectors.draggable_item ).draggable( {
				helper: 'clone',
				cursor: 'move',
				connectToSortable: this.selectors.droppable_area,
				addClasses: false,
				start: function( event, ui ) {
					var $element = ui.helper;

					$( self.selectors.drop_elements_here ).hide();
					$element.css( 'height', 'auto' ).css( 'width', '100px' );
				},
				stop: function( event, ui ) {
					var $element = ui.helper;
					var nr = af_rand();
					var id = 'widget_formelement_' + nr;
					var i = $( self.selectors.droppable_area + ' ' + self.selectors.dropped_item_sub ).length - 1;

					$element.css( 'width', '100%' ).css( 'height', 'auto' );
					$element.addClass( 'widget' );

					// Replacing name
					$element.attr( 'id', id );
					$element.attr( 'data-element-id', id );
					$element.html( $element.html().replace( /XXnrXX/g, nr ) );

					var input_name = 'input[name="elements\[widget_formelement_' + nr +'\]\[sort\]"]';
					$( input_name ).val( i );

					$( self.selectors.droppable_area ).trigger( 'elementDropped', {
						element: $element
					});

					$( self.selectors.droppable_area + ' ' + self.selectors.dropped_item_sub ).each( function( e ) {
						var element_id = $( this ).attr('data-element-id') ;
						var index = $( this ).index();

						console.log( 'Element: ' + element_id + ' Index: ' + index );

						$( 'input[name="elements\[' + element_id +'\]\[sort\]"]' ).val( index ) ;
					});

					if ( $element.data( 'element-type' ) ) {
						switch ( $element.data( 'element-type' ) ) {
							case 'description':
								$.post( ajaxurl, {
									action: 'af_get_editor_html',
									widget_id: id,
									editor_id: 'description_content-' +  id,
									field_name: 'elements[widget_formelement_' + nr + '][label]',
								}, function( response ) {
									response = jQuery.parseJSON( response );

									$( '#' + id + ' .af-element-description' ).html( response.html );
								});
								break;
							default:
						}
					}
				}
			});

			// init droppable
			$( this.selectors.droppable_area ).droppable({
				accept: this.selectors.draggable_item
			}).sortable({
				placeholder: 'form-element-placeholder',
				items: this.selectors.dropped_item_sub,
				update: function( event, ui ) {
					$( self.selectors.droppable_area + ' ' + self.selectors.dropped_item_sub ).each( function( e ) {
						var element_id = $( this ).attr('data-element-id') ;
						var index = $( this ).index();

						$( 'input[name="elements\[' + element_id +'\]\[sort\]"]' ).val( index ) ;
					});
				}
			});
		},

		/**
         * Initializing form element deletion
         */
		init_formelement_deletion: function() {
			var self = this;
			var $form_delete_element_dialog = $( this.selectors.delete_element_dialog );

			$form_delete_element_dialog.dialog({
				'dialogClass'   : 'wp-dialog',
				'modal'         : true,
				'autoOpen'      : false,
				'closeOnEscape' : true,
				'minHeight'     : 80,
				'buttons'       : [
					{
						text: this.translations.yes,
						click: function() {
							if ( self.current_element_id ) {
								self.current_element_id = self.current_element_id.split( '_' );
								self.current_element_id = self.current_element_id[2];

								var deleted_formelements = $( self.selectors.deleted_elements ).val();

								if ( '' == deleted_formelements ) {
									deleted_formelements += self.current_element_id;
								} else {
									deleted_formelements += ',' + self.current_element_id;
								}

								$( self.selectors.deleted_elements ).val( deleted_formelements );
								$( '#widget_formelement_' + self.current_element_id ).remove();

								self.current_element_id = '';

								if ( $( self.selectors.droppable_area + ' ' + self.selectors.dropped_item_sub ).length < 1 ) {
									$( self.selectors.drop_elements_here ).show();
								}
							}

							$( this ).dialog('close');
						}
					},
					{
						text: this.translations.no,
						click: function() {
							$( this ).dialog( "close" );
						}
					}
				]
			});

			$( this.selectors.droppable_area ).on( 'click', this.selectors.delete_element_button, function( e ){
				e.preventDefault();

				self.current_element_id = $( this ).closest( self.selectors.dropped_item_sub ).attr('id');
				$form_delete_element_dialog.dialog( 'open' );
			});
		},

		/**
		 * Making answers in elements sortable
		 */
		init_sortable_answers: function() {
			var self = this;

			function make_sortable( $group ) {
				$group.sortable({
					update: function(  event, ui ){

						var element_id = $( this ).closest( '.widget' ).attr('id');
						var order = [];

						$( this ).find( self.selectors.answer_sub ).each( function( e ) {
							var nr = $( this ).attr( 'id' );
							nr = nr.split( '_' );
							nr = nr[1];

							var input_name = 'input[name="elements\[' + element_id + '\]\[answers\]\[id_' + nr + '\]\[sort\]"]';
							var index = $( this ).index();
							$( input_name ).val( index ) ;
						});
					},
					items: self.selectors.answer_sub
				});
			}

			$( this.selectors.droppable_area ).on( 'elementDropped', function( event, data ) {
				var $element = data.element;

				make_sortable( $element.find( self.selectors.answers_sub ) );
			});

			make_sortable( $( this.selectors.droppable_area + ' ' + self.selectors.answers_sub ) );
		},

		/**
		 * Initializing answer addition button in elements
		 */
		init_answer_addition: function() {
			var self = this;
			$( this.selectors.droppable_area ).on( 'click', this.selectors.add_answer_button, function() {
				var $button = $( this );
				var element_id = $button.attr( 'rel' );

				var nr = af_rand();
				var section_val = $( 'input[name="elements\[' + element_id + '\]\[sections\]"]' ).val()

				// Setting up new answer HTML
				var answer_content = '<div class="answer" id="answer_XXnrXX">';
				answer_content = answer_content + '<p><input type="text" id="answer_XXnrXX_input" name="elements[' + element_id + '][answers][id_XXnrXX][answer]" /></p>';
				answer_content = answer_content + '<input type="hidden" name="elements[' + element_id + '][answers][id_XXnrXX][id]" /><input type="hidden" name="elements[' + element_id + '][answers][id_XXnrXX][sort]" />';

				if ( 'yes' == section_val ) {
					var section_key = $button.parent().find( 'input[name="section_key"]' ).val();
					answer_content = answer_content + '<input type="hidden" name="elements[' + element_id + '][answers][id_XXnrXX][section]" value="' + section_key + '" />';
				}

				answer_content = answer_content + ' <input type="button" value="' + self.translations.delete + '" class="delete_answer button answer_action"></div>';
				answer_content = answer_content.replace( /XXnrXX/g, nr );

				// Getting order number for new answer
				var order = 0;
				$button.parent().find( '.answer' ).each( function( e ) { order++; });

				// Adding Content
				if ( 'yes' == section_val ) {
					var selector = '#' + element_id + ' #section_' + section_key + ' ' + self.selectors.answers_sub;
				} else {
					var selector = '#' + element_id + ' ' + self.selectors.answers_sub;
				}

				$( selector ).append( answer_content );

				var $answer_input = $( '#answer_' + nr + '_input' );
				$answer_input.focus();

				// Adding sorting number
				$( 'input[name="elements\[' + element_id + '\]\[answers\]\[id_' + nr + '\]\[sort\]"]' ).val( order );
			});
		},

		/**
		 * Initializing answer deletion button in elements
		 */
		init_answer_deletion: function() {
			var self = this;
			var $form_deleteanswer_dialog = $( this.selectors.delete_answer_dialog );

			$form_deleteanswer_dialog.dialog({
				'dialogClass'   : 'wp-dialog',
				'modal'         : true,
				'autoOpen'      : false,
				'closeOnEscape' : true,
				'minHeight'		: 80,
				'buttons'       : [
					{
						text: this.translations.yes,
						click: function() {
							if ( self.current_answer_id ) {
								self.current_answer_id = self.current_answer_id.split( '_' );
								self.current_answer_id = self.current_answer_id[1];

								var deleted_answers = $( self.selectors.deleted_answers ).val();

								if ( '' == deleted_answers ) {
									deleted_answers += self.current_answer_id;
								} else {
									deleted_answers += ',' + self.current_answer_id;
								}

								$( self.selectors.deleted_answers ).val( deleted_answers );
								$( '#answer_' + self.current_answer_id ).remove();

								self.current_answer_id = '';
							}

							$( this ).dialog('close');
						}
					},
					{
						text: this.translations.no,
						click: function() {
							$( this ).dialog( "close" );
						}
					}
				]
			});

			$( this.selectors.droppable_area ).on( 'click', this.selectors.delete_answer_button, function( e ){
				e.preventDefault();

				self.current_answer_id = $( this ).closest( '.answer' ).attr('id');
				$form_deleteanswer_dialog.dialog( 'open' );
			});
		},

		/**
         * Initializing jquery tabs in elements
         */
		init_tab_handling: function() {
			var self = this;

			function make_tabs( $element ) {
				$element.tabs({
					active: 0
				});
			}

			$( this.selectors.droppable_area ).on( 'elementDropped', function( event, data ) {
				var $element = data.element;

				make_tabs( $element.find( self.selectors.element_tabs_sub ) );
			});

			make_tabs( $( this.selectors.element_tabs_sub ) );
		},

		/**
		 * Live typing of element headline
		 */
		init_element_title_rewrite: function() {
			$( this.selectors.droppable_area ).on( 'input', this.selectors.element_form_label, function(){
				var element_id = $( this ).closest( '.widget' ).attr('id');
				$( '#' + element_id + ' .widget-title h4' ).text( $( this ).val() );
            });
		},

		/**
		 * Initializing the form duplication button
		 */
		init_form_duplication: function() {
			var self = this;
			$( this.selectors.duplicate_form_button ).on( 'click', function() {
				var $button = $( this );

				if ( $button.hasClass( 'button' ) ) {
					var data = {
						action: 'af_duplicate_form',
						form_id: self.get_form_id()
					};

					$button.addClass( 'button-loading' );

					$.post( ajaxurl, data, function( response ) {
						response = jQuery.parseJSON( response );

						var response_text = self.translations.duplicated_form_successfully + ' <a href="' + response.admin_url + '">' + self.translations.edit_form + '</a>';
						var $notices = $( '#form-options .notices' );

						$notices.html( response_text );
						$notices.show();

						$button.removeClass( 'button-loading' );

						$notices.fadeOut( 5000 );
					});

				} else {
					$button.addClass( 'button' );
				}
			});
		},

		/**
		 * Initializing the Delete Results button
		 */
		init_results_deletion: function() {
			var self = this;
			$( this.selectors.delete_results_button ).on( 'click', function() {
				var $button = $( this );

				if ( $button.hasClass( 'button' ) ) {

					var $form_deleteresults_dialog = $( self.selectors.delete_results_dialog );

					$form_deleteresults_dialog.dialog({
						'dialogClass'	: 'wp-dialog',
						'modal'			: true,
						'autoOpen'		: false,
						'closeOnEscape'	: true,
						'minHeight'		: 80,
						'buttons'		: [
							{
								text: self.translations.yes,
								click: function() {
									$( this ).dialog('close');
									$button.addClass( 'button-loading' );

									var data = {
										action: 'af_delete_responses',
										form_id: self.get_form_id()
									};

									$.post( ajaxurl, data, function( response ) {
										response = jQuery.parseJSON( response );

										var response_text = self.translations.deleted_results_successfully;

										$( '#af-entries .af-slider-start-content' ).html( response.html );

										$( '#charts .af-chart' ).remove();
										$( '#charts' ).prepend( response.html );

										$( '#form-functions-notices').html( response_text );
										$( '#form-functions-notices').show();

										$button.removeClass( 'button-loading' );

										$( '#form-functions-notices' ).fadeOut( 5000 );
									});
								}
							},
							{
								text: self.translations.no,
								click: function() {
									$( this ).dialog( "close" );
								}
							},
						],
					});

					$form_deleteresults_dialog.dialog( 'open' );

				} else {
					$button.addClass( 'button' );
				}
			});
		},

		/**
		 * Initializing clipboard and tooltips
		 */
		init_clipboard: function() {
			var self = this;
			var clipboard = new Clipboard( '.clipboard' );

			clipboard.on( 'success', function( e ) {
				var elem = e.trigger;

				e.clearSelection();

				elem.setAttribute( 'class', 'clipboard tooltipped tooltipped-s button' );
				elem.setAttribute( 'aria-label', self.translations.copied );
			});

			var btns = document.querySelectorAll( '.clipboard' );

			for ( var i = 0; i < btns.length; i++ ) {
				btns[ i ].addEventListener( 'mouseleave', function( e ) {
					e.currentTarget.setAttribute( 'class', 'clipboard button' );
					e.currentTarget.removeAttribute( 'aria-label' );
				});
			}
		},

		/**
		 * Handling the Templatetag Button
		 */
		handle_templatetag_buttons: function() {
			$( 'html' ).on( 'click', function() {
				$( '.af-templatetag-list' ).hide();
			});

			$( '.af-templatetag-button' ).on( 'click', function( e ) {
				var $list = $( this ).find( '.af-templatetag-list' );

				if ( 'none' == $list.css( 'display' ) ) {
					$list.show();
				} else {
					$list.hide();
				}

				e.stopPropagation();
			});

			var $template_tag = $( '.af-templatetag-list .af-templatetag' );

			$template_tag.unbind();

			$template_tag.on( 'click', function() {
				var tag_name_value = '{' + $( this ).attr( 'data-tagname' ) + '}';
				var $input = $( 'input[name="' + $( this ).attr( 'rel' ) + '"]' );

				$input.val( $input.val() + tag_name_value );

				tinymce.editors[ $input.attr( 'name' ) ].execCommand( 'mceInsertContent', false, tag_name_value );
			});
		},

		/**
         * Counting form input vars and showing
         */
		check_max_input_vars: function() {
			var max_input_vars =  parseInt( $( "#max_input_vars" ).val() );
			var input_vars = parseInt( this.count_form_elements( '#post' ) );
			var alert_zone = 50; // The alert will start the alert X before max_input_vars have been reached

			var msg_near_limit = '<strong>' + this.translations.max_fields_near_limit + '</strong> (' + input_vars + ' ' + this.translations.of + ' ' + max_input_vars + ')<br /> ' + this.translations.max_fields_todo;
			var msg_over_limit = '<strong>' + this.translations.max_fields_over_limit + '</strong> (' + input_vars + ' ' + this.translations.of + ' ' + max_input_vars + ')<br /> ' + this.translations.max_fields_todo;

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
		},

		/**
         * Counting all input fields of a selected container
         */
		count_form_elements: function( selector ) {
			var count_inputs = $( selector ).find( 'input' ).length;
			var count_textareas = $( selector ).find( 'textarea' ).length;
			var count_select = $( selector ).find( 'select' ).length;

			var count_all = count_inputs + count_textareas + count_select;

			return count_all;
		},

		/**
         * Returns the current form ID
         */
		get_form_id: function() {
			return $( '#post_ID' ).val();
		}
	};

	var fb_main = new Form_Builder( translations );

	$( document ).ready( function() {
		fb_main.init();
	});

	exports.form_builder = fb_main;

}( window, jQuery, translation_fb ) );
