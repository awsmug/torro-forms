( function( torro, $, _ ) {
	'use strict';

	/**
	 * A container view.
	 *
	 * @class
	 *
	 * @param {torro.Builder.Container} container Container model.
	 * @param {object}                  options   View options.
	 */
	function ContainerView( container, options ) {
		var id       = container.get( 'id' );
		var selected = container.get( 'id' ) === container.collection.props.get( 'selected' );

		this.container = container;
		this.options = options || {};

		this.tabTemplate = torro.template( 'container-tab' );
		this.panelTemplate = torro.template( 'container-panel' );
		this.footerPanelTemplate = torro.template( 'container-footer-panel' );

		this.$tab = $( '<button />' );
		this.$tab.attr( 'type', 'button' );
		this.$tab.attr( 'id', 'container-tab-' + id );
		this.$tab.addClass( 'torro-form-canvas-tab' );
		this.$tab.attr( 'aria-controls', 'container-panel-' + id + ' container-footer-panel-' + id );
		this.$tab.attr( 'aria-selected', selected ? 'true' : 'false' );
		this.$tab.attr( 'role', 'tab' );

		this.$panel = $( '<div />' );
		this.$panel.attr( 'id', 'container-panel-' + id );
		this.$panel.addClass( 'torro-form-canvas-panel' );
		this.$panel.attr( 'aria-labelledby', 'container-tab-' + id );
		this.$panel.attr( 'aria-hidden', selected ? 'false' : 'true' );
		this.$panel.attr( 'role', 'tabpanel' );

		this.$footerPanel = $( '<div />' );
		this.$footerPanel.attr( 'id', 'container-footer-panel-' + id );
		this.$footerPanel.addClass( 'torro-form-canvas-panel' );
		this.$footerPanel.attr( 'aria-labelledby', 'container-tab-' + id );
		this.$footerPanel.attr( 'aria-hidden', selected ? 'false' : 'true' );
		this.$footerPanel.attr( 'role', 'tabpanel' );
	}

	_.extend( ContainerView.prototype, {
		render: function() {
			var combinedAttributes, i;

			combinedAttributes = _.clone( this.container.attributes );

			combinedAttributes.elementTypes = [];
			_.each( torro.Builder.getInstance().elementTypes.getAll(), function( elementType ) {
				combinedAttributes.elementTypes.push( elementType.attributes );
			});

			this.$tab.html( this.tabTemplate( this.container.attributes ) );
			this.$panel.html( this.panelTemplate( combinedAttributes ) );
			this.$footerPanel.html( this.footerPanelTemplate( this.container.attributes ) );

			this.checkHasElements();

			for ( i = 0; i < this.container.elements.length; i++ ) {
				this.listenAddElement( this.container.elements.at( i ) );
			}

			this.attach();
		},

		destroy: function() {
			this.detach();

			this.$tab.remove();
			this.$panel.remove();
			this.$footerPanel.remove();
		},

		attach: function() {
			this.container.on( 'remove', this.listenRemove, this );
			this.container.elements.on( 'add', this.listenAddElement, this );
			this.container.elements.on( 'add remove reset', this.checkHasElements, this );
			this.container.on( 'change:label', this.listenChangeLabel, this );
			this.container.on( 'change:sort', this.listenChangeSort, this );
			this.container.on( 'change:addingElement', this.listenChangeAddingElement, this );
			this.container.on( 'change:selectedElementType', this.listenChangeSelectedElementType, this );
			this.container.collection.props.on( 'change:selected', this.listenChangeSelected, this );

			this.$tab.on( 'click', _.bind( this.setSelected, this ) );
			this.$tab.on( 'dblclick', _.bind( this.editLabel, this ) );
			this.$panel.on( 'click', '.add-element-toggle', _.bind( this.toggleAddingElement, this ) );
			this.$panel.on( 'click', '.torro-element-type', _.bind( this.setSelectedElementType, this ) );
			this.$panel.on( 'click', '.add-element-button', _.bind( this.addElement, this ) );
			this.$footerPanel.on( 'click', '.delete-container-button', _.bind( this.deleteContainer, this ) );

			// TODO: add jQuery hooks
		},

		detach: function() {
			this.container.collection.props.off( 'change:selected', this.listenChangeSelected, this );
			this.container.off( 'change:selectedElementType', this.listenChangeSelectedElementType, this );
			this.container.off( 'change:addingElement', this.listenChangeAddingElement, this );
			this.container.off( 'change:sort', this.listenChangeSort, this );
			this.container.off( 'change:label', this.listenChangeLabel, this );
			this.container.elements.off( 'add remove reset', this.checkHasElements, this );
			this.container.elements.off( 'add', this.listenAddContainer, this );
			this.container.off( 'remove', this.listenRemove, this );

			this.$footerPanel.off( 'click', '.delete-container-button', _.bind( this.deleteContainer, this ) );
			this.$panel.off( 'click', '.add-element-button', _.bind( this.addElement, this ) );
			this.$panel.off( 'click', '.torro-element-type', _.bind( this.setSelectedElementType, this ) );
			this.$panel.off( 'click', '.add-element-toggle', _.bind( this.toggleAddingElement, this ) );
			this.$tab.off( 'dblclick', _.bind( this.editLabel, this ) );
			this.$tab.off( 'click', _.bind( this.setSelected, this ) );

			// TODO: remove jQuery hooks
		},

		listenRemove: function() {
			this.destroy();
		},

		listenAddElement: function( element ) {
			var view = new torro.Builder.ElementView( element, this.options );

			this.$panel.find( '.drag-drop-area' ).append( view.$wrap );

			view.render();
		},

		checkHasElements: function() {
			if ( this.container.elements.length ) {
				this.$panel.find( '.drag-drop-area' ).removeClass( 'is-empty' );
			} else {
				this.$panel.find( '.drag-drop-area' ).addClass( 'is-empty' );
			}
		},

		listenChangeLabel: function( container, label ) {
			var name = torro.escapeSelector( torro.getFieldName( this.container, 'label' ) );

			this.$panel.find( 'input[name="' + name + '"]' ).val( label );
		},

		listenChangeSort: function( container, sort ) {
			var name = torro.escapeSelector( torro.getFieldName( this.container, 'sort' ) );

			this.$panel.find( 'input[name="' + name + '"]' ).val( sort );
		},

		listenChangeAddingElement: function( container, addingElement ) {
			if ( addingElement ) {
				this.$panel.find( '.add-element-toggle-wrap' ).addClass( 'is-expanded' );
				this.$panel.find( '.add-element-toggle' ).attr( 'aria-expanded', 'true' );
				this.$panel.find( '.add-element-content-wrap' ).addClass( 'is-expanded' );
			} else {
				this.$panel.find( '.add-element-toggle-wrap' ).removeClass( 'is-expanded' );
				this.$panel.find( '.add-element-toggle' ).attr( 'aria-expanded', 'false' );
				this.$panel.find( '.add-element-content-wrap' ).removeClass( 'is-expanded' );
			}
		},

		listenChangeSelectedElementType: function( container, selectedElementType ) {
			var elementType;

			this.$panel.find( '.torro-element-type' ).removeClass( 'is-selected' );

			if ( selectedElementType ) {
				elementType = torro.Builder.getInstance().elementTypes.get( selectedElementType );
				if ( elementType ) {
					this.$panel.find( '.torro-element-type-' + elementType.getSlug() ).addClass( 'is-selected' );
					this.$panel.find( '.add-element-button' ).prop( 'disabled', false );
					return;
				}
			}

			this.$panel.find( '.add-element-button' ).prop( 'disabled', true );
		},

		listenChangeSelected: function( props, selected ) {
			if ( selected === this.container.get( 'id' ) ) {
				this.$tab.attr( 'aria-selected', 'true' );
				this.$panel.attr( 'aria-hidden', 'false' );
				this.$footerPanel.attr( 'aria-hidden', 'false' );
			} else {
				this.$tab.attr( 'aria-selected', 'false' );
				this.$panel.attr( 'aria-hidden', 'true' );
				this.$footerPanel.attr( 'aria-hidden', 'true' );
			}
		},

		setSelected: function() {
			this.container.collection.props.set( 'selected', this.container.get( 'id' ) );
		},

		editLabel: function() {
			var container = this.container;
			var $original = this.$tab.find( 'span' );
			var $replacement;

			if ( ! $original.length ) {
				return;
			}

			$replacement = $( '<input />' );
			$replacement.attr( 'type', 'text' );
			$replacement.val( $original.text() );

			$replacement.on( 'keydown blur', function( event ) {
				var proceed = false;
				var value;

				if ( 'keydown' === event.type ) {
					if ( 13 === event.which ) {
						proceed = true;

						event.preventDefault();
					} else if ( [ 32, 37, 38, 39, 40 ].includes( event.which ) ) {
						event.stopPropagation();
					}
				} else if ( 'blur' === event.type ) {
					proceed = true;
				} else {
					event.stopPropagation();
				}

				if ( ! proceed ) {
					return;
				}

				value = $replacement.val();

				container.set( 'label', value );

				$original.text( value );
				$replacement.replaceWith( $original );
				$original.focus();
			});

			$original.replaceWith( $replacement );
			$replacement.focus();
		},

		toggleAddingElement: function() {
			if ( this.container.get( 'addingElement' ) ) {
				this.container.set( 'addingElement', false );
			} else {
				this.container.set( 'addingElement', true );
			}
		},

		setSelectedElementType: function( e ) {
			var slug = false;

			if ( e && e.currentTarget ) {
				slug = $( e.currentTarget ).data( 'slug' );
			}

			if ( slug ) {
				this.container.set( 'selectedElementType', slug );
			} else {
				this.container.set( 'selectedElementType', false );
			}
		},

		addElement: function() {
			var selectedElementType = this.container.get( 'selectedElementType' );
			var element;

			if ( ! selectedElementType ) {
				return;
			}

			element = this.container.elements.create({
				type: selectedElementType
			});

			this.toggleAddingElement();
			this.setSelectedElementType();

			this.container.elements.toggleActive( element.get( 'id' ) );
		},

		deleteContainer: function() {
			this.container.collection.remove( this.container );
		}
	});

	torro.Builder.ContainerView = ContainerView;

})( window.torro, window.jQuery, window._ );
