/*!
 * Torro Forms Version 1.0.8 (https://torro-forms.com)
 * Licensed under GNU General Public License v2 (or later) (http://www.gnu.org/licenses/gpl-2.0.html)
 */
window.torro = window.torro || {};

( function( torro, $, _, i18n ) {
	'use strict';

	var instanceCount = 0,
		initialized = [],
		callbacks = {},
		builder;

	/**
	 * A form builder instance.
	 *
	 * @class
	 *
	 * @param {string} selector DOM selector for the wrapping element for the UI.
	 */
	function Builder( selector ) {
		instanceCount++;
		callbacks[ 'builder' + instanceCount ] = [];

		this.instanceNumber = instanceCount;

		this.$el = $( selector );
	}

	_.extend( Builder.prototype, {

		/**
		 * Available element types.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {torro.Builder.ElementTypes}
		 */
		elementTypes: undefined,

		/**
		 * Current form model.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {torro.Builder.FormModel}
		 */
		form: undefined,

		/**
		 * Form view object.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {torro.Builder.FormView}
		 */
		formView: undefined,

		/**
		 * Initializes the form builder.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		init: function() {
			if ( ! this.$el.length ) {
				console.error( i18n.couldNotInitCanvas );
				return;
			}

			torro.api.init()
				.done( _.bind( function() {
					( new torro.api.collections.ElementTypes() ).fetch({
						data: {
							context: 'edit'
						},
						context: this,
						success: function( elementTypes ) {
							this.elementTypes = torro.Builder.ElementTypes.fromApiCollection( elementTypes );

							if ( 'auto-draft' !== $( '#original_post_status' ).val() ) {
								( new torro.api.models.Form({
									id: parseInt( $( '#post_ID' ).val(), 10 )
								}) ).fetch({
									data: {
										context: 'edit',
										_embed: true
									},
									context: this,
									success: function( form ) {
										$( document ).ready( _.bind( function() {
											var i;

											initialized.push( this.instanceCount );

											this.setupInitialData( form.attributes );
											this.setupViews();

											for ( i in callbacks[ 'builder' + this.instanceCount ] ) {
												callbacks[ 'builder' + this.instanceCount ][ i ]( this );
											}

											delete callbacks[ 'builder' + this.instanceCount ];
										}, this ) );
									},
									error: function() {
										$( document ).ready( _.bind( function() {
											this.fail( i18n.couldNotLoadData );
										}, this ) );
									}
								});
							} else {
								$( document ).ready( _.bind( function() {
									var i;

									initialized.push( this.instanceCount );

									this.setupInitialData();
									this.setupViews();

									for ( i in callbacks[ 'builder' + this.instanceCount ] ) {
										callbacks[ 'builder' + this.instanceCount ][ i ]( this );
									}

									delete callbacks[ 'builder' + this.instanceCount ];
								}, this ) );
							}
						},
						error: function() {
							$( document ).ready( _.bind( function() {
								this.fail( i18n.couldNotLoadData );
							}, this ) );
						}
					});
				}, this ) )
				.fail( _.bind( function() {
					$( document ).ready( _.bind( function() {
						this.fail( i18n.couldNotLoadData );
					}, this ) );
				}, this ) );
		},

		/**
		 * Sets up initial data for the form builder.
		 *
		 * This method only works if the form builder has been initialized.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {object|undefined} form REST API form response including embedded data, or
		 *                                undefined if this is a new form.
		 */
		setupInitialData: function( form ) {
			var container, element, elementChoice, elementSetting, elementParents, i;

			if ( ! _.contains( initialized, this.instanceCount ) ) {
				return;
			}

			if ( form ) {
				this.form = new torro.Builder.FormModel( form );

				if ( form._embedded.containers && form._embedded.containers[0] ) {
					this.form.containers.add( form._embedded.containers[0] );

					if ( form._embedded.elements && form._embedded.elements[0] ) {
						elementParents = {};

						for ( i = 0; i < form._embedded.elements[0].length; i++ ) {
							element = form._embedded.elements[0][ i ];

							container = this.form.containers.get( element.container_id );
							if ( container ) {
								container.elements.add( element );

								elementParents[ element.id ] = element.container_id;
							}
						}

						if ( form._embedded.element_choices && form._embedded.element_choices[0] ) {
							for ( i = 0; i < form._embedded.element_choices[0].length; i++ ) {
								elementChoice = form._embedded.element_choices[0][ i ];

								if ( elementParents[ elementChoice.element_id ] ) {
									container = this.form.containers.get( elementParents[ elementChoice.element_id ] );
									if ( container ) {
										element = container.elements.get( elementChoice.element_id );
										if ( element ) {
											element.element_choices.add( elementChoice );
										}
									}
								}
							}
						}

						if ( form._embedded.element_settings && form._embedded.element_settings[0] ) {
							for ( i = 0; i < form._embedded.element_settings[0].length; i++ ) {
								elementSetting = form._embedded.element_settings[0][ i ];

								if ( elementParents[ elementSetting.element_id ] ) {
									container = this.form.containers.get( elementParents[ elementSetting.element_id ] );
									if ( container ) {
										element = container.elements.get( elementSetting.element_id );
										if ( element ) {
											element.setElementSetting( elementSetting );
										}
									}
								}
							}
						}
					}
				}
			} else {
				this.form = new torro.Builder.FormModel({});

				this.form.containers.add({});
			}
		},

		/**
		 * Sets up form builder views.
		 *
		 * This method only works if the form builder has been initialized.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		setupViews: function() {
			if ( ! _.contains( initialized, this.instanceCount ) ) {
				return;
			}

			this.formView = new torro.Builder.FormView( this.$el, this.form );

			this.formView.render();
		},

		/**
		 * Adds a callback that will be executed once the form builder has been initialized.
		 *
		 * If the form builder has already been initialized, the callback will be executed
		 * immediately.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {function} callback Callback to execute. Should accept the form builder instance
		 *                            as parameter.
		 */
		onLoad: function( callback ) {
			if ( _.isUndefined( callbacks[ 'builder' + this.instanceCount ] ) ) {
				callback( this );
				return;
			}

			callbacks[ 'builder' + this.instanceCount ].push( callback );
		},

		/**
		 * Shows a failure message for the form builder in the UI.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {string} message Failure message to display.
		 */
		fail: function( message ) {
			var compiled = torro.template( 'failure' );

			this.$el.find( '.drag-drop-area' ).addClass( 'is-empty' ).html( compiled({ message: message }) );
		},

		/**
		 * Registers a function to be called whenever a certain form builder hook is triggered.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {string}   hook     Hook name.
		 * @param {function} callback Callback function to execute.
		 */
		on: function( hook, callback ) {
			hook = 'torro.' + hook;

			this.$el.on( hook, function() {

				// Pass on all arguments except the event.
				var args = Array.prototype.slice.call( arguments, 1 );

				if ( args.length ) {
					callback.apply( undefined, args );
				} else {
					callback.apply( undefined, undefined );
				}
			});
		},

		/**
		 * Triggers a hook for the form builder.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {string} hook Hook name.
		 * @param {array}  data Optional. Arguments to pass to each callback.
		 */
		trigger: function( hook, data ) {
			hook = 'torro.' + hook;
			data = data || [];

			this.$el.trigger( hook, data );
		}
	});

	torro.Builder = Builder;

	/**
	 * Returns the main form builder instance.
	 *
	 * It will be instantiated and initialized if it does not exist yet.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	torro.Builder.getInstance = function() {
		if ( ! builder ) {
			builder = new Builder( '#torro-form-canvas' );
			builder.init();
		}

		return builder;
	};

	// Scaffold the AddElement namespace for modal functionality.
	torro.Builder.AddElement = {
		State: {},
		View:  {}
	};

	torro.getFieldName = function( model, attribute ) {
		var groupSlug;

		if ( model instanceof torro.Builder.FormModel ) {
			groupSlug = 'forms';
		} else if ( model instanceof torro.Builder.ContainerModel ) {
			groupSlug = 'containers';
		} else if ( model instanceof torro.Builder.ElementModel ) {
			groupSlug = 'elements';
		} else if ( model instanceof torro.Builder.ElementChoiceModel ) {
			groupSlug = 'element_choices';
		} else if ( model instanceof torro.Builder.ElementSettingModel ) {
			groupSlug = 'element_settings';
		}

		if ( ! groupSlug ) {
			return;
		}

		return 'torro_' + groupSlug + '[' + model.get( 'id' ) + '][' + attribute + ']';
	};

	torro.getDeletedFieldName = function( model ) {
		var groupSlug;

		if ( model instanceof torro.Builder.FormModel ) {
			groupSlug = 'forms';
		} else if ( model instanceof torro.Builder.ContainerModel ) {
			groupSlug = 'containers';
		} else if ( model instanceof torro.Builder.ElementModel ) {
			groupSlug = 'elements';
		} else if ( model instanceof torro.Builder.ElementChoiceModel ) {
			groupSlug = 'element_choices';
		} else if ( model instanceof torro.Builder.ElementSettingModel ) {
			groupSlug = 'element_settings';
		}

		if ( ! groupSlug ) {
			return;
		}

		return 'torro_deleted_' + groupSlug + '[]';
	};

	torro.askConfirmation = function( message, successCallback ) {
		var $dialog = $( '<div />' );

		$dialog.html( message );

		$( 'body' ).append( $dialog );

		$dialog.dialog({
			dialogClass: 'wp-dialog torro-dialog',
			modal: true,
			autoOpen: true,
			closeOnEscape: true,
			minHeight: 80,
			buttons: [
				{
					text: i18n.yes,
					click: function() {
						successCallback();

						$( this ).dialog( 'close' );
						$( this ).remove();
					}
				},
				{
					text: i18n.no,
					click: function() {
						$( this ).dialog( 'close' );
						$( this ).remove();
					}
				}
			]
		});
	};

	torro.Builder.i18n = i18n;

}( window.torro, window.jQuery, window._, window.torroBuilderI18n ) );

( function( torroBuilder, _ ) {
	'use strict';

	/**
	 * An element type.
	 *
	 * @class
	 *
	 * @param {object} attributes Element type attributes.
	 */
	function ElementType( attributes ) {
		this.attributes = attributes;
	}

	_.extend( ElementType.prototype, {

		/**
		 * Returns the element type slug.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {string} Element type slug.
		 */
		getSlug: function() {
			return this.attributes.slug;
		},

		/**
		 * Returns the element type title.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {string} Element type title.
		 */
		getTitle: function() {
			return this.attributes.title;
		},

		/**
		 * Returns the element type description.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {string} Element type description.
		 */
		getDescription: function() {
			return this.attributes.description;
		},

		/**
		 * Returns the element type icon CSS class.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {string} Element type icon CSS class.
		 */
		getIconCssClass: function() {
			return this.attributes.icon_css_class;
		},

		/**
		 * Returns the element type icon SVG ID.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {string} Element type icon SVG ID.
		 */
		getIconSvgId: function() {
			return this.attributes.icon_svg_id;
		},

		/**
		 * Returns the element type icon URL.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {string} Element type icon URL.
		 */
		getIconUrl: function() {
			return this.attributes.icon_url;
		},

		/**
		 * Checks whether the element type is a non input element type.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {string} True if the element type is a non input element type, false otherwise.
		 */
		isNonInput: function() {
			return this.attributes.non_input;
		},

		/**
		 * Checks whether the element type is evaluable.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {string} True if the element type is evaluable, false otherwise.
		 */
		isEvaluable: function() {
			return this.attributes.evaluable;
		},

		/**
		 * Checks whether the element type contains multiple fields.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {string} True if the element type contains multiple fields, false otherwise.
		 */
		isMultiField: function() {
			return this.attributes.multifield;
		},

		/**
		 * Returns the settings sections that belong to the element type.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object[]} Element type sections.
		 */
		getSections: function() {
			return this.attributes.sections;
		},

		/**
		 * Returns the settings fields that belong to the element type.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object[]} Element type fields.
		 */
		getFields: function() {
			return this.attributes.fields;
		}
	});

	torroBuilder.ElementType = ElementType;

})( window.torro.Builder, window._ );

