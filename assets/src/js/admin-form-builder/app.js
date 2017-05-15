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

		this.elementTypes;

		this.form;
		this.containers;
		this.elements;
		this.elementChoices;
		this.elementSettings;
	}

	_.extend( Builder.prototype, {

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
					( new torro.api.models.Form({
						id: parseInt( $( '#post_ID' ).val(), 10 )
					}) ).fetch({
						data: {
							context: 'edit',
							_embed: true
						},
						context: this,
						success: function( form ) {
							( new torro.api.collections.ElementTypes() ).fetch({
								data: {
									context: 'edit'
								},
								context: this,
								success: function( elementTypes ) {
									$( document ).ready( _.bind( function() {
										var i;

										initialized.push( this.instanceCount );

										this.elementTypes = torro.Builder.ElementTypes.fromApiCollection( elementTypes );

										this.addHooks();
										this.setupInitialData( form );

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
		 * Adds hooked callbacks.
		 *
		 * This method only works if the form builder has been initialized.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		addHooks: function() {
			if ( ! _.contains( initialized, this.instanceCount ) ) {
				return;
			}
		},

		/**
		 * Sets up initial data for the form builder.
		 *
		 * This method only works if the form builder has been initialized.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {object} form REST API form response including embedded data.
		 */
		setupInitialData: function( form ) {
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
