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
		var sections;

		this.element = element;
		this.options = options || {};

		this.elementType = torro.Builder.getInstance().elementTypes.get( this.element.get( 'type' ) );

		sections = this.elementType.getSections();
		if ( sections.length ) {
			this.options.activeSection = sections[0].slug;
		}

		this.wrapTemplate = torro.template( 'element' );

		this.$wrap = $( '<div />' );
		this.$wrap.attr( 'id', 'torro-element-' + id );
		this.$wrap.addClass( 'torro-element' );
	}

	_.extend( ElementView.prototype, {
		render: function() {
			var templateData            = this.element.attributes;
			templateData.type           = this.elementType.attributes;
			templateData.active         = this.element.collection.props.get( 'active' ).includes( this.element.get( 'id' ) );
			templateData.active_section = this.options.activeSection;

			this.$wrap.html( this.wrapTemplate( templateData ) ).find( 'input,textarea,select' ).first().focus();

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
			this.element.collection.props.on( 'toggleActive', this.listenChangeActive, this );

			this.$wrap.on( 'click', '.torro-element-expand-button', _.bind( this.toggleActive, this ) );
			this.$wrap.on( 'click', '.delete-element-button', _.bind( this.deleteElement, this ) );
			this.$wrap.on( 'click', '.torro-element-content-tab', _.bind( this.changeActiveSection, this ) );

			// TODO: add jQuery hooks
		},

		detach: function() {
			this.element.collection.props.off( 'toggleActive', this.listenChangeActive, this );
			this.element.off( 'change:sort', this.listenChangeSort, this );
			this.element.off( 'change:label', this.listenChangeLabel, this );
			this.element.off( 'remove', this.listenRemove, this );

			this.$wrap.off( 'click', '.torro-element-content-tab', _.bind( this.changeActiveSection, this ) );
			this.$wrap.off( 'click', '.delete-element-button', _.bind( this.deleteElement, this ) );
			this.$wrap.off( 'click', '.torro-element-expand-button', _.bind( this.toggleActive, this ) );

			// TODO: remove jQuery hooks
		},

		listenRemove: function() {
			var id = this.element.get( 'id' );

			if ( ! torro.isTempId( id ) ) {
				$( '#torro-deleted-wrap' ).append( '<input type="hidden" name="' + torro.getDeletedFieldName( this.element ) + '" value="' + id + '" />' );
			}

			this.destroy();
		},

		listenChangeLabel: function( element, label ) {
			var name = torro.escapeSelector( torro.getFieldName( this.element, 'label' ) );

			this.$wrap.find( 'input[name="' + name + '"]' ).val( label );
		},

		listenChangeSort: function( element, sort ) {
			var name = torro.escapeSelector( torro.getFieldName( this.element, 'sort' ) );

			this.$wrap.find( 'input[name="' + name + '"]' ).val( sort );
		},

		listenChangeActive: function( props, active ) {
			if ( active.includes( this.element.get( 'id' ) ) ) {
				this.$wrap.find( '.torro-element-expand-button' ).attr( 'aria-expanded', 'true' ).find( '.screen-reader-text' ).text( this.options.i18n.hideContent );
				this.$wrap.find( '.torro-element-content' ).addClass( 'is-expanded' );
			} else {
				this.$wrap.find( '.torro-element-expand-button' ).attr( 'aria-expanded', 'false' ).find( '.screen-reader-text' ).text( this.options.i18n.showContent );
				this.$wrap.find( '.torro-element-content' ).removeClass( 'is-expanded' );
			}
		},

		toggleActive: function() {
			this.element.collection.toggleActive( this.element.get( 'id' ) );
		},

		deleteElement: function() {
			torro.askConfirmation( this.options.i18n.confirmDeleteElement, _.bind( function() {
				this.element.collection.remove( this.element );
			}, this ) );
		},

		changeActiveSection: function( e ) {
			var $button = $( e.target || e.delegateTarget );

			if ( $button.data( 'slug' ) === this.options.activeSection ) {
				return;
			}

			this.options.activeSection = $button.data( 'slug' );

			this.$wrap.find( '.torro-element-content-tab' ).attr( 'aria-selected', 'false' );
			this.$wrap.find( '.torro-element-content-panel' ).attr( 'aria-hidden', 'true' );

			$button.attr( 'aria-selected', 'true' );
			this.$wrap.find( '#' + $button.attr( 'aria-controls' ) ).attr( 'aria-hidden', 'false' );
		}
	});

	torro.Builder.ElementView = ElementView;

})( window.torro, window.jQuery, window._ );
