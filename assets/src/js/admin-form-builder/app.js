window.torro = window.torro || {};

( function( torro, $, _, Backbone, wp, i18n ) {
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
		 * Current container collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {torro.Builder.ContainerCollection}
		 */
		containers: undefined,

		/**
		 * Current element collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {torro.Builder.ElementCollection}
		 */
		elements: undefined,

		/**
		 * Current element choice collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {torro.Builder.ElementChoiceCollection}
		 */
		elementChoices: undefined,

		/**
		 * Current element setting collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {torro.Builder.ElementSettingCollection}
		 */
		elementSettings: undefined,

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
			if ( ! _.contains( initialized, this.instanceCount ) ) {
				return;
			}

			if ( form ) {
				this.form = new torro.Builder.FormModel( form );

				if ( form._embedded.containers && form._embedded.containers[0] ) {
					this.containers = new torro.Builder.ContainerCollection( form._embedded.containers[0] );
				} else {
					this.containers = new torro.Builder.ContainerCollection();
				}

				if ( form._embedded.elements && form._embedded.elements[0] ) {
					this.elements = new torro.Builder.ElementCollection( form._embedded.elements[0] );
				} else {
					this.elements = new torro.Builder.ElementCollection();
				}

				if ( form._embedded.element_choices && form._embedded.element_choices[0] ) {
					this.elementChoices = new torro.Builder.ElementChoiceCollection( form._embedded.element_choices[0] );
				} else {
					this.elementChoices = new torro.Builder.ElementChoiceCollection();
				}

				if ( form._embedded.element_settings && form._embedded.element_settings[0] ) {
					this.elementSettings = new torro.Builder.ElementSettingCollection( form._embedded.element_settings[0] );
				} else {
					this.elementSettings = new torro.Builder.ElementSettingCollection();
				}
			} else {
				this.form = new torro.Builder.FormModel();

				this.containers = new torro.Builder.ContainerCollection([
					{
						form_id: this.form.get( 'id' ),
						label: i18n.defaultContainerLabel.replace( '%s', '1' )
					}
				]);

				this.elements = new torro.Builder.ElementCollection();

				this.elementChoices = new torro.Builder.ElementChoiceCollection();

				this.elementSettings = new torro.Builder.ElementSettingCollection();
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

}( window.torro, window.jQuery, window._, window.Backbone, window.wp, window.torroBuilderI18n ) );
