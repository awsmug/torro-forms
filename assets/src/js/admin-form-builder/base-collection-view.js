( function( torroBuilder, _, wp ) {
	'use strict';

	/**
	 * Base for a form builder collection view.
	 *
	 * @class
	 * @augments wp.Backbone.View
	 */
	torroBuilder.BaseCollectionView = wp.Backbone.View.extend({

		/**
		 * Model view class for the collection view.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {function}
		 */
		modelView: torroBuilder.BaseModelView,

		/**
		 * Performs additional initialization logic.
		 *
		 * Sets up the view lookup and some hooks.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		initialize: function() {
			this._viewsByCid = {};

			this.collection.on( 'add', function( model ) {
				if ( 1 === this.collection.length ) {
					this.$el.removeClass( 'is-empty' );
				}

				this.views.add( this.createModelView( model ), {
					at: this.collection.indexOf( model )
				});
			}, this );

			this.collection.on( 'remove', function( model ) {
				var view = this._viewsByCid[ model.cid ];
				delete this._viewsByCid[ model.cid ];

				if ( ! this.collection.length ) {
					this.$el.addClass( 'is-empty' );
				}

				if ( view ) {
					view.remove();
				}
			}, this );

			this.collection.on( 'reset', this.render, this );
		},

		/**
		 * Creates a new model view for the collection view.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {torro.Builder.BaseModel} model Model to create a view for.
		 * @returns {torro.Builder.BaseModelView} New model view.
		 */
		createModelView: function( model ) {
			var view = new this.modelView({
				model:      model,
				collection: this.collection
			});

			return this._viewsByCid[ model.cid ] = view;
		},

		/**
		 * Renders the view, and subviews.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @returns {torro.Builder.BaseCollectionView} Returns itself to allow chaining.
		 */
		render: function() {
			var options;

			if ( this.prepare ) {
				options = this.prepare();
			}

			this.views.detach();

			if ( this.collection.length ) {
				this.$el.removeClass( 'is-empty' );

				if ( this.template ) {
					options = options || {};
					this.trigger( 'prepare', options );
					this.$el.html( this.template( options ) );
				}
			} else {
				this.$el.addClass( 'is-empty' );

				if ( this.emptyTemplate ) {
					options = options || {};
					this.trigger( 'prepare', options );
					this.$el.html( this.emptyTemplate( options ) );
				}
			}

			this.views.render();
			return this;
		},

		/**
		 * Prepares collection data for the template.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Template data.
		 */
		prepare: function() {
			if ( this.collection.length ) {
				this.views.set( this.collection.map( this.createModelView, this ) );
			} else {
				this.views.unset();
			}

			return this.options;
		},

		/**
		 * Undelegates events related to the view.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {torro.Builder.BaseCollectionView} Returns itself to allow chaining.
		 */
		dispose: function() {
			if ( this.collection && this.collection.props && this.collection.props.off ) {
				this.collection.props.off( null, null, this );
			}

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
		 * @returns {torro.Builder.BaseCollectionView} Returns itself to allow chaining.
		 */
		remove: function() {
			this.dispose();

			return wp.Backbone.View.prototype.remove.apply( this, arguments );
		}
	});

})( window.torro.Builder, window._, window.wp );
