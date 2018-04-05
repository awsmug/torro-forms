( function( torro, _, Backbone, wp ) {
	'use strict';

	var Frame = wp.media.view.Frame;
	var AddElementFrameView;
	var ElementTypeLibrary;

	ElementTypeLibrary = wp.media.controller.State.extend({
		defaults: {
			id: 'element-type-library',
			title: 'Select Element Type',
			content: 'default',
			toolbar: 'default'
		},

		initialize: function() {
			if ( ! this.get( 'collection' ) ) {
				this.set( 'collection', new Backbone.Collection( [] ) );
			}

			this.set( 'selected', null );
		}
	});

	AddElementFrameView = Frame.extend({
		className: 'media-frame',
		template:  torro.template( 'add-element-frame' ),
		regions:   [ 'title', 'content', 'toolbar' ],

		initialize: function() {
			Frame.prototype.initialize.apply( this, arguments );

			_.defaults( this.options, {
				title: '',
				buttonLabel: '',
				modal: true,
				collection: [],
				state: 'element-type-library'
			});

			this.$el.addClass( 'wp-core-ui' );

			if ( this.options.modal ) {
				this.modal = new wp.media.view.Modal({
					controller: this,
					title:      this.options.title
				});

				this.modal.content( this );
			}

			console.log( this );

			this.on( 'attach', _.bind( this.views.ready, this.views ), this );

			this.createCollection();
			this.createStates();
			this.bindHandlers();

			this.title.mode( 'default' );
		},

		render: function() {
			if ( ! this.state() && this.options.state ) {
				this.setState( this.options.state );
			}

			return Frame.prototype.render.apply( this, arguments );
		},

		createCollection: function() {
			var collection = this.options.collection;
			var elementTypes;

			if ( ! ( collection instanceof Backbone.Collection ) ) {
				elementTypes = [];
				if ( collection instanceof torro.Builder.ElementTypes ) {
					_.each( collection.getAll(), function( elementType ) {
						elementTypes.push( elementType.attributes );
					});
				} else if ( collection ) {
					elementTypes = collection;
				}

				this.options.collection = new Backbone.Collection( elementTypes );
			}
		},

		createStates: function() {
			this.states.add([
				new ElementTypeLibrary({
					title:      this.options.title,
					collection: this.options.collection,
					priority:   20
				})
			]);
		},

		bindHandlers: function() {
			this.on( 'title:create:default', this.createTitle, this );
			this.on( 'content:create:default', this.createContent, this );
			this.on( 'toolbar:create:default', this.createToolbar, this );

			this.on( 'title:render', function( view ) {
				view.$el.append( '<span class="dashicons dashicons-arrow-down"></span>' );
			});
		},

		createTitle: function( title ) {
			title.view = new wp.media.View({
				controller: this,
				tagName: 'h1'
			});
		},

		createContent: function( content ) {
			content.view = new torro.Builder.ElementTypesBrowserView({
				controller: this,
				collection: this.options.collection
			});
		},

		createToolbar: function( toolbar ) {
			var controller = this;

			toolbar.view = new wp.media.view.Toolbar({
				controller: this,
				items: {
					insert: {
						style:    'primary',
						text:     this.options.buttonLabel,
						priority: 80,
						requires: { selected: true },

						click: function() {
							var state    = controller.state();
							var selected = state.get( 'selected' );

							controller.close();
							state.trigger( 'insert', selected ).reset();
						}
					}
				}
			});
		}
	});

	_.each([ 'open', 'close', 'attach', 'detach', 'escape' ], function( method ) {
		AddElementFrameView.prototype[ method ] = function() {
			if ( this.modal ) {
				this.modal[ method ].apply( this.modal, arguments );
			}
			return this;
		};
	});

	torro.Builder.AddElementFrameView = AddElementFrameView;

})( window.torro, window._, window.Backbone, window.wp );
