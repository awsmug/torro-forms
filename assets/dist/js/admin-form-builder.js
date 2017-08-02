/*!
 * Torro Forms Version 1.0.0-beta.8 (http://torro-forms.com)
 * Licensed under GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
window.torro = window.torro || {};

( function( torro, $, _, Backbone, wp, i18n ) {
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

		this.el  = selector;
		this.$el = $( this.el );
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
		 * Current container collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {torro.Builder.ContainerCollection}
		 */
		containers: undefined,

		/**
		 * Current element collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {torro.Builder.ElementCollection}
		 */
		elements: undefined,

		/**
		 * Current element choice collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {torro.Builder.ElementChoiceCollection}
		 */
		elementChoices: undefined,

		/**
		 * Current element setting collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {torro.Builder.ElementSettingCollection}
		 */
		elementSettings: undefined,

		/**
		 * Form canvas view.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {torro.Builder.FormCanvasView}
		 */
		formCanvas: undefined,

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
			if ( ! _.contains( initialized, this.instanceCount ) ) {
				return;
			}

			if ( form ) {
				this.form = new torro.Builder.FormModel( form );

				if ( form._embedded.containers && form._embedded.containers[0] ) {
					this.containers = new torro.Builder.ContainerCollection( form._embedded.containers[0] );
				} else {
					this.containers = new torro.Builder.ContainerCollection();
				}

				if ( form._embedded.elements && form._embedded.elements[0] ) {
					this.elements = new torro.Builder.ElementCollection( form._embedded.elements[0] );
				} else {
					this.elements = new torro.Builder.ElementCollection();
				}

				if ( form._embedded.element_choices && form._embedded.element_choices[0] ) {
					this.elementChoices = new torro.Builder.ElementChoiceCollection( form._embedded.element_choices[0] );
				} else {
					this.elementChoices = new torro.Builder.ElementChoiceCollection();
				}

				if ( form._embedded.element_settings && form._embedded.element_settings[0] ) {
					this.elementSettings = new torro.Builder.ElementSettingCollection( form._embedded.element_settings[0] );
				} else {
					this.elementSettings = new torro.Builder.ElementSettingCollection();
				}
			} else {
				this.form = new torro.Builder.FormModel();

				this.containers = new torro.Builder.ContainerCollection([ {}, {} ], {
					props: {
						form_id:           this.form.get( 'id' ),
						label_placeholder: i18n.defaultContainerLabel
					}
				});

				this.elements = new torro.Builder.ElementCollection();

				this.elementChoices = new torro.Builder.ElementChoiceCollection();

				this.elementSettings = new torro.Builder.ElementSettingCollection();
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

			this.formCanvas = new torro.Builder.FormCanvasView({
				el: this.el,
				model: this.form
			});
			this.formCanvas.views.set( '.torro-js-container-tabs', new torro.Builder.ContainerTabsView({
				collection: this.containers
			}) );
			this.formCanvas.views.set( '.torro-js-container-panels', new torro.Builder.ContainerPanelsView({
				collection: this.containers
			}) );
			this.formCanvas.views.set( '.torro-js-container-footer-panels', new torro.Builder.ContainerFooterPanelsView({
				collection: this.containers
			}) );

			this.formCanvas.render();
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

}( window.torro, window.jQuery, window._, window.Backbone, window.wp, window.torroBuilderI18n ) );

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
		}
	});

})( window.torro.Builder, window._ );

