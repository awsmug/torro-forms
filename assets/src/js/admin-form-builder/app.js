window.torro = window.torro || {};

( function( torro, $, _, i18n ) {
	'use strict';

	var instanceCount = 0,
		initialized = [],
		callbacks = {},
		builder;

	/**
	 * A form builder instance.
	 *
	 * @class
	 *
	 * @param {string} selector DOM selector for the wrapping element for the UI.
	 */
	function Builder( selector ) {
		instanceCount++;
		callbacks[ 'builder' + instanceCount ] = [];

		this.instanceNumber = instanceCount;

		this.$el = $( selector );
	}

	_.extend( Builder.prototype, {

		/**
		 * Available element types.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {torro.Builder.ElementTypes}
		 */
		elementTypes: undefined,

		/**
		 * Current form model.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {torro.Builder.FormModel}
		 */
		form: undefined,

		/**
		 * Form view object.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {torro.Builder.FormView}
		 */
		formView: undefined,

		/**
		 * Initializes the form builder.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		init: function() {
			if ( ! this.$el.length ) {
				console.error( i18n.couldNotInitCanvas );
				return;
			}

			torro.api.init()
				.done( _.bind( function() {
					( new torro.api.collections.ElementTypes() ).fetch({
						data: {
							context: 'edit'
						},
						context: this,
						success: function( elementTypes ) {
							this.elementTypes = torro.Builder.ElementTypes.fromApiCollection( elementTypes );

							if ( 'auto-draft' !== $( '#original_post_status' ).val() ) {
								( new torro.api.models.Form({
									id: parseInt( $( '#post_ID' ).val(), 10 )
								}) ).fetch({
									data: {
										context: 'edit',
										_embed: true
									},
									context: this,
									success: function( form ) {
										$( document ).ready( _.bind( function() {
											var i;

											initialized.push( this.instanceCount );

											this.setupInitialData( form.attributes );
											this.setupViews();

											for ( i in callbacks[ 'builder' + this.instanceCount ] ) {
												callbacks[ 'builder' + this.instanceCount ][ i ]( this );
											}

											delete callbacks[ 'builder' + this.instanceCount ];
										}, this ) );
									},
									error: function() {
										$( document ).ready( _.bind( function() {
											this.fail( i18n.couldNotLoadData );
										}, this ) );
									}
								});
							} else {
								$( document ).ready( _.bind( function() {
									var i;

									initialized.push( this.instanceCount );

									this.setupInitialData();
									this.setupViews();

									for ( i in callbacks[ 'builder' + this.instanceCount ] ) {
										callbacks[ 'builder' + this.instanceCount ][ i ]( this );
									}

									delete callbacks[ 'builder' + this.instanceCount ];
								}, this ) );
							}
						},
						error: function() {
							$( document ).ready( _.bind( function() {
								this.fail( i18n.couldNotLoadData );
							}, this ) );
						}
					});
				}, this ) )
				.fail( _.bind( function() {
					$( document ).ready( _.bind( function() {
						this.fail( i18n.couldNotLoadData );
					}, this ) );
				}, this ) );
		},

		/**
		 * Sets up initial data for the form builder.
		 *
		 * This method only works if the form builder has been initialized.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {object|undefined} form REST API form response including embedded data, or
		 *                                undefined if this is a new form.
		 */
		setupInitialData: function( form ) {
			var container, element, elementChoice, elementSetting, elementParents, i;

			if ( ! _.contains( initialized, this.instanceCount ) ) {
				return;
			}

			if ( form ) {
				this.form = new torro.Builder.FormModel( form );

				if ( form._embedded.containers && form._embedded.containers[0] ) {
					this.form.containers.add( form._embedded.containers[0] );

					if ( form._embedded.elements && form._embedded.elements[0] ) {
						elementParents = {};

						for ( i = 0; i < form._embedded.elements[0].length; i++ ) {
							element = form._embedded.elements[0][ i ];

							container = this.form.containers.get( element.container_id );
							if ( container ) {
								container.elements.add( element );

								elementParents[ element.id ] = element.container_id;
							}
						}

						if ( form._embedded.element_choices && form._embedded.element_choices[0] ) {
							for ( i = 0; i < form._embedded.element_choices[0].length; i++ ) {
								elementChoice = form._embedded.element_choices[0][ i ];

								if ( elementParents[ elementChoice.element_id ] ) {
									container = this.form.containers.get( elementParents[ elementChoice.element_id ] );
									if ( container ) {
										element = container.elements.get( elementChoice.element_id );
										if ( element ) {
											element.element_choices.add( elementChoice );
										}
									}
								}
							}
						}

						if ( form._embedded.element_settings && form._embedded.element_settings[0] ) {
							for ( i = 0; i < form._embedded.element_settings[0].length; i++ ) {
								elementSetting = form._embedded.element_settings[0][ i ];

								if ( elementParents[ elementSetting.element_id ] ) {
									container = this.form.containers.get( elementParents[ elementSetting.element_id ] );
									if ( container ) {
										element = container.elements.get( elementSetting.element_id );
										if ( element ) {
											element.setElementSetting( elementSetting );
										}
									}
								}
							}
						}
					}
				}
			} else {
				this.form = new torro.Builder.FormModel({});

				this.form.containers.add({});
			}
		},

		/**
		 * Sets up form builder views.
		 *
		 * This method only works if the form builder has been initialized.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		setupViews: function() {
			if ( ! _.contains( initialized, this.instanceCount ) ) {
				return;
			}

			this.formView = new torro.Builder.FormView( this.$el, this.form );

			this.formView.render();
		},

		/**
		 * Adds a callback that will be executed once the form builder has been initialized.
		 *
		 * If the form builder has already been initialized, the callback will be executed
		 * immediately.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {function} callback Callback to execute. Should accept the form builder instance
		 *                            as parameter.
		 */
		onLoad: function( callback ) {
			if ( _.isUndefined( callbacks[ 'builder' + this.instanceCount ] ) ) {
				callback( this );
				return;
			}

			callbacks[ 'builder' + this.instanceCount ].push( callback );
		},

		/**
		 * Shows a failure message for the form builder in the UI.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {string} message Failure message to display.
		 */
		fail: function( message ) {
			var compiled = torro.template( 'failure' );

			this.$el.find( '.drag-drop-area' ).addClass( 'is-empty' ).html( compiled({ message: message }) );
		},

		/**
		 * Registers a function to be called whenever a certain form builder hook is triggered.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {string}   hook     Hook name.
		 * @param {function} callback Callback function to execute.
		 */
		on: function( hook, callback ) {
			hook = 'torro.' + hook;

			this.$el.on( hook, function() {

				// Pass on all arguments except the event.
				var args = Array.prototype.slice.call( arguments, 1 );

				if ( args.length ) {
					callback.apply( undefined, args );
				} else {
					callback.apply( undefined, undefined );
				}
			});
		},

		/**
		 * Triggers a hook for the form builder.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {string} hook Hook name.
		 * @param {array}  data Optional. Arguments to pass to each callback.
		 */
		trigger: function( hook, data ) {
			hook = 'torro.' + hook;
			data = data || [];

			this.$el.trigger( hook, data );
		}
	});

	torro.Builder = Builder;

	/**
	 * Returns the main form builder instance.
	 *
	 * It will be instantiated and initialized if it does not exist yet.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	torro.Builder.getInstance = function() {
		if ( ! builder ) {
			builder = new Builder( '#torro-form-canvas' );
			builder.init();
		}

		return builder;
	};

	// Scaffold the AddElement namespace for modal functionality.
	torro.Builder.AddElement = {
		State: {},
		View:  {}
	};

	torro.getFieldName = function( model, attribute ) {
		var groupSlug;

		if ( model instanceof torro.Builder.FormModel ) {
			groupSlug = 'forms';
		} else if ( model instanceof torro.Builder.ContainerModel ) {
			groupSlug = 'containers';
		} else if ( model instanceof torro.Builder.ElementModel ) {
			groupSlug = 'elements';
		} else if ( model instanceof torro.Builder.ElementChoiceModel ) {
			groupSlug = 'element_choices';
		} else if ( model instanceof torro.Builder.ElementSettingModel ) {
			groupSlug = 'element_settings';
		}

		if ( ! groupSlug ) {
			return;
		}

		return 'torro_' + groupSlug + '[' + model.get( 'id' ) + '][' + attribute + ']';
	};

	torro.getDeletedFieldName = function( model ) {
		var groupSlug;

		if ( model instanceof torro.Builder.FormModel ) {
			groupSlug = 'forms';
		} else if ( model instanceof torro.Builder.ContainerModel ) {
			groupSlug = 'containers';
		} else if ( model instanceof torro.Builder.ElementModel ) {
			groupSlug = 'elements';
		} else if ( model instanceof torro.Builder.ElementChoiceModel ) {
			groupSlug = 'element_choices';
		} else if ( model instanceof torro.Builder.ElementSettingModel ) {
			groupSlug = 'element_settings';
		}

		if ( ! groupSlug ) {
			return;
		}

		return 'torro_deleted_' + groupSlug + '[]';
	};

	torro.askConfirmation = function( message, successCallback ) {
		var $dialog = $( '<div />' );

		$dialog.html( message );

		$( 'body' ).append( $dialog );

		$dialog.dialog({
			dialogClass: 'wp-dialog torro-dialog',
			modal: true,
			autoOpen: true,
			closeOnEscape: true,
			minHeight: 80,
			buttons: [
				{
					text: i18n.yes,
					click: function() {
						successCallback();

						$( this ).dialog( 'close' );
						$( this ).remove();
					}
				},
				{
					text: i18n.no,
					click: function() {
						$( this ).dialog( 'close' );
						$( this ).remove();
					}
				}
			]
		});
	};

	torro.Builder.i18n = i18n;

}( window.torro, window.jQuery, window._, window.torroBuilderI18n ) );
