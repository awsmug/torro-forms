( function( torro, $, _, Backbone, wp ) {
	'use strict';

	var View = wp.media.View;
	var ElementTypesBrowser;

	ElementTypesBrowser = View.extend({
		tagName: 'div',
		className: 'element-types-browser',
		template:  torro.template( 'element-types-browser' ),

		events: {
			'click .torro-element-type': 'setSelected',
			'keyup .torro-element-type': 'setSelected'
		},

		initialize: function() {
			View.prototype.initialize.apply( this, arguments );

			_.defaults( this.options, {
				collection: []
			});

			if ( ! ( this.options.collection instanceof Backbone.Collection ) ) {
				this.options.collection = new Backbone.Collection( this.options.collection );
			}

			this.controller.state().on( 'change:selected', this.listenToSelected, this );
		},

		prepare: function() {
			var data = {
				elementTypes: this.options.collection.toJSON(),
				selectedElementType: this.controller.state().get( 'selected' )
			};

			return data;
		},

		setSelected: function( e ) {
			if ( 'keyup' === e.type && 32 !== e.keyCode ) {
				return;
			}

			if ( e.currentTarget && e.currentTarget.dataset.slug ) {
				this.controller.state().set( 'selected', e.currentTarget.dataset.slug );
			}
		},

		listenToSelected: function( state, selected ) {
			this.$el.find( '.torro-element-type' ).each( function() {
				var $this = $( this );

				$this.toggleClass( 'is-selected', $this.data( 'slug' ) === selected );
			});
		}
	});

	torro.Builder.AddElement.View.ElementTypesBrowser = ElementTypesBrowser;

})( window.torro, window.jQuery, window._, window.Backbone, window.wp );
