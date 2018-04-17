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

		this.addElementFrame = new torro.Builder.AddElement.View.Frame({
			title: torro.Builder.i18n.selectElementType,
			buttonLabel: torro.Builder.i18n.insertIntoContainer,
			collection: torro.Builder.getInstance().elementTypes
		});
	}

	_.extend( ContainerView.prototype, {
		render: function() {
			var i;

			this.$tab.html( this.tabTemplate( this.container.attributes ) );
			this.$panel.html( this.panelTemplate( this.container.attributes ) );
			this.$footerPanel.html( this.footerPanelTemplate( this.container.attributes ) );

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
			this.container.on( 'change:label', this.listenChangeLabel, this );
			this.container.on( 'change:sort', this.listenChangeSort, this );
			this.container.collection.props.on( 'change:selected', this.listenChangeSelected, this );

			this.addElementFrame.on( 'insert', this.addElement, this );

			this.$tab.on( 'click', _.bind( this.setSelected, this ) );
			this.$tab.on( 'dblclick', _.bind( this.editLabel, this ) );
			this.$panel.on( 'click', '.add-element-toggle', _.bind( this.openAddElementFrame, this ) );
			this.$footerPanel.on( 'click', '.delete-container-button', _.bind( this.deleteContainer, this ) );
			this.$panel.find( '.drag-drop-area' ).sortable({
				handle: '.torro-element-header',
				items: '.torro-element',
				placeholder: 'torro-element-placeholder',
				tolerance: 'pointer',
				start: function( e, ui ) {
					ui.placeholder.height( ui.item.height() );
				},
				update: _.bind( this.updateElementsSorted, this )
			});
		},

		detach: function() {
			this.$panel.find( 'drag-drop-area' ).sortable( 'destroy' );
			this.$footerPanel.off( 'click', '.delete-container-button', _.bind( this.deleteContainer, this ) );
			this.$panel.off( 'click', '.add-element-toggle', _.bind( this.openAddElementFrame, this ) );
			this.$tab.off( 'dblclick', _.bind( this.editLabel, this ) );
			this.$tab.off( 'click', _.bind( this.setSelected, this ) );

			this.addElementFrame.off( 'insert', this.addElement, this );

			this.container.collection.props.off( 'change:selected', this.listenChangeSelected, this );
			this.container.off( 'change:sort', this.listenChangeSort, this );
			this.container.off( 'change:label', this.listenChangeLabel, this );
			this.container.elements.off( 'add', this.listenAddContainer, this );
			this.container.off( 'remove', this.listenRemove, this );
		},

		listenRemove: function() {
			var id = this.container.get( 'id' );

			if ( ! torro.isTempId( id ) ) {
				$( '#torro-deleted-wrap' ).append( '<input type="hidden" name="' + torro.getDeletedFieldName( this.container ) + '" value="' + id + '" />' );
			}

			this.destroy();

			torro.Builder.getInstance().trigger( 'removeContainer', [ this.container, this ] );
		},

		listenAddElement: function( element ) {
			var view = new torro.Builder.ElementView( element, this.options );
			var $dragDropArea = this.$panel.find( '.drag-drop-area' );

			$dragDropArea.append( view.$wrap );

			view.render();

			if ( $dragDropArea.sortable( 'instance' ) ) {
				$dragDropArea.sortable( 'refresh' );
			}

			torro.Builder.getInstance().trigger( 'addElement', [ element, view ] );
		},

		listenChangeLabel: function( container, label ) {
			var name = torro.escapeSelector( torro.getFieldName( this.container, 'label' ) );

			this.$panel.find( 'input[name="' + name + '"]' ).val( label );
		},

		listenChangeSort: function( container, sort ) {
			var name = torro.escapeSelector( torro.getFieldName( this.container, 'sort' ) );

			this.$panel.find( 'input[name="' + name + '"]' ).val( sort );
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

		openAddElementFrame: function() {
			this.addElementFrame.open();
		},

		addElement: function( selectedElementType ) {
			var element;

			if ( ! selectedElementType ) {
				return;
			}

			element = this.container.elements.create({
				type: selectedElementType
			});

			this.container.elements.toggleActive( element.get( 'id' ) );
		},

		deleteContainer: function() {
			torro.askConfirmation( torro.Builder.i18n.confirmDeleteContainer, _.bind( function() {
				this.container.collection.remove( this.container );
			}, this ) );
		},

		updateElementsSorted: function( e, ui ) {
			var container = this.container;

			ui.item.parent().find( '.torro-element' ).each( function( index ) {
				var $element = $( this );
				var element  = container.elements.get( $element.attr( 'id' ).replace( 'torro-element-', '' ) );

				element.set( 'sort', index );
			});

			container.elements.sort();
		}
	});

	torro.Builder.ContainerView = ContainerView;

})( window.torro, window.jQuery, window._ );