( function( torroBuilder, _ ) {
	'use strict';

	/**
	 * A list of available element types.
	 *
	 * @class
	 *
	 * @param {torro.Builder.ElementType[]} elementTypes Registered element type objects.
	 */
	function ElementTypes( elementTypes ) {
		var i;

		this.types = {};

		for ( i in elementTypes ) {
			this.types[ elementTypes[ i ].getSlug() ] = elementTypes[ i ];
		}
	}

	_.extend( ElementTypes.prototype, {

		/**
		 * Returns a specific element type.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {torro.Builder.ElementType|undefined} Element type object, or undefined if not available.
		 */
		get: function( slug ) {
			if ( _.isUndefined( this.types[ slug ] ) ) {
				return undefined;
			}

			return this.types[ slug ];
		},

		/**
		 * Returns all element types.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {torro.Builder.ElementType[]} All element type objects.
		 */
		getAll: function() {
			return this.types;
		}
	});

	/**
	 * Generates an element types list instance from a REST API response.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @returns {torro.Builder.ElementTypes} Element types object.
	 */
	ElementTypes.fromApiCollection = function( collection ) {
		var elementTypes = [];

		collection.each( function( model ) {
			var attributes = _.extend({}, model.attributes );
			if ( attributes._links ) {
				delete attributes._links;
			}
			if ( attributes._embedded ) {
				delete attributes._embedded;
			}

			elementTypes.push( new torroBuilder.ElementType( attributes ) );
		});

		return new ElementTypes( elementTypes );
	};

	torroBuilder.ElementTypes = ElementTypes;

})( window.torro.Builder, window._ );

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

( function( torro, Backbone, wp ) {
	'use strict';

	var State = wp.media.controller.State;
	var ElementTypeLibrary;

	ElementTypeLibrary = State.extend({
		defaults: {
			id: 'element-type-library',
			title: torro.Builder.i18n.selectElementType,
			menu: 'default',
			content: 'select-element-type',
			toolbar: 'insert-element'
		},

		initialize: function() {
			if ( ! this.get( 'collection' ) ) {
				this.set( 'collection', new Backbone.Collection( [] ) );
			}

			this.set( 'selected', null );
		},

		reset: function() {
			this.set( 'selected', null );
		}
	});

	torro.Builder.AddElement.State.ElementTypeLibrary = ElementTypeLibrary;

})( window.torro, window.Backbone, window.wp );

