( function( torro, _, Backbone, wp ) {
	'use strict';

	var View = wp.media.View;
	var ElementTypesBrowserView;

	ElementTypesBrowserView = View.extend({
		tagName: 'div',
		className: 'element-types-browser',
		template:  torro.template( 'element-types-browser' ),

		events: {
			'click .torro-element-type': 'setSelected'
		},

		initialize: function() {
			View.prototype.initialize.apply( this, arguments );

			_.defaults( this.options, {
				collection: []
			});

			if ( ! ( this.options.collection instanceof Backbone.Collection ) ) {
				this.options.collection = new Backbone.Collection( this.options.collection );
			}
		},

		prepare: function() {
			var data = {
				elementTypes: this.options.collection.toJSON(),
				selectedElementType: this.controller.state().get( 'selected' )
			};

			return data;
		},

		setSelected: function( e ) {
			if ( e.currentTarget && e.currentTarget.dataset.slug ) {
				this.controller.state().set( 'selected', e.currentTarget.dataset.slug );
			}
		}
	});

	torro.Builder.ElementTypesBrowserView = ElementTypesBrowserView;

})( window.torro, window._, window.Backbone, window.wp );
