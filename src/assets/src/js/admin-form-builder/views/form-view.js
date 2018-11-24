( function( torro, $, _ ) {
	'use strict';

	/**
	 * A form view.
	 *
	 * @class
	 *
	 * @param {jQuery}             $canvas Form canvas div.
	 * @param {torro.Builder.Form} form    Form model.
	 * @param {object}             options View options.
	 */
	function FormView( $canvas, form, options ) {
		this.form = form;
		this.options = options || {};

		this.canvasTemplate = torro.template( 'form-canvas' );

		this.$canvas = $canvas;
	}

	_.extend( FormView.prototype, {
		render: function() {
			var $deletedWrap, i;

			$deletedWrap = $( '<div />' );
			$deletedWrap.attr( 'id', 'torro-deleted-wrap' );
			$deletedWrap.css( 'display', 'none' );

			this.$canvas.html( this.canvasTemplate( this.form.attributes ) );
			this.$canvas.after( $deletedWrap );

			this.$addButton = this.$canvas.find( '.add-button' );
			this.$addPanel  = this.$canvas.find( '.add-panel' );

			this.checkHasContainers();

			for ( i = 0; i < this.form.containers.length; i++ ) {
				this.listenAddContainer( this.form.containers.at( i ) );
			}

			this.attach();
		},

		destroy: function() {
			this.detach();

			this.$canvas.empty();
		},

		attach: function() {
			this.form.containers.on( 'add', this.listenAddContainer, this );
			this.form.containers.on( 'add remove reset', this.checkHasContainers, this );

			this.$addButton.on( 'click', _.bind( this.addContainer, this ) );
		},

		detach: function() {
			this.$addButton.off( 'click', _.bind( this.addContainer, this ) );

			this.form.containers.off( 'add remove reset', _.bind( this.checkHasContainers, this ) );
			this.form.containers.off( 'add', this.listenAddContainer, this );
		},

		listenAddContainer: function( container ) {
			var view = new torro.Builder.ContainerView( container, this.options );

			view.$tab.insertBefore( this.$addButton );
			view.$panel.insertBefore( this.$addPanel );
			this.$canvas.find( '.torro-form-canvas-footer' ).append( view.$footerPanel );

			view.render();

			torro.Builder.getInstance().trigger( 'addContainer', [ container, view ] );
		},

		checkHasContainers: function() {
			if ( this.form.containers.length ) {
				this.$addButton.removeClass( 'is-active' );
				this.$addPanel.attr( 'aria-hidden', 'true' );
			} else {
				this.$addButton.addClass( 'is-active' );
				this.$addPanel.attr( 'aria-hidden', 'false' );
			}
		},

		addContainer: function() {
			this.form.containers.create();
		}
	});

	torro.Builder.FormView = FormView;

})( window.torro, window.jQuery, window._ );
