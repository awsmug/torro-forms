/*!
 * Torro Forms Version 1.0.0-beta.8 (http://torro-forms.com)
 * Licensed under GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
window.torro = window.torro || {};

( function( torro, $, _, Backbone, wp, i18n ) {
	'use strict';

	var instanceCount = 0,
		initialized = [],
		callbacks = {},
		builder;

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

		addHooks: function() {
			if ( ! _.contains( initialized, this.instanceCount ) ) {
				return;
			}
		},

		setupInitialData: function( form ) {
			if ( ! _.contains( initialized, this.instanceCount ) ) {
				return;
			}
		},

		onLoad: function( callback ) {
			if ( _.isUndefined( callbacks[ 'builder' + this.instanceCount ] ) ) {
				callback( this );
				return;
			}

			callbacks[ 'builder' + this.instanceCount ].push( callback );
		},

		fail: function( message ) {
			var compiled = torro.template( 'failure' );

			this.$el.find( '.drag-drop-area' ).addClass( 'is-empty' ).html( compiled({ message: message }) );
		}
	});

	torro.Builder = Builder;
	torro.Builder.getInstance = function() {
		if ( ! builder ) {
			builder = new Builder( '#torro-form-canvas' );
			builder.init();
		}

		return builder;
	};

}( window.torro, window.jQuery, window._, window.Backbone, window.wp, window.torroBuilderI18n ) );

( function( torroBuilder, _ ) {
	'use strict';

	function ElementType( attributes ) {
		this.attributes = attributes;
	}

	_.extend( ElementType.prototype, {

		getSlug: function() {
			return this.attributes.slug;
		},

		getTitle: function() {
			return this.attributes.title;
		},

		getDescription: function() {
			return this.attributes.description;
		},

		getIconUrl: function() {
			return this.attributes.icon_url;
		},

		isEvaluable: function() {
			return this.attributes.evaluable;
		},

		isMultiField: function() {
			return this.attributes.multifield;
		}
	});

	torroBuilder.ElementType = ElementType;

})( window.torro.Builder, window._ );

( function( torroBuilder, _ ) {
	'use strict';

	function ElementTypes( elementTypes ) {
		var i;

		this.types = {};

		for ( i in elementTypes ) {
			this.types[ elementTypes[ i ].getSlug() ] = elementTypes[ i ];
		}
	}

	_.extend( ElementTypes.prototype, {

		get: function( slug ) {
			if ( _.isUndefined( this.types[ slug ] ) ) {
				return undefined;
			}

			return this.types[ slug ];
		},

		getAll: function() {
			return this.types;
		}
	});

	ElementTypes.fromApiCollection = function( collection ) {
		var elementTypes = [];

		collection.each( function( model ) {
			var attributes = _.extend({}, model.attributes );
			if ( attributes._links ) {
				delete attributes._links;
			}
			if ( attributes._embedded ) {
				delete attributes._embedded;
			}

			elementTypes.push( new torroBuilder.ElementType( attributes ) );
		});

		return new ElementTypes( elementTypes );
	};

	torroBuilder.ElementTypes = ElementTypes;

})( window.torro.Builder, window._ );

( function( torroBuilder ) {
	'use strict';

	torroBuilder.getInstance();

})( window.torro.Builder );
