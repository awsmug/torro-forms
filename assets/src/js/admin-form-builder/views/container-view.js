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
			this.$tab.html( this.tabTemplate( this.container.attributes ) );
			this.$panel.html( this.panelTemplate( this.container.attributes ) );
			this.$footerPanel.html( this.footerPanelTemplate( this.container.attributes ) );

			this.attach();
		},

		destroy: function() {
			this.detach();

			this.$tab.remove();
			this.$panel.remove();
			this.$footerPanel.remove();
		},

		attach: function() {
			this.container.collection.props.on( 'change:selected', this.listenChangeSelected, this );

			this.$tab.on( 'click', _.bind( this.setSelected, this ) );

			// TODO: add jQuery hooks
		},

		detach: function() {
			this.container.collection.props.off( 'change:selected', this.listenChangeSelected, this );

			this.$tab.off( 'click', _.bind( this.setSelected, this ) );

			// TODO: remove jQuery hooks
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
		}
	});

	torro.Builder.ContainerView = ContainerView;

})( window.torro, window.jQuery, window._ );