( function( torroBuilder ) {
	'use strict';

	/**
	 * A single element choice.
	 *
	 * @class
	 * @augments torro.Builder.BaseModel
	 */
	torroBuilder.ElementChoiceModel = torroBuilder.BaseModel.extend({

		/**
		 * Element choice defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		defaults: {
			id: 0,
			element_id: 0,
			field: '',
			value: '',
			sort: 0
		}
	});

})( window.torro.Builder );

( function( torroBuilder ) {
	'use strict';

	/**
	 * A single element.
	 *
	 * @class
	 * @augments torro.Builder.BaseModel
	 */
	torroBuilder.ElementModel = torroBuilder.BaseModel.extend({

		/**
		 * Element defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		defaults: {
			id: 0,
			container_id: 0,
			label: '',
			sort: 0,
			type: 'textfield'
		}
	});

})( window.torro.Builder );

( function( torroBuilder ) {
	'use strict';

	/**
	 * A single element setting.
	 *
	 * @class
	 * @augments torro.Builder.BaseModel
	 */
	torroBuilder.ElementSettingModel = torroBuilder.BaseModel.extend({

		/**
		 * Element setting defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		defaults: {
			id: 0,
			element_id: 0,
			name: '',
			value: ''
		}
	});

})( window.torro.Builder );

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
		}
	});

})( window.torro.Builder );

( function( torroBuilder ) {
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
			selected:          false,
			form_id:           0,
			label_placeholder: 'Page %s'
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
			return {
				form_id: this.props.get( 'form_id' ),
				label:   this.props.get( 'label_placeholder' ).replace( '%s', this.length + 1 ),
				sort:    this.length
			};
		}
	});

})( window.torro.Builder );

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
		urlEndpoint: 'element_choices'
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
		urlEndpoint: 'elements'
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
		urlEndpoint: 'element_settings'
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

( function( torroBuilder, torro ) {
	'use strict';

	/**
	 * Container footer panel view.
	 *
	 * @class
	 * @augments torro.Builder.BaseModelView
	 */
	torroBuilder.ContainerFooterPanelView = torroBuilder.BaseModelView.extend({

		/**
		 * Element tag name.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		tagName: 'div',

		/**
		 * Element class name.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		className: 'torro-form-canvas-panel',

		/**
		 * Template function.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @type {function}
		 */
		template: torro.template( 'container-footer-panel' ),

		/**
		 * Element attributes.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Default attributes.
		 */
		attributes: function() {
			return {
				'id': 'container-footer-panel-' + this.model.get( 'id' ),
				'aria-labelledby': 'container-tab-' + this.model.get( 'id' ),
				'aria-hidden': this.model.get( 'id' ) === this.collection.props.get( 'selection' ) ? 'false' : 'true',
				'role': 'tabpanel'
			};
		},

		/**
		 * Initializes the view.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		initialize: function() {
			this.listenTo( this.collection.props, 'change:selection', this._toggleSelection );
		},

		/**
		 * Sets the aria-hidden attribute depending on whether this is a panel for the currently selected tab.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {Backbone.Model} props Collection properties.
		 */
		_toggleSelection: function( props ) {
			if ( this.model.get( 'id' ) === props.get( 'selection' ) ) {
				this.$el.attr( 'aria-hidden', 'false' );
			} else {
				this.$el.attr( 'aria-hidden', 'true' );
			}
		}
	});

})( window.torro.Builder, window.torro );

( function( torroBuilder, torro ) {
	'use strict';

	/**
	 * Container panel view.
	 *
	 * @class
	 * @augments torro.Builder.BaseModelView
	 */
	torroBuilder.ContainerPanelView = torroBuilder.BaseModelView.extend({

		/**
		 * Element tag name.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		tagName: 'div',

		/**
		 * Element class name.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		className: 'torro-form-canvas-panel',

		/**
		 * Template function.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @type {function}
		 */
		template: torro.template( 'container-panel' ),

		/**
		 * Element attributes.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Default attributes.
		 */
		attributes: function() {
			return {
				'id': 'container-panel-' + this.model.get( 'id' ),
				'aria-labelledby': 'container-tab-' + this.model.get( 'id' ),
				'aria-hidden': this.model.get( 'id' ) === this.collection.props.get( 'selection' ) ? 'false' : 'true',
				'role': 'tabpanel'
			};
		},

		/**
		 * Initializes the view.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		initialize: function() {
			this.listenTo( this.collection.props, 'change:selection', this._toggleSelection );
		},

		/**
		 * Sets the aria-hidden attribute depending on whether this is a panel for the currently selected tab.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {Backbone.Model} props Collection properties.
		 */
		_toggleSelection: function( props ) {
			if ( this.model.get( 'id' ) === props.get( 'selection' ) ) {
				this.$el.attr( 'aria-hidden', 'false' );
			} else {
				this.$el.attr( 'aria-hidden', 'true' );
			}
		}
	});

})( window.torro.Builder, window.torro );

( function( torroBuilder, torro ) {
	'use strict';

	/**
	 * Container tab button view.
	 *
	 * @class
	 * @augments torro.Builder.BaseModelView
	 */
	torroBuilder.ContainerTabButtonView = torroBuilder.BaseModelView.extend({

		/**
		 * Element tag name.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		tagName: 'button',

		/**
		 * Element class name.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		className: 'torro-form-canvas-tab add-button',

		/**
		 * Template function.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @type {function}
		 */
		template: torro.template( 'container-tab-button' ),

		/**
		 * Element attributes.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Default attributes.
		 */
		attributes: function() {
			return {
				'type': 'button',
				'aria-selected': false === this.collection.props.get( 'selection' ) ? 'true' : 'false'
			};
		},

		/**
		 * View events.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @type {object}
		 */
		events: {
			'click': 'addContainer'
		},

		/**
		 * Initializes the view.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		initialize: function() {
			this.listenTo( this.collection.props, 'change:selection', this._toggleSelection );
		},

		/**
		 * Adds a new container.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		addContainer: function() {
			this.collection.add({});
		},

		/**
		 * Sets the aria-selected attribute depending on whether this is the currently selected tab.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {Backbone.Model} props Collection properties.
		 */
		_toggleSelection: function( props ) {
			if ( false === props.get( 'selection' ) ) {
				this.$el.attr( 'aria-selected', 'true' );
			} else {
				this.$el.attr( 'aria-selected', 'false' );
			}
		}
	});

})( window.torro.Builder, window.torro );

( function( torroBuilder, torro ) {
	'use strict';

	/**
	 * Container tab view.
	 *
	 * @class
	 * @augments torro.Builder.BaseModelView
	 */
	torroBuilder.ContainerTabView = torroBuilder.BaseModelView.extend({

		/**
		 * Element tag name.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		tagName: 'button',

		/**
		 * Element class name.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		className: 'torro-form-canvas-tab',

		/**
		 * Template function.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @type {function}
		 */
		template: torro.template( 'container-tab' ),

		/**
		 * Element attributes.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Default attributes.
		 */
		attributes: function() {
			return {
				'type': 'button',
				'id': 'container-tab-' + this.model.get( 'id' ),
				'aria-controls': 'container-panel-' + this.model.get( 'id' ) + ' container-footer-panel-' + this.model.get( 'id' ),
				'aria-selected': this.model.get( 'id' ) === this.collection.props.get( 'selection' ) ? 'true' : 'false',
				'role': 'tab'
			};
		},

		/**
		 * View events.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @type {object}
		 */
		events: {
			'click': 'selectTab'
		},

		/**
		 * Initializes the view.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		initialize: function() {
			this.listenTo( this.collection.props, 'change:selection', this._toggleSelection );
		},

		/**
		 * Selects the element as the current tab.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		selectTab: function() {
			if ( this.model.get( 'id' ) === this.collection.props.get( 'selection' ) ) {
				return;
			}

			this.collection.props.set( 'selection', this.model.get( 'id' ) );
		},

		/**
		 * Sets the aria-selected attribute depending on whether this is the currently selected tab.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {Backbone.Model} props Collection properties.
		 */
		_toggleSelection: function( props ) {
			if ( this.model.get( 'id' ) === props.get( 'selection' ) ) {
				this.$el.attr( 'aria-selected', 'true' );
			} else {
				this.$el.attr( 'aria-selected', 'false' );
			}
		}
	});

})( window.torro.Builder, window.torro );

( function( torroBuilder, torro ) {
	'use strict';

	/**
	 * Form canvas view.
	 *
	 * @class
	 * @augments torro.Builder.BaseModelView
	 */
	torroBuilder.FormCanvasView = torroBuilder.BaseModelView.extend({

		/**
		 * Element tag name.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		tagName: 'div',

		/**
		 * Element class name.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		className: 'torro-form-canvas',

		/**
		 * Template function.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @type {function}
		 */
		template: torro.template( 'form-canvas' )
	});

})( window.torro.Builder, window.torro );

( function( torroBuilder ) {
	'use strict';

	/**
	 * Container footer panels view.
	 *
	 * @class
	 * @augments torro.Builder.BaseCollectionView
	 */
	torroBuilder.ContainerFooterPanelsView = torroBuilder.BaseCollectionView.extend({

		/**
		 * Model view class for the collection view.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {function}
		 */
		modelView: torroBuilder.ContainerFooterPanelView,

		/**
		 * Element tag name.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		tagName: 'div',

		/**
		 * Element class name.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		className: 'torro-form-container-footer-panels'
	});

})( window.torro.Builder );

( function( torroBuilder, torro ) {
	'use strict';

	/**
	 * Container panels view.
	 *
	 * @class
	 * @augments torro.Builder.BaseCollectionView
	 */
	torroBuilder.ContainerPanelsView = torroBuilder.BaseCollectionView.extend({

		/**
		 * Model view class for the collection view.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {function}
		 */
		modelView: torroBuilder.ContainerPanelView,

		/**
		 * Element tag name.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		tagName: 'div',

		/**
		 * Element class name.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		className: 'torro-form-container-panels drag-drop-area',

		/**
		 * Template function.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @type {function}
		 */
		emptyTemplate: torro.template( 'empty-container-drag-drop' )
	});

})( window.torro.Builder, window.torro );

( function( torroBuilder ) {
	'use strict';

	/**
	 * Container tabs view.
	 *
	 * @class
	 * @augments torro.Builder.BaseCollectionView
	 */
	torroBuilder.ContainerTabsView = torroBuilder.BaseCollectionView.extend({

		/**
		 * Model view class for the collection view.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {function}
		 */
		modelView: torroBuilder.ContainerTabView,

		/**
		 * Element tag name.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		tagName: 'div',

		/**
		 * Element class name.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		className: 'torro-form-container-tabs',

		/**
		 * Element attributes.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Default attributes.
		 */
		attributes: function() {
			return {
				'role': 'tablist'
			};
		}
	});

})( window.torro.Builder );

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
