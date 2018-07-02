( function( torro, _, Backbone, wp ) {
	'use strict';

	var Frame = wp.media.view.Frame;
	var AddElementFrame;

	AddElementFrame = Frame.extend({
		className: 'media-frame',
		template:  torro.template( 'add-element-frame' ),
		regions:   [ 'menu', 'title', 'content', 'toolbar' ],

		events: {
			'click div.media-frame-title h1': 'toggleMenu'
		},

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

			this.on( 'attach', _.bind( this.views.ready, this.views ), this );
			this.on( 'attach', this.showMenu, this );

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
				new torro.Builder.AddElement.State.ElementTypeLibrary({
					title:      this.options.title,
					collection: this.options.collection,
					priority:   20
				})
			]);
		},

		bindHandlers: function() {
			this.on( 'menu:create:default', this.createMenu, this );
			this.on( 'title:create:default', this.createTitle, this );
			this.on( 'content:create:select-element-type', this.createContent, this );
			this.on( 'toolbar:create:insert-element', this.createToolbar, this );

			this.on( 'title:render', function( view ) {
				view.$el.append( '<span class="dashicons dashicons-arrow-down"></span>' );
			});
		},

		createMenu: function( menu ) {
			menu.view = new wp.media.view.Menu({
				controller: this
			});
		},

		toggleMenu: function() {
			this.$el.find( '.media-menu' ).toggleClass( 'visible' );
		},

		createTitle: function( title ) {
			title.view = new wp.media.View({
				controller: this,
				tagName: 'h1'
			});
		},

		createContent: function( content ) {
			content.view = new torro.Builder.AddElement.View.ElementTypesBrowser({
				controller: this,
				collection: this.options.collection
			});
		},

		createToolbar: function( toolbar ) {
			var controller = this;

			toolbar.view = new torro.Builder.AddElement.View.InsertElementToolbar({
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
		},

		showMenu: function() {

			// This fixes that the menu is not shown otherwise.
			this.$el.removeClass( 'hide-menu' );
		}
	});

	_.each([ 'open', 'close', 'attach', 'detach', 'escape' ], function( method ) {
		AddElementFrame.prototype[ method ] = function() {
			if ( this.modal ) {
				this.modal[ method ].apply( this.modal, arguments );
			}
			return this;
		};
	});

	torro.Builder.AddElement.View.Frame = AddElementFrame;

})( window.torro, window._, window.Backbone, window.wp );
