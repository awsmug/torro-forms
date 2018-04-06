( function( torro, Backbone, wp ) {
	'use strict';

	var State = wp.media.controller.State;
	var ElementTypeLibrary;

	ElementTypeLibrary = State.extend({
		defaults: {
			id: 'element-type-library',
			title: torro.Builder.i18n.selectElementType,
			menu: 'default',
			content: 'select-element-type',
			toolbar: 'insert-element'
		},

		initialize: function() {
			if ( ! this.get( 'collection' ) ) {
				this.set( 'collection', new Backbone.Collection( [] ) );
			}

			this.set( 'selected', null );
		},

		reset: function() {
			this.set( 'selected', null );
		}
	});

	torro.Builder.AddElement.State.ElementTypeLibrary = ElementTypeLibrary;

})( window.torro, window.Backbone, window.wp );