( function( torro, $, _, Backbone, wp ) {
	'use strict';

	var View = wp.media.View;
	var ElementTypesBrowser;

	ElementTypesBrowser = View.extend({
		tagName: 'div',
		className: 'element-types-browser',
		template:  torro.template( 'element-types-browser' ),

		events: {
			'click .torro-element-type': 'setSelected',
			'keyup .torro-element-type': 'setSelected'
		},

		initialize: function() {
			View.prototype.initialize.apply( this, arguments );

			_.defaults( this.options, {
				collection: []
			});

			if ( ! ( this.options.collection instanceof Backbone.Collection ) ) {
				this.options.collection = new Backbone.Collection( this.options.collection );
			}

			this.controller.state().on( 'change:selected', this.listenToSelected, this );
		},

		prepare: function() {
			var data = {
				elementTypes: this.options.collection.toJSON(),
				selectedElementType: this.controller.state().get( 'selected' )
			};

			return data;
		},

		setSelected: function( e ) {
			if ( 'keyup' === e.type && 32 !== e.keyCode ) {
				return;
			}

			if ( e.currentTarget && e.currentTarget.dataset.slug ) {
				this.controller.state().set( 'selected', e.currentTarget.dataset.slug );
			}
		},

		listenToSelected: function( state, selected ) {
			this.$el.find( '.torro-element-type' ).each( function() {
				var $this = $( this );

				$this.toggleClass( 'is-selected', $this.data( 'slug' ) === selected );
			});
		}
	});

	torro.Builder.AddElement.View.ElementTypesBrowser = ElementTypesBrowser;

})( window.torro, window.jQuery, window._, window.Backbone, window.wp );

( function( torro, _, wp ) {
	'use strict';

	var Toolbar = wp.media.view.Toolbar;
	var InsertElementToolbar;

	InsertElementToolbar = Toolbar.extend({
		initialize: function() {
			wp.media.view.Toolbar.prototype.initialize.apply( this, arguments );

			this.controller.state().on( 'change:selected', this.refresh, this );
		},

		refresh: function() {
			var selected = this.controller.state().get( 'selected' );

			_.each( this._views, function( button ) {
				var disabled = false;

				if ( ! button.model || ! button.options || ! button.options.requires ) {
					return;
				}

				if ( button.options.requires && button.options.requires.selected && ( ! selected || ! selected.length ) ) {
					disabled = true;
				}

				button.model.set( 'disabled', disabled );
			});
		}
	});

	torro.Builder.AddElement.View.InsertElementToolbar = InsertElementToolbar;

})( window.torro, window._, window.wp );

( function( torroBuilder, torro, _, Backbone ) {
	'use strict';

	/**
	 * Base for a form builder model.
	 *
	 * This model has no persistence with the server.
	 *
	 * @class
	 * @augments Backbone.Model
	 */
	torroBuilder.BaseModel = Backbone.Model.extend({

		/**
		 * Related REST links.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {object}
		 */
		links: {},

		/**
		 * Instantiates a new model.
		 *
		 * Overrides constructor in order to strip out unnecessary attributes.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {object} [attributes] Model attributes.
		 * @param {object} [options]    Options for the model behavior.
		 */
		constructor: function( attributes, options ) {
			var attrs = attributes || {};
			var idAttribute = this.idAttribute || Backbone.Model.prototype.idAttribute || 'id';

			if ( attrs._links ) {
				this.links = attrs._links;
			}

			attrs = _.omit( attrs, [ '_links', '_embedded' ] );

			if ( ! attrs[ idAttribute ] ) {
				attrs[ idAttribute ] = torro.generateTempId();
			}

			Backbone.Model.apply( this, [ attrs, options ] );
		},

		/**
		 * Synchronizes the model with the server.
		 *
		 * Overrides synchronization in order to disable synchronization.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {boolean} True on success, false on failure.
		 */
		sync: function( method, model, options ) {
			if ( 'create' === method && model.has( model.idAttribute ) ) {
				if ( ! options.attrs ) {
					options.attrs = model.toJSON( options );
				}

				options.attrs = _.omit( options.attrs, model.idAttribute );
			}

			return false;
		},

		/**
		 * Checks whether this model is new.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @return {boolean} True if the model is new, false otherwise.
		 */
		isNew: function() {
			return ! this.has( this.idAttribute ) || torro.isTempId( this.get( this.idAttribute ) );
		}
	});

})( window.torro.Builder, window.torro, window._, window.Backbone );

( function( torroBuilder, torro, _, Backbone ) {
	'use strict';

	/**
	 * Base for a form builder collection.
	 *
	 * This collection has no persistence with the server.
	 *
	 * @class
	 * @augments Backbone.Collection
	 */
	torroBuilder.BaseCollection = Backbone.Collection.extend({

		/**
		 * Model class for the collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {function}
		 */
		model: torroBuilder.BaseModel,

		/**
		 * Default properties for the collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		defaultProps: {},

		/**
		 * Instantiates a new collection.
		 *
		 * Sets up collection properties.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {object[]} [models]  Models for the collection.
		 * @param {object}   [options] Options for the model behavior.
		 */
		constructor: function( models, options ) {
			var props = _.defaults( options && options.props || {}, this.defaultProps );

			this.props = new Backbone.Model( props );

			if ( this.urlEndpoint ) {
				this.url = torro.api.root + torro.api.versionString + this.urlEndpoint;
			}

			Backbone.Collection.apply( this, arguments );
		},

		/**
		 * Synchronizes the collection with the server.
		 *
		 * Overrides synchronization in order to disable synchronization.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {boolean} True on success, false on failure.
		 */
		sync: function() {
			return false;
		}
	});

})( window.torro.Builder, window.torro, window._, window.Backbone );

( function( torroBuilder, _ ) {
	'use strict';

	/**
	 * A single container.
	 *
	 * @class
	 * @augments torro.Builder.BaseModel
	 */
	torroBuilder.ContainerModel = torroBuilder.BaseModel.extend({

		/**
		 * Returns container defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Container defaults.
		 */
		defaults: function() {
			return _.extend( _.clone({
				id: 0,
				form_id: 0,
				label: '',
				sort: 0
			}), this.collection.getDefaultAttributes() );
		},

		/**
		 * Element collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		elements: undefined,

		/**
		 * Instantiates a new model.
		 *
		 * Overrides constructor in order to strip out unnecessary attributes.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {object} [attributes] Model attributes.
		 * @param {object} [options]    Options for the model behavior.
		 */
		constructor: function( attributes, options ) {
			attributes = attributes || {};

			torroBuilder.BaseModel.apply( this, [ attributes, options ] );

			this.elements = new torroBuilder.ElementCollection([], {
				props: {
					container_id: this.get( 'id' )
				},
				comparator: 'sort'
			});
		}
	});

})( window.torro.Builder, window._ );

( function( torroBuilder, _ ) {
	'use strict';

	/**
	 * A single element choice.
	 *
	 * @class
	 * @augments torro.Builder.BaseModel
	 */
	torroBuilder.ElementChoiceModel = torroBuilder.BaseModel.extend({

		/**
		 * Returns element choice defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Element choice defaults.
		 */
		defaults: function() {
			return _.extend( _.clone({
				id: 0,
				element_id: 0,
				field: '',
				value: '',
				sort: 0
			}), this.collection.getDefaultAttributes() );
		}
	});

})( window.torro.Builder, window._ );

