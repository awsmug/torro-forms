( function( torro, $, _ ) {
	'use strict';

	/**
	 * An element view.
	 *
	 * @class
	 *
	 * @param {torro.Builder.Element} element Element model.
	 * @param {object}                options View options.
	 */
	function ElementView( element, options ) {
		var id = element.get( 'id' );

		this.element = element;
		this.options = options || {};

		this.elementType = torro.Builder.getInstance().elementTypes.get( this.element.get( 'type' ) );

		this.wrapTemplate = torro.template( 'element' );

		this.$wrap = $( '<div />' );
		this.$wrap.attr( 'id', 'torro-element-' + id );
		this.$wrap.addClass( 'torro-element' );
	}

	_.extend( ElementView.prototype, {
		render: function() {
			var templateData = this.element.attributes;
			templateData.type = this.elementType.attributes;

			this.$wrap.html( this.wrapTemplate( templateData ) );

			this.attach();
		},

		destroy: function() {
			this.detach();

			this.$wrap.remove();
		},

		attach: function() {
			this.element.on( 'remove', this.listenRemove, this );
			this.element.on( 'change:label', this.listenChangeLabel, this );
			this.element.on( 'change:sort', this.listenChangeSort, this );

			this.$wrap.on( 'click', '.delete-element-button', _.bind( this.deleteElement, this ) );

			// TODO: add jQuery hooks
		},

		detach: function() {
			this.element.off( 'change:sort', this.listenChangeSort, this );
			this.element.off( 'change:label', this.listenChangeLabel, this );
			this.element.off( 'remove', this.listenRemove, this );

			this.$wrap.off( 'click', '.delete-element-button', _.bind( this.deleteElement, this ) );

			// TODO: remove jQuery hooks
		},

		listenRemove: function() {
			this.destroy();
		},

		listenChangeLabel: function( container, label ) {
			var name = torro.escapeSelector( torro.getFieldName( this.element, 'label' ) );

			this.$wrap.find( 'input[name="' + name + '"]' ).val( label );
		},

		listenChangeSort: function( container, sort ) {
			var name = torro.escapeSelector( torro.getFieldName( this.element, 'sort' ) );

			this.$wrap.find( 'input[name="' + name + '"]' ).val( sort );
		},

		deleteElement: function() {
			this.element.collection.remove( this.element );
		}
	});

	torro.Builder.ElementView = ElementView;

})( window.torro, window.jQuery, window._ );
