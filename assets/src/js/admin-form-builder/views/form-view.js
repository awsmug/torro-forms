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
			var i;

			console.log( this.form );

			this.$canvas.html( this.canvasTemplate( this.form.attributes ) );

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

			// TODO: add jQuery hooks
		},

		detach: function() {
			this.form.containers.off( 'add remove reset', _.bind( this.checkHasContainers, this ) );
			this.form.containers.off( 'add', this.listenAddContainer, this );

			this.$addButton.off( 'click', _.bind( this.addContainer, this ) );

			// TODO: remove jQuery hooks
		},

		listenAddContainer: function( container ) {
			var view = new torro.Builder.ContainerView( container );

			view.$tab.insertBefore( this.$addButton );
			view.$panel.insertBefore( this.$addPanel );
			this.$canvas.find( '.torro-form-canvas-footer' ).append( view.$footerPanel );

			view.render();
		},

		checkHasContainers: function() {
			if ( this.form.containers.length ) {
				this.$addPanel.attr( 'aria-hidden', 'true' );
			} else {
				this.$addPanel.attr( 'aria-hidden', 'false' );
			}
		},

		addContainer: function() {
			this.form.containers.create();
		}
	});

	torro.Builder.FormView = FormView;

})( window.torro, window.jQuery, window._ );