( function( torroBuilder, _ ) {
	'use strict';

	/**
	 * A single element.
	 *
	 * @class
	 * @augments torro.Builder.BaseModel
	 */
	torroBuilder.ElementModel = torroBuilder.BaseModel.extend({

		/**
		 * Returns element defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Element defaults.
		 */
		defaults: function() {
			return _.extend( _.clone({
				id: 0,
				container_id: 0,
				label: '',
				sort: 0,
				type: 'textfield'
			}), this.collection.getDefaultAttributes() );
		},

		/**
		 * Element type object.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		element_type: null,

		/**
		 * Element choice collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		element_choices: null,

		/**
		 * Element setting collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		element_settings: undefined,

		/**
		 * Identifier of the currently active section.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {string}
		 */
		active_section: undefined,

		/**
		 * Instantiates a new model.
		 *
		 * Overrides constructor in order to strip out unnecessary attributes.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {object} [attributes] Model attributes.
		 * @param {object} [options]    Options for the model behavior.
		 */
		constructor: function( attributes, options ) {
			torroBuilder.BaseModel.apply( this, [ attributes, options ] );

			this.element_choices = new torroBuilder.ElementChoiceCollection([], {
				props: {
					element_id: this.get( 'id' )
				},
				comparator: 'sort'
			});

			this.element_settings = new torroBuilder.ElementSettingCollection([], {
				props: {
					element_id: this.get( 'id' )
				}
			});

			this.listenTypeChanged( this, this.get( 'type' ) );

			this.on( 'change:type', this.listenTypeChanged, this );
		},

		setElementSetting: function( elementSetting ) {
			var existingSetting, index;

			if ( elementSetting.attributes ) {
				elementSetting = elementSetting.attributes;
			}

			existingSetting = this.element_settings.findWhere({
				name: elementSetting.name
			});
			if ( ! existingSetting ) {
				return false;
			}

			index = this.element_settings.indexOf( existingSetting );

			this.element_settings.remove( existingSetting );
			this.element_settings.add( elementSetting, {
				at: index
			});

			return true;
		},

		setActiveSection: function( section ) {
			if ( section === this.active_section ) {
				return;
			}

			this.active_section = section;

			this.trigger( 'changeActiveSection', this, this.active_section );
		},

		getActiveSection: function() {
			return this.active_section;
		},

		listenTypeChanged: function( element, type ) {
			var sections, settingFields, settingNames, oldSettings = {};

			element.element_type = torroBuilder.getInstance().elementTypes.get( type );
			if ( ! element.element_type ) {
				return;
			}

			this.trigger( 'changeElementType', element, element.element_type );

			sections = element.element_type.getSections();
			if ( sections.length ) {
				element.setActiveSection( sections[0].slug );
			}

			settingFields = element.element_type.getFields().filter( function( field ) {
				return ! field.is_label && ! field.is_choices;
			});

			settingNames = settingFields.map( function( settingField ) {
				return settingField.slug;
			});

			element.element_settings.each( function( elementSetting ) {
				if ( settingNames.includes( elementSetting.name ) ) {
					oldSettings[ elementSetting.name ] = elementSetting.attributes;
				}
			});
			element.element_settings.reset();

			_.each( settingFields, function( settingField ) {
				if ( oldSettings[ settingField.slug ] ) {
					element.element_settings.add( oldSettings[ settingField.slug ] );
				} else {
					element.element_settings.create({
						name: settingField.slug,
						value: settingField['default'] || null
					});
				}
			});
		}
	});

})( window.torro.Builder, window._ );

( function( torroBuilder, _ ) {
	'use strict';

	/**
	 * A single element setting.
	 *
	 * @class
	 * @augments torro.Builder.BaseModel
	 */
	torroBuilder.ElementSettingModel = torroBuilder.BaseModel.extend({

		/**
		 * Returns element choice defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Element choice defaults.
		 */
		defaults: function() {
			return _.extend( _.clone({
				id: 0,
				element_id: 0,
				name: '',
				value: ''
			}), this.collection.getDefaultAttributes() );
		}
	});

})( window.torro.Builder, window._ );

( function( torroBuilder ) {
	'use strict';

	/**
	 * A single form.
	 *
	 * @class
	 * @augments torro.Builder.BaseModel
	 */
	torroBuilder.FormModel = torroBuilder.BaseModel.extend({

		/**
		 * Form defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		defaults: {
			id: 0,
			title: '',
			slug: '',
			author: 0,
			status: 'draft',
			timestamp: 0,
			timestamp_modified: 0
		},

		/**
		 * Container collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		containers: undefined,

		/**
		 * Instantiates a new model.
		 *
		 * Overrides constructor in order to strip out unnecessary attributes.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {object} [attributes] Model attributes.
		 * @param {object} [options]    Options for the model behavior.
		 */
		constructor: function( attributes, options ) {
			var containerProps;

			torroBuilder.BaseModel.apply( this, [ attributes, options ] );

			containerProps = {
				form_id: this.get( 'id' )
			};

			this.containers = new torroBuilder.ContainerCollection([], {
				props: containerProps,
				comparator: 'sort'
			});
		}
	});

})( window.torro.Builder );

( function( torroBuilder, _ ) {
	'use strict';

	/**
	 * A collection of containers.
	 *
	 * @class
	 * @augments torro.Builder.BaseCollection
	 */
	torroBuilder.ContainerCollection = torroBuilder.BaseCollection.extend({

		/**
		 * Model class for the container collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {function}
		 */
		model: torroBuilder.ContainerModel,

		/**
		 * REST endpoint URL part for accessing containers.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		urlEndpoint: 'containers',

		/**
		 * Default properties for the collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		defaultProps: {
			selected: false,
			form_id:  0
		},

		/**
		 * Returns container defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Container defaults.
		 */
		getDefaultAttributes: function() {
			var labelPlaceholder = torroBuilder.i18n.defaultContainerLabel;
			var labelNumber      = this.length + 1;
			var sort             = this.length;
			var last;

			if ( this.length ) {
				last = this.at( this.length - 1 );

				if ( last ) {
					sort = last.get( 'sort' ) + 1;

					if ( last.get( 'label' ) === labelPlaceholder.replace( '%s', sort ) ) {
						labelNumber = sort + 1;
					}
				}
			}

			return {
				form_id: this.props.get( 'form_id' ),
				label:   labelPlaceholder.replace( '%s', labelNumber ),
				sort:    sort
			};
		},

		initialize: function() {
			this.on( 'add', _.bind( this.maybeUpdateSelectedOnAdd, this ) );
			this.on( 'remove', _.bind( this.maybeUpdateSelectedOnRemove, this ) );
		},

		maybeUpdateSelectedOnAdd: function( container ) {
			if ( container ) {
				this.props.set( 'selected', container.get( 'id' ) );
			}
		},

		maybeUpdateSelectedOnRemove: function( container, containers, options ) {
			var index = options.index ? options.index - 1 : options.index;

			if ( container && this.props.get( 'selected' ) === container.get( 'id' ) ) {
				if ( this.length ) {
					this.props.set( 'selected', this.at( index ).get( 'id' ) );
				} else {
					this.props.set( 'selected', false );
				}
			}
		}
	});

})( window.torro.Builder, window._ );

