( function( torroBuilder, _, wp ) {
	'use strict';

	/**
	 * Base for a form builder model view.
	 *
	 * @class
	 * @augments wp.Backbone.View
	 */
	torroBuilder.BaseModelView = wp.Backbone.View.extend({

		/**
		 * Renders the view, and subviews.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @returns {torro.Builder.BaseModelView} Returns itself to allow chaining.
		 */
		render: function() {
			var options;

			if ( this.prepare ) {
				options = this.prepare();
			}

			this.views.detach();

			if ( this.template ) {
				options = options || {};
				this.trigger( 'prepare', options );
				this.$el.html( this.template( options ) );
			}

			this.views.render();
			return this;
		},

		/**
		 * Prepares model data for the template.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @return {object} Template data.
		 */
		prepare: function() {
			return _.extend( {}, this.model.attributes, this.options );
		},

		/**
		 * Undelegates events related to the view.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @return {torro.Builder.BaseModelView} Returns itself to allow chaining.
		 */
		dispose: function() {

			// Undelegating events, removing events from the model, and
			// removing events from the controller mirror the code for
			// `Backbone.View.dispose` in Backbone 0.9.8 development.
			this.undelegateEvents();

			if ( this.model && this.model.off ) {
				this.model.off( null, null, this );
			}

			if ( this.collection && this.collection.off ) {
				this.collection.off( null, null, this );
			}

			return this;
		},

		/**
		 * Removes the view from the DOM.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @return {torro.Builder.BaseModelView} Returns itself to allow chaining.
		 */
		remove: function() {
			this.dispose();

			return wp.Backbone.View.prototype.remove.apply( this, arguments );
		}
	});

})( window.torro.Builder, window._, window.wp );