( function( torroBuilder ) {
	'use strict';

	/**
	 * A collection of element choices.
	 *
	 * @class
	 * @augments torro.Builder.BaseCollection
	 */
	torroBuilder.ElementChoiceCollection = torroBuilder.BaseCollection.extend({

		/**
		 * Model class for the element choice collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {function}
		 */
		model: torroBuilder.ElementChoiceModel,

		/**
		 * REST endpoint URL part for accessing element choices.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		urlEndpoint: 'element_choices',

		/**
		 * Default properties for the collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		defaultProps: {
			element_id: 0
		},

		/**
		 * Returns element choice defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Element choice defaults.
		 */
		getDefaultAttributes: function() {
			return {
				element_id: this.props.get( 'element_id' ),
				sort:       this.length
			};
		}
	});

})( window.torro.Builder );

( function( torroBuilder ) {
	'use strict';

	/**
	 * A collection of elements.
	 *
	 * @class
	 * @augments torro.Builder.BaseCollection
	 */
	torroBuilder.ElementCollection = torroBuilder.BaseCollection.extend({

		/**
		 * Model class for the element collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {function}
		 */
		model: torroBuilder.ElementModel,

		/**
		 * REST endpoint URL part for accessing elements.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		urlEndpoint: 'elements',

		/**
		 * Default properties for the collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		defaultProps: {
			active:       [],
			container_id: 0
		},

		/**
		 * Returns element defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Element defaults.
		 */
		getDefaultAttributes: function() {
			return {
				container_id: this.props.get( 'container_id' ),
				sort:         this.length
			};
		},

		toggleActive: function( id ) {
			var active = this.props.get( 'active' );
			var index = active.indexOf( id );

			if ( index > -1 ) {
				active.splice( index, 1 );
			} else {
				active.push( id );
			}

			this.props.set( 'active', active );

			this.props.trigger( 'toggleActive', this, active, {});
		}
	});

})( window.torro.Builder );

( function( torroBuilder ) {
	'use strict';

	/**
	 * A collection of element settings.
	 *
	 * @class
	 * @augments torro.Builder.BaseCollection
	 */
	torroBuilder.ElementSettingCollection = torroBuilder.BaseCollection.extend({

		/**
		 * Model class for the element setting collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {function}
		 */
		model: torroBuilder.ElementSettingModel,

		/**
		 * REST endpoint URL part for accessing element settings.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		urlEndpoint: 'element_settings',

		/**
		 * Default properties for the collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		defaultProps: {
			element_id: 0
		},

		/**
		 * Returns element setting defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Element setting defaults.
		 */
		getDefaultAttributes: function() {
			return {
				element_id: this.props.get( 'element_id' ),
				sort:       this.length
			};
		}
	});

})( window.torro.Builder );

( function( torroBuilder ) {
	'use strict';

	/**
	 * A collection of forms.
	 *
	 * @class
	 * @augments torro.Builder.BaseCollection
	 */
	torroBuilder.FormCollection = torroBuilder.BaseCollection.extend({

		/**
		 * Model class for the form collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {function}
		 */
		model: torroBuilder.FormModel,

		/**
		 * REST endpoint URL part for accessing forms.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		urlEndpoint: 'forms'
	});

})( window.torro.Builder );

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

( function( torro, $, _, fieldsAPI, dummyFieldManager ) {
	'use strict';

	function deepClone( input ) {
		var output = _.clone( input );

		_.each( output, function( value, key ) {
			var temp, i;

			if ( _.isArray( value ) ) {
				temp = [];

				for ( i = 0; i < value.length; i++ ) {
					if ( _.isObject( value[ i ] ) ) {
						temp.push( deepClone( value[ i ] ) );
					} else {
						temp.push( value[ i ] );
					}
				}

				output[ key ] = temp;
			} else if ( _.isObject( value ) ) {
				output[ key ] = deepClone( value );
			}
		});

		return output;
	}

	function getObjectReplaceableFields( obj ) {
		var fields = {};

		_.each( obj, function( value, key ) {
			if ( _.isObject( value ) && ! _.isArray( value ) ) {
				value = getObjectReplaceableFields( value );
				if ( ! _.isEmpty( value ) ) {
					fields[ key ] = value;
				}
			} else if ( _.isString( value ) ) {
				if ( value.match( /%([A-Za-z0-9]+)%/g ) ) {
					fields[ key ] = value;
				}
			}
		});

		return fields;
	}

	function replaceObjectFields( obj, replacements, fields ) {
		if ( _.isUndefined( fields ) ) {
			fields = getObjectReplaceableFields( obj );
		}

		function _doReplacements( match, name ) {
			if ( ! _.isUndefined( replacements[ name ] ) ) {
				return replacements[ name ];
			}

			return match;
		}

		_.each( fields, function( value, key ) {
			if ( _.isObject( value ) ) {
				if ( ! _.isObject( obj[ key ] ) ) {
					obj[ key ] = {};
				}

				replaceObjectFields( obj[ key ], replacements, value );
			} else {
				obj[ key ] = value.replace( /%([A-Za-z0-9]+)%/g, _doReplacements );
			}
		});
	}

	function generateItem( itemInitial, index ) {
		var newItem = _.deepClone( itemInitial );

		replaceObjectFields( newItem, {
			index: index,
			indexPlus1: index + 1
		});

		return newItem;
	}

	function getElementFieldId( element, field ) {
		return 'torro_element_' + element.get( 'id' ) + '_' + field;
	}

	function parseFields( fields, element ) {
		var parsedFields = [];
		var hasLabel = false;

		_.each( fields, function( field ) {
			var parsedField;
			var elementChoices;
			var elementSetting;
			var tempId;

			if ( _.isUndefined( field.type ) || _.isUndefined( dummyFieldManager.fields[ 'dummy_' + field.type ] ) ) {
				return;
			}

			parsedField = deepClone( dummyFieldManager.fields[ 'dummy_' + field.type ] );

			parsedField.section     = field.section;
			parsedField.label       = field.label;
			parsedField.description = field.description;
			parsedField['default']  = field['default'] || null;

			if ( field.is_choices ) {
				elementChoices = element.element_choices.where({
					field: _.isString( field.is_choices ) ? field.is_choices : '_main'
				});

				tempId = torro.generateTempId();

				parsedField.repeatable = true;
				parsedField.repeatableLimit = 0;

				parsedField.id = getElementFieldId( element, field.slug );
				parsedField.labelAttrs.id = parsedField.id + '-label';

				parsedField.itemInitial.currentValue = parsedField['default'];
				parsedField.itemInitial['default']   = parsedField['default'];
				parsedField.itemInitial.element_id   = element.get( 'id' );
				parsedField.itemInitial.field        = _.isString( field.is_choices ) ? field.is_choices : '_main';
				parsedField.itemInitial.id           = parsedField.id + '-%indexPlus1%';
				parsedField.itemInitial.label        = torro.Builder.i18n.elementChoiceLabel.replace( '%s', '%indexPlus1%' );
				parsedField.itemInitial.name         = 'torro_element_choices[' + tempId + '_%index%][value]';
				parsedField.itemInitial.section      = parsedField.section;
				parsedField.itemInitial.sort         = '%index%';

				parsedField.itemInitial.inputAttrs.id   = parsedField.itemInitial.id;
				parsedField.itemInitial.inputAttrs.name = parsedField.itemInitial.name;

				if ( _.isArray( field.input_classes ) ) {
					parsedField.itemInitial.inputAttrs['class'] += ' ' + field.input_classes.join( ' ' );
				}

				parsedField.itemInitial.labelAttrs.id     = parsedField.itemInitial.id + '-label';
				parsedField.itemInitial.labelAttrs['for'] = parsedField.itemInitial.id;

				parsedField.itemInitial.wrapAttrs.id = parsedField.itemInitial.id + '-wrap';

				parsedField.wrapAttrs = deepClone( parsedField.itemInitial.wrapAttrs );
				parsedField.wrapAttrs.id = parsedField.id + '-wrap';

				_.each( elementChoices, function( elementChoice, index ) {
					var newItem = generateItem( parsedField.itemInitial, index );

					newItem.name = torro.getFieldName( elementChoice, 'value' );
					newItem.inputAttrs.name = newItem.name;

					newItem.currentValue = elementChoice.get( 'value' );

					parsedField.items.push( newItem );
				});
			} else {
				if ( field.repeatable ) {

					// Repeatable fields are currently not supported.
					return;
				}

				if ( field.is_label ) {

					// Only one label field is allowed.
					if ( hasLabel ) {
						return;
					}

					hasLabel = true;

					parsedField.id = getElementFieldId( element, 'label' );
					parsedField.name = torro.getFieldName( element, 'label' );
					parsedField.currentValue = element.get( 'label' );
				} else {

					elementSetting = element.element_settings.findWhere({
						name: field.slug
					});

					if ( ! elementSetting ) {
						return;
					}

					parsedField.id = getElementFieldId( element, elementSetting.get( 'id' ) );
					parsedField.name = torro.getFieldName( elementSetting, 'value' );
					parsedField.currentValue = elementSetting.get( 'value' );

					parsedField._element_setting = _.clone( elementSetting.attributes );

					parsedField.inputAttrs['data-element-setting-id'] = elementSetting.get( 'id' );
				}

				// Manage special fields per type.
				switch ( parsedField.slug ) {
					case 'autocomplete':
						if ( ! _.isUndefined( field.autocomplete ) ) {
							parsedField.autocomplete = deepClone( field.autocomplete );
						}
						break;
					case 'color':
						parsedField.inputAttrs.maxlength = 7;
						break;
					case 'datetime':
						if ( ! _.isUndefined( field.store ) ) {
							parsedField.store = field.store;
							parsedField.inputAttrs['data-store'] = field.store;
						}
						if ( ! _.isUndefined( field.min ) ) {
							parsedField.inputAttrs.min = field.min;
						}
						if ( ! _.isUndefined( field.max ) ) {
							parsedField.inputAttrs.max = field.max;
						}
						break;
					case 'map':
					case 'media':
						if ( ! _.isUndefined( field.store ) ) {
							parsedField.store = field.store;
							parsedField.inputAttrs['data-store'] = field.store;
						}
						break;
					case 'number':
					case 'range':
						if ( ! _.isUndefined( field.min ) ) {
							parsedField.inputAttrs.min = field.min;
						}
						if ( ! _.isUndefined( field.max ) ) {
							parsedField.inputAttrs.max = field.max;
						}
						if ( ! _.isUndefined( field.step ) ) {
							parsedField.inputAttrs.step = field.step;
						}
						if ( ! _.isUndefined( field.unit ) ) {
							parsedField.unit = field.unit;
						}
						break;
					case 'radio':
					case 'multibox':
					case 'select':
					case 'multiselect':
						if ( ! _.isUndefined( field.choices ) ) {
							parsedField.choices = deepClone( field.choices );
						} else {
							parsedField.choices = {};
						}
						break;
					case 'text':
						if ( ! _.isUndefined( field.maxlength ) ) {
							parsedField.inputAttrs.maxlength = field.maxlength;
						}
						if ( ! _.isUndefined( field.pattern ) ) {
							parsedField.inputAttrs.pattern = field.pattern;
						}
						break;
					case 'textarea':
						if ( ! _.isUndefined( field.rows ) ) {
							parsedField.inputAttrs.rows = field.rows;
						}
						break;
					case 'wysiwyg':
						if ( ! _.isUndefined( field.wpautop ) ) {
							parsedField.wpautop = field.wpautop;
							parsedField.inputAttrs['data-wpautop'] = field.wpautop;
						}
						if ( ! _.isUndefined( field.media_buttons ) ) {
							parsedField.media_buttons = field.media_buttons;
							parsedField.inputAttrs['data-media-buttons'] = field.media_buttons;
						}
						if ( ! _.isUndefined( field.button_mode ) ) {
							parsedField.button_mode = field.button_mode;
							parsedField.inputAttrs['data-button-mode'] = field.button_mode;
						}
						break;
				}

				if ( null === parsedField.currentValue ) {
					switch ( parsedField.slug ) {
						case 'media':
							if ( 'url' === parsedField.store ) {
								parsedField.currentValue = '';
							} else {
								parsedField.currentValue = 0;
							}
							break;
						case 'number':
						case 'range':
							if ( ! _.isUndefined( parsedField.inputAttrs.min ) ) {
								parsedField.currentValue = parsedField.inputAttrs.min;
							} else {
								parsedField.currentValue = 0;
							}
							break;
						case 'multibox':
						case 'multiselect':
							parsedField.currentValue = [];
							break;
						default:
							parsedField.currentValue = '';
					}
				}

				parsedField.inputAttrs.id = parsedField.id;
				parsedField.inputAttrs.name = parsedField.name;

				if ( _.isArray( field.input_classes ) ) {
					parsedField.inputAttrs['class'] += ' ' + field.input_classes.join( ' ' );
				}

				if ( parsedField.description.length ) {
					parsedField.inputAttrs['aria-describedby'] = parsedField.id + '-description';
				}

				parsedField.labelAttrs.id = parsedField.id + '-label';
				parsedField.labelAttrs['for'] = parsedField.id;

				parsedField.wrapAttrs.id = parsedField.id + '-wrap';
			}

			parsedFields.push( parsedField );
		});

		return parsedFields;
	}

	function sanitizeElementLabelForElementHeader( label ) {
		var tmp;

		// Strip HTML tags.
		if ( label.length && -1 !== label.search( '<' ) ) {
			tmp = document.createElement( 'div' );
			tmp.innerHTML = label;
			label  = tmp.textContent.trim();
		}

		// Limit maximum length.
		if ( label.length > 50 ) {
			label = label.substring( 0, 47 ) + '...';
		}

		return label;
	}

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

		this.element = element;
		this.options = options || {};

		this.wrapTemplate = torro.template( 'element' );
		this.sectionTabTemplate = torro.template( 'element-section-tab' );
		this.sectionPanelTemplate = torro.template( 'element-section-panel' );
		this.fieldTemplate = torro.template( 'element-field' );

		this.$wrap = $( '<div />' );
		this.$wrap.attr( 'id', 'torro-element-' + id );
		this.$wrap.addClass( 'torro-element' );
	}

	_.extend( ElementView.prototype, {
		render: function() {
			var templateData            = this.element.attributes;
			templateData.elementHeader  = templateData.label ? sanitizeElementLabelForElementHeader( templateData.label ) : '';
			templateData.type           = this.element.element_type.attributes;
			templateData.active         = this.element.collection.props.get( 'active' ).includes( this.element.get( 'id' ) );
			templateData.active_section = this.element.getActiveSection();

			this.$wrap.html( this.wrapTemplate( templateData ) );

			this.initializeSections();
			this.initializeFields();

			this.attach();
		},

		destroy: function() {
			this.detach();

			this.deinitializeFields();
			this.deinitializeSections();

			this.$wrap.remove();
		},

		initializeSections: function() {
			var $sectionTabsWrap   = this.$wrap.find( '.torro-element-content-tabs' );
			var $sectionPanelsWrap = this.$wrap.find( '.torro-element-content-panels' );

			var sections = this.element.element_type.getSections();
			var element = this.element;

			_.each( sections, _.bind( function( section ) {
				var templateData = _.clone( section );

				templateData.elementId = element.get( 'id' );
				templateData.active = element.getActiveSection() === templateData.slug;

				$sectionTabsWrap.append( this.sectionTabTemplate( templateData ) );
				$sectionPanelsWrap.append( this.sectionPanelTemplate( templateData ) );
			}, this ) );
		},

		deinitializeSections: function() {
			var $sectionTabsWrap   = this.$wrap.find( '.torro-element-content-tabs' );
			var $sectionPanelsWrap = this.$wrap.find( '.torro-element-content-panels' );

			$sectionTabsWrap.empty();
			$sectionPanelsWrap.empty();
		},

		initializeFields: function() {
			this.fieldManager = new fieldsAPI.FieldManager( parseFields( this.element.element_type.getFields(), this.element ), {
				instanceId: 'torro_element_' + this.element.get( 'id' )
			});
			this.fieldViews = [];

			_.each( this.fieldManager.models, _.bind( function( field ) {
				var viewClassName      = field.get( 'backboneView' );
				var FieldView          = fieldsAPI.FieldView;
				var $sectionFieldsWrap = this.$wrap.find( '#element-panel-' + this.element.get( 'id' ) + '-' + field.get( 'section' ) + ' > .torro-element-fields' );
				var view;

				if ( ! $sectionFieldsWrap.length ) {
					return;
				}

				$sectionFieldsWrap.append( this.fieldTemplate( field.attributes ) );

				if ( viewClassName && 'FieldView' !== viewClassName && fieldsAPI.FieldView[ viewClassName ] ) {
					FieldView = fieldsAPI.FieldView[ viewClassName ];
				}

				view = new FieldView({
					model: field
				});

				view.renderLabel();
				view.renderContent();

				this.fieldViews.push( view );
			}, this ) );
		},

		deinitializeFields: function() {
			_.each( this.fieldViews, function( fieldView ) {
				fieldView.remove();
			});

			this.$wrap.find( '.torro-element-fields' ).each( function() {
				$( this ).empty();
			});

			this.fieldViews = [];
			this.fieldManager = null;
		},

		attach: function() {
			var updateElementChoicesSorted = _.bind( this.updateElementChoicesSorted, this );

			this.element.on( 'remove', this.listenRemove, this );
			this.element.on( 'change:label', this.listenChangeLabel, this );
			this.element.on( 'change:type', this.listenChangeType, this );
			this.element.on( 'change:sort', this.listenChangeSort, this );
			this.element.on( 'changeElementType', this.listenChangeElementType, this );
			this.element.on( 'changeActiveSection', this.listenChangeActiveSection, this );
			this.element.collection.props.on( 'toggleActive', this.listenChangeActive, this );

			_.each( this.fieldViews, _.bind( function( fieldView ) {
				if ( fieldView.model.get( '_element_setting' ) ) {
					fieldView.model.on( 'changeValue', _.bind( this.listenChangeElementSettingFieldValue, this ) );
				} else if ( 'torrochoices' === fieldView.model.get( 'slug' ) ) {
					fieldView.model.on( 'addItem', _.bind( this.listenAddElementChoiceField, this ) );
					fieldView.model.on( 'removeItem', _.bind( this.listenRemoveElementChoiceField, this ) );
					fieldView.model.on( 'changeItemValue', _.bind( this.listenChangeElementChoiceFieldValue, this ) );
				}
			}, this ) );

			this.$wrap.on( 'click', '.torro-element-header', _.bind( this.toggleActive, this ) );
			this.$wrap.on( 'click', '.torro-element-expand-button', _.bind( this.toggleActive, this ) );
			this.$wrap.on( 'click', '.delete-element-button', _.bind( this.deleteElement, this ) );
			this.$wrap.on( 'click', '.torro-element-content-tab', _.bind( this.changeActiveSection, this ) );
			this.$wrap.on( 'keyup change', 'input[type="text"]#' + getElementFieldId( this.element, 'label' ), _.bind( this.updateLabel, this ) );
			this.$wrap.find( '.plugin-lib-repeatable-torrochoices-wrap' ).each( function() {
				$( this ).sortable({
					containment: 'parent',
					handle: '.torro-element-choice-move',
					items: '.plugin-lib-repeatable-item',
					placeholder: 'torro-element-choice-placeholder',
					tolerance: 'pointer',
					update: updateElementChoicesSorted
				});
			});
		},

		detach: function() {
			this.$wrap.find( '.plugin-lib-repeatable-torrochoices-wrap' ).each( function() {
				$( this ).sortable( 'destroy' );
			});
			this.$wrap.off( 'keyup change', 'input[type="text"]#' + getElementFieldId( this.element, 'label' ), _.bind( this.updateLabel, this ) );
			this.$wrap.off( 'click', '.torro-element-content-tab', _.bind( this.changeActiveSection, this ) );
			this.$wrap.off( 'click', '.delete-element-button', _.bind( this.deleteElement, this ) );
			this.$wrap.off( 'click', '.torro-element-expand-button', _.bind( this.toggleActive, this ) );
			this.$wrap.off( 'click', '.torro-element-header', _.bind( this.toggleActive, this ) );

			_.each( this.fieldViews, _.bind( function( fieldView ) {
				if ( fieldView.model.get( '_element_setting' ) ) {
					fieldView.model.off( 'changeValue', _.bind( this.listenChangeElementSettingFieldValue, this ) );
				} else if ( 'torrochoices' === fieldView.model.get( 'slug' ) ) {
					fieldView.model.off( 'addItem', _.bind( this.listenAddElementChoiceField, this ) );
					fieldView.model.off( 'removeItem', _.bind( this.listenRemoveElementChoiceField, this ) );
					fieldView.model.off( 'changeItemValue', _.bind( this.listenChangeElementChoiceFieldValue, this ) );
				}
			}, this ) );

			this.element.collection.props.off( 'toggleActive', this.listenChangeActive, this );
			this.element.off( 'changeActiveSection', this.listenChangeActiveSection, this );
			this.element.off( 'changeElementType', this.listenChangeElementType, this );
			this.element.off( 'change:sort', this.listenChangeSort, this );
			this.element.off( 'change:type', this.listenChangeType, this );
			this.element.off( 'change:label', this.listenChangeLabel, this );
			this.element.off( 'remove', this.listenRemove, this );
		},

		listenRemove: function() {
			var id = this.element.get( 'id' );

			if ( ! torro.isTempId( id ) ) {
				$( '#torro-deleted-wrap' ).append( '<input type="hidden" name="' + torro.getDeletedFieldName( this.element ) + '" value="' + id + '" />' );
			}

			this.destroy();

			torro.Builder.getInstance().trigger( 'removeElement', [ this.element, this ] );
		},

		listenChangeLabel: function( element, label ) {
			var name          = torro.escapeSelector( torro.getFieldName( this.element, 'label' ) );
			var elementHeader = label;

			this.$wrap.find( 'input[name="' + name + '"]' ).val( label );

			if ( elementHeader ) {
				elementHeader = sanitizeElementLabelForElementHeader( elementHeader );

				if ( elementHeader.length ) {
					this.$wrap.find( '.torro-element-header-title' ).text( elementHeader );
					return;
				}
			}

			this.$wrap.find( '.torro-element-header-title' ).text( this.element.element_type.getTitle() );
		},

		listenChangeType: function( element, type ) {
			var name = torro.escapeSelector( torro.getFieldName( this.element, 'type' ) );

			this.$wrap.find( 'input[name="' + name + '"]' ).val( type );
		},

		listenChangeSort: function( element, sort ) {
			var name = torro.escapeSelector( torro.getFieldName( this.element, 'sort' ) );

			this.$wrap.find( 'input[name="' + name + '"]' ).val( sort );
		},

		listenChangeElementType: function() {
			this.deinitializeFields();
			this.deinitializeSections();

			this.initializeSections();
			this.initializeFields();
		},

		listenChangeActiveSection: function( element, activeSection ) {
			var $button = this.$wrap.find( '.torro-element-content-tab[data-slug="' + activeSection + '"]' );

			this.$wrap.find( '.torro-element-content-tab' ).attr( 'aria-selected', 'false' );
			this.$wrap.find( '.torro-element-content-panel' ).attr( 'aria-hidden', 'true' );

			if ( $button.length ) {
				$button.attr( 'aria-selected', 'true' );
				this.$wrap.find( '#' + $button.attr( 'aria-controls' ) ).attr( 'aria-hidden', 'false' );
			}
		},

		listenChangeActive: function( props, active ) {
			if ( active.includes( this.element.get( 'id' ) ) ) {
				this.$wrap.find( '.torro-element-expand-button' ).attr( 'aria-expanded', 'true' ).find( '.screen-reader-text' ).text( torro.Builder.i18n.hideContent );
				this.$wrap.find( '.torro-element-content' ).addClass( 'is-expanded' );
			} else {
				this.$wrap.find( '.torro-element-expand-button' ).attr( 'aria-expanded', 'false' ).find( '.screen-reader-text' ).text( torro.Builder.i18n.showContent );
				this.$wrap.find( '.torro-element-content' ).removeClass( 'is-expanded' );
			}

			this.$wrap.find( '.plugin-lib-repeatable-torrochoices-wrap' ).each( function() {
				var $repeatableWrap = $( this );

				if ( $repeatableWrap.sortable( 'instance' ) ) {
					$repeatableWrap.sortable( 'refresh' );
				}
			});
		},

		listenChangeElementSettingFieldValue: function( model, value ) {
			var elementSettingId = model.get( '_element_setting' ).id;
			var elementSetting = this.element.element_settings.get( elementSettingId );

			if ( ! elementSetting ) {
				return;
			}

			elementSetting.set( 'value', value );
		},

		listenAddElementChoiceField: function( model, addedChoiceItem ) {
			var elementChoiceId = addedChoiceItem.name.replace( 'torro_element_choices[', '' ).replace( '][value]', '' );
			var $elementChoicesRepeatableWrap = $( '#torro_element_' + this.element.get( 'id' ) + '_choices_' + addedChoiceItem.field + '-repeatable-wrap' );

			this.element.element_choices.create({
				id: elementChoiceId,
				field: addedChoiceItem.field
			});

			if ( $elementChoicesRepeatableWrap.sortable( 'instance' ) ) {
				$elementChoicesRepeatableWrap.sortable( 'refresh' );
			}
		},

		listenRemoveElementChoiceField: function( model, removedChoiceItem ) {
			var elementChoiceId = removedChoiceItem.name.replace( 'torro_element_choices[', '' ).replace( '][value]', '' );
			var elementChoice = this.element.element_choices.get( elementChoiceId );

			this.element.element_choices.remove( elementChoiceId );

			if ( ! torro.isTempId( elementChoiceId ) ) {
				$( '#torro-deleted-wrap' ).append( '<input type="hidden" name="' + torro.getDeletedFieldName( elementChoice ) + '" value="' + elementChoiceId + '" />' );
			}
		},

		listenChangeElementChoiceFieldValue: function( model, changedChoiceItem, value ) {
			var elementChoiceId = changedChoiceItem.name.replace( 'torro_element_choices[', '' ).replace( '][value]', '' );
			var elementChoice = this.element.element_choices.get( elementChoiceId );

			elementChoice.set( 'value', value );
		},

		toggleActive: function( e ) {
			e.stopPropagation();

			this.element.collection.toggleActive( this.element.get( 'id' ) );
		},

		deleteElement: function() {
			torro.askConfirmation( torro.Builder.i18n.confirmDeleteElement, _.bind( function() {
				this.element.collection.remove( this.element );
			}, this ) );
		},

		changeActiveSection: function( e ) {
			var $button = $( e.target || e.delegateTarget );

			this.element.setActiveSection( $button.data( 'slug' ) );
		},

		updateLabel: function( e ) {
			var $input = $( e.target || e.delegateTarget );

			this.element.set( 'label', $input.val() );
		},

		updateElementChoicesSorted: function( e, ui ) {
			var element = this.element;

			ui.item.parent().find( '.plugin-lib-repeatable-item' ).each( function( index ) {
				var elementChoiceId = $( this ).find( 'input[type="text"]' ).attr( 'name' ).replace( 'torro_element_choices[', '' ).replace( '][value]', '' );
				var elementChoice   = element.element_choices.get( elementChoiceId );

				elementChoice.set( 'sort', index );

				// This is far from optimal, but we don't have element choice listeners at this point.
				$( this ).find( 'input[name="' + torro.escapeSelector( 'torro_element_choices[' + elementChoiceId + '][sort]' ) + '"]' ).val( index );
			});

			element.element_choices.sort();
		}
	});

	torro.Builder.ElementView = ElementView;

})( window.torro, window.jQuery, window._, window.pluginLibFieldsAPI, window.pluginLibFieldsAPIData.field_managers.torro_dummy_1 );

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

( function( $ ) {
	'use strict';

	$( '.torro-metabox-tab' ).on( 'click', function( e ) {
		var $this = $( this );
		var $all  = $this.parent().children( '.torro-metabox-tab' );

		e.preventDefault();

		if ( 'true' === $this.attr( 'aria-selected' ) ) {
			return;
		}

		$all.each( function() {
			$( this ).attr( 'aria-selected', 'false' );
			$( $( this ).attr( 'href' ) ).attr( 'aria-hidden', 'true' );
		});

		$this.attr( 'aria-selected', 'true' );
		$( $this.attr( 'href' ) ).attr( 'aria-hidden', 'false' ).find( '.plugin-lib-map-control' ).each( function() {
			$( this ).wpMapPicker( 'refresh' );
		});
	});

})( window.jQuery );

( function( torroBuilder ) {
	'use strict';

	torroBuilder.getInstance();

})( window.torro.Builder );
