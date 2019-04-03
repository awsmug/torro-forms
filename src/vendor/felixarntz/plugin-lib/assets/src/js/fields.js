( function( exports, $, _, Backbone, wp, fieldsAPIData ) {
	'use strict';

	_.mixin({
		alias: function( alias, func ) {
			func( alias );
		},
		attrs: function( attrs ) {
			var attributeString = '';

			_.each( attrs, function( value, attr ) {
				if ( _.isBoolean( value ) && 0 !== attr.indexOf( 'data-' ) ) {
					if ( value ) {
						attributeString += ' ' + attr;
					}
				} else {
					if ( _.isArray( value ) || _.isObject( value ) ) {
						value = window.JSON.stringify( value );
					}

					if ( _.isString( value ) && -1 !== value.search( '"' ) ) {
						attributeString += " " + attr + "='" + value + "'";
					} else {
						attributeString += ' ' + attr + '="' + value + '"';
					}
				}
			});

			return attributeString;
		},
		deepClone: function( obj ) {
			var clone = _.clone( obj );

			_.each( clone, function( value, key ) {
				if ( _.isObject( value ) ) {
					clone[ key ] = _.deepClone( value );
				}
			});

			return clone;
		}
	});

	var cbHelpers = {
		'get_data_by_condition_bool_helper': function( prop, values, args, reverse ) {
			var operator = ( args.operator && args.operator.toUpperCase() === 'OR' ) ? 'OR' : 'AND';

			var resultFalse, resultTrue, value, identifier, i;
			if ( reverse ) {
				resultFalse = args.result_true || true;
				resultTrue  = args.result_false || false;
			} else {
				resultFalse = args.result_false || false;
				resultTrue  = args.result_true || true;
			}

			if ( 'OR' === operator ) {
				for ( i in Object.keys( values ) ) {
					identifier = Object.keys( values )[ i ];
					value      = values[ identifier ];

					if ( value ) {
						return resultTrue;
					}
				}

				return resultFalse;
			}

			for ( i in Object.keys( values ) ) {
				identifier = Object.keys( values )[ i ];
				value      = values[ identifier ];

				if ( ! value ) {
					return resultFalse;
				}
			}

			return resultTrue;
		},

		'get_data_by_condition_numeric_comparison_helper': function( prop, values, args, reverse ) {
			var operator = ( args.operator && args.operator.toUpperCase() === 'OR' ) ? 'OR' : 'AND';

			var resultFalse, resultTrue, breakpoint, sanitize, inclusive, value, identifier, i;
			if ( reverse ) {
				resultFalse = args.result_true || true;
				resultTrue  = args.result_false || false;
			} else {
				resultFalse = args.result_false || false;
				resultTrue  = args.result_true || true;
			}

			breakpoint = 0.0;
			sanitize = parseFloat;
			if ( ! _.isUndefined( args.breakpoint ) ) {
				if ( parseInt( args.breakpoint, 10 ) === args.breakpoint ) {
					sanitize = _.bind( parseInt, undefined, undefined, 10 );
				} else if ( 'string' === typeof args.breakpoint && args.breakpoint.match( /^((\d{4}-\d{2}-\d{2})|(\d{2}:\d{2}:\d{2})|(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}))$/ ) ) {
					sanitize = function( dt ) {
						var datetime = new Date( dt.replace( ' ', 'T' ) );

						return datetime.getTime() / 1000;
					};
				}

				breakpoint = sanitize( args.breakpoint );
			}

			inclusive = !! args.inclusive;
			if ( reverse ) {
				inclusive = ! inclusive;
			}

			if ( 'OR' === operator ) {
				for ( i in Object.keys( values ) ) {
					identifier = Object.keys( values )[ i ];
					value      = sanitize( values[ identifier ] );

					if ( value > breakpoint || value === breakpoint && inclusive ) {
						return resultTrue;
					}
				}

				return resultFalse;
			}

			for ( i in Object.keys( values ) ) {
				identifier = Object.keys( values )[ i ];
				value      = sanitize( values[ identifier ] );

				if ( value < breakpoint || value === breakpoint && ! inclusive ) {
					return resultFalse;
				}
			}

			return resultTrue;
		},

		'merge_into_result': function( result, value, operator ) {
			if ( _.isArray( result ) && _.isArray( value ) ) {
				if ( 'OR' === operator ) {
					result = _.union( result, value );
				} else {
					result = _.intersection( result, value );
				}

				return result;
			}

			if ( _.isObject( result ) && _.isObject( value ) ) {
				if ( 'OR' === operator ) {
					result = _.extend( result, value );
				} else {
					result = _.extend( _.pick( result, _.keys( value ) ), _.pick( value, _.keys( result ) ) );
				}

				return result;
			}

			if ( _.isBoolean( result ) && _.isBoolean( value ) ) {
				if ( 'OR' === operator ) {
					result = result || value;
				} else {
					result = result && value;
				}

				return result;
			}

			return value;
		}
	};

	var defaultDependencyCallbacks = {
		'get_data_by_condition_true': function( prop, values, args, cb ) {
			var result = cbHelpers.get_data_by_condition_bool_helper( prop, values, args, false );

			cb( result );
		},

		'get_data_by_condition_false': function( prop, values, args, cb ) {
			var result = cbHelpers.get_data_by_condition_bool_helper( prop, values, args, true );

			cb( result );
		},

		'get_data_by_condition_greater_than': function( prop, values, args, cb ) {
			var result = cbHelpers.get_data_by_condition_numeric_comparison_helper( prop, values, args, false );

			cb( result );
		},

		'get_data_by_condition_lower_than': function( prop, values, args, cb ) {
			var result = cbHelpers.get_data_by_condition_numeric_comparison_helper( prop, values, args, true );

			cb( result );
		},

		'get_data_by_map': function( prop, values, args, cb ) {
			var defaultResult = args['default'] || null;

			if ( _.isUndefined( args.map ) || _.isEmpty( args.map ) ) {
				cb( defaultResult );
				return;
			}

			var map      = args.map;
			var merge    = !! args.merge;
			var operator = ( args.operator && args.operator.toUpperCase() === 'OR' ) ? 'OR' : 'AND';
			var identifier, value;

			var result = null;

			var usedValues = [];
			for ( var i in Object.keys( values ) ) {
				identifier = Object.keys( values )[ i ];
				value      = values[ identifier ];

				if ( _.isBoolean( value ) ) {
					value = value ? 1 : 0;
				}

				if ( _.isUndefined( map[ value ] ) ) {
					continue;
				}

				if ( merge && ! _.contains( usedValues, value ) ) {
					usedValues.push( value );
					result = cbHelpers.merge_into_result( result, map[ value ], operator );
				} else {
					usedValues.push( value );
					if ( _.isObject( map[ value ] ) ) {
						result = _.clone( map[ value ] );
					} else {
						result = map[ value ];
					}
				}
			}

			if ( null === result ) {
				cb( defaultResult );
			} else {
				cb( result );
			}
		},

		'get_data_by_named_map': function( prop, values, args, cb, instanceId ) {
			var defaultResult = args['default'] || null;

			if ( _.isUndefined( args.named_map ) || _.isEmpty( args.named_map ) ) {
				cb( defaultResult );
				return;
			}

			var namedMap = args.named_map;
			var merge    = !! args.merge;
			var operator = ( args.operator && args.operator.toUpperCase() === 'OR' ) ? 'OR' : 'AND';
			var identifier, rawIdentifier, value, map;

			var result = null;

			var usedValues = {};
			for ( var i in Object.keys( values ) ) {
				identifier = Object.keys( values )[ i ];
				value      = values[ identifier ];

				if ( _.isUndefined( namedMap[ identifier ] ) ) {
					rawIdentifier = identifier.replace( '-', '_' );
					if ( _.isUndefined( namedMap[ rawIdentifier ] ) ) {
						rawIdentifier = rawIdentifier.replace( instanceId + '_', '' );
						if ( _.isUndefined( namedMap[ rawIdentifier ] ) ) {
							continue;
						}
						map = namedMap[ rawIdentifier ];
					} else {
						map = namedMap[ rawIdentifier ];
					}
				} else {
					map = namedMap[ identifier ];
				}

				if ( _.isBoolean( value ) ) {
					value = value ? 1 : 0;
				}

				usedValues[ identifier ] = [];
				if ( _.isUndefined( map[ value ] ) ) {
					continue;
				}

				if ( merge && ! _.contains( usedValues[ identifier ], value ) ) {
					usedValues[ identifier ].push( value );
					result = cbHelpers.merge_into_result( result, map[ value ], operator );
				} else {
					usedValues[ identifier ].push( value );
					if ( _.isObject( map[ value ] ) ) {
						result = _.clone( map[ value ] );
					} else {
						result = map[ value ];
					}
				}
			}

			if ( null === result ) {
				cb( defaultResult );
			} else {
				cb( result );
			}
		}
	};

	var fieldsAPI = {};

	var drPriv = {
		callbacks: {},
		queues: {},
		queueCount: 0,
		queueTotal: 0
	};

	fieldsAPI.DependencyResolver = {
		startQueue: function( instanceId ) {
			drPriv.queueCount++;
			drPriv.queueTotal++;

			var queueIdentifier = 'queue' + drPriv.queueTotal;
			var queue = new fieldsAPI.DependencyResolverQueue( queueIdentifier, instanceId );

			drPriv.queues[ queueIdentifier ] = queue;

			return queue;
		},

		finishQueue: function( queueIdentifier ) {
			if ( _.isUndefined( drPriv.queues[ queueIdentifier ] ) ) {
				return;
			}

			delete drPriv.queues[ queueIdentifier ];

			drPriv.queueCount--;
		},

		addCallback: function( callbackName, callback ) {
			if ( ! _.isFunction( callback ) ) {
				return;
			}

			drPriv.callbacks[ callbackName ] = callback;
		},

		getCallback: function( callbackName ) {
			return drPriv.callbacks[ callbackName ];
		},

		loadCallbacks: function() {
			var names = Object.keys( defaultDependencyCallbacks );

			for ( var i in names ) {
				fieldsAPI.DependencyResolver.addCallback( names[ i ], defaultDependencyCallbacks[ names[ i ] ] );
			}

			$( document ).trigger( 'pluginLibFieldsAPIDependencyCallbacks', fieldsAPI.DependencyResolver );
		}
	};

	fieldsAPI.DependencyResolverQueue = function( queueIdentifier, instanceId ) {
		this.queueIdentifier = queueIdentifier;
		this.instanceId      = instanceId;
		this.queuedItems     = [];
		this.resolvedProps   = {};
		this.busyCount       = 0;
		this.finalizeCallback;
	};

	_.extend( fieldsAPI.DependencyResolverQueue.prototype, {
		add: function( targetId, prop, callback, values, args ) {
			callback = fieldsAPI.DependencyResolver.getCallback( callback );
			if ( ! callback ) {
				return;
			}

			this.queuedItems.push({
				targetId: targetId,
				prop: prop,
				callback: callback,
				values: values,
				args: args
			});
		},

		resolve: function( finalizeCallback ) {
			var queuedItem;

			this.busyCount = this.queuedItems.length;
			this.finalizeCallback = finalizeCallback;

			for ( var i in this.queuedItems ) {
				queuedItem = this.queuedItems[ i ];

				queuedItem.callback( queuedItem.prop, queuedItem.values, queuedItem.args, _.bind( this.resolved, this, queuedItem.targetId, queuedItem.prop ), this.instanceId );
			}
		},

		resolved: function( targetId, prop, propValue ) {
			if ( null !== propValue ) {
				if ( _.isUndefined( this.resolvedProps[ targetId ] ) ) {
					this.resolvedProps[ targetId ] = {};
				}

				this.resolvedProps[ targetId ][ prop ] = propValue;
			}

			this.busyCount--;

			if ( 0 === this.busyCount ) {
				this.finalResolved();
			}
		},

		finalResolved: function() {
			fieldsAPI.DependencyResolver.finishQueue( this.queueIdentifier );

			this.finalizeCallback( this.resolvedProps );
		}
	});

	/**
	 * pluginLibFieldsAPI.Field
	 *
	 * A field.
	 *
	 * A field has no persistence with the server.
	 *
	 * @class
	 * @augments Backbone.Model
	 */
	fieldsAPI.Field = Backbone.Model.extend({
		initialize: function() {
			this.on( 'change:display', this.onDisplayChange, this );
		},

		onDisplayChange: function( field, display ) {
			var wrapAttrs  = field.get( 'wrapAttrs' );
			var inputAttrs = field.get( 'inputAttrs' );

			if ( wrapAttrs ) {
				wrapAttrs['aria-hidden'] = display ? 'false' : 'true';
			}

			if ( inputAttrs ) {
				inputAttrs.tabindex = display ? '0' : '-1';

				if ( inputAttrs['data-required'] ) {
					inputAttrs.required = display;
				}
			}
		},

		sync: function() {
			return false;
		}
	});

	/**
	 * pluginLibFieldsAPI.FieldManager
	 *
	 * A collection of fields.
	 *
	 * This collection has no persistence with the server.
	 *
	 * @class
	 * @augments Backbone.Collection
	 *
	 * @param {array}  [models]             Models to initialize with the collection.
	 * @param {object} [options]            Options hash for the collection.
	 * @param {string} [options.instanceId] Instance ID for the collection.
	 *
	 */
	fieldsAPI.FieldManager = Backbone.Collection.extend({
		/**
		 * @type {pluginLibFieldsAPI.Field}
		 */
		model: fieldsAPI.Field,

		/**
		 * @param {Array} [models=[]] Array of models used to populate the collection.
		 * @param {Object} [options={}]
		 */
		initialize: function( models, options ) {
			options = options || {};

			if ( options.instanceId ) {
				this.instanceId = options.instanceId;
			}

			this.dependencyTriggers = {};

			this.on( 'update', this.updateDependencyTriggers, this );
			this.on( 'change:currentValue', this.triggerDependantsUpdate, this );
		},

		sync: function() {
			return false;
		},

		setupDependencyTriggers: function() {
			var field;

			for ( var i in this.models ) {
				field = this.models[ i ];

				this.addFieldDependencies( field.get( 'id' ), field.get( 'dependencies' ) );
			}
		},

		updateDependencyTriggers: function( collection, options ) {
			var field;

			for ( var i in options.added ) {
				field = options.added[ i ];

				this.addFieldDependencies( field.get( 'id' ), field.get( 'dependencies' ) );
			}

			for ( var j in options.removed ) {
				field = options.removed[ j ];

				this.removeFieldDependencies( field.get( 'id' ) );
			}
		},

		triggerDependantsUpdate: function( field, currentValue ) {
			var fieldId = field.get( 'id' );

			if ( ! _.isArray( this.dependencyTriggers[ fieldId ] ) ) {
				return;
			}

			var dependencyQueue = new fieldsAPI.DependencyResolver.startQueue( this.instanceId );
			var dependency;
			var currentValues;

			for ( var i in this.dependencyTriggers[ fieldId ] ) {
				dependency = this.dependencyTriggers[ fieldId ][ i ];
				currentValues = {};

				for ( var j in dependency.fields ) {
					if ( dependency.fields[ j ] === fieldId ) {
						currentValues[ fieldId ] = currentValue;
					} else {
						currentValues[ dependency.fields[ j ] ] = this.get( dependency.fields[ j ] ).get( 'currentValue' );
					}
				}

				dependencyQueue.add( dependency.targetId, dependency.prop, dependency.callback, currentValues, dependency.args );
			}

			dependencyQueue.resolve( _.bind( this.updateDependants, this ) );
		},

		updateDependants: function( dependencyProps ) {
			_.each( dependencyProps, _.bind( function( props, targetId ) {
				this.get( targetId ).set( props );
			}, this ) );
		},

		addFieldDependencies: function( id, dependencies ) {
			if ( ! _.isArray( dependencies ) ) {
				return;
			}

			_.each( dependencies, _.bind( function( dependency ) {
				var fieldId;

				for ( var i in dependency.fields ) {
					fieldId = dependency.fields[ i ];

					if ( _.isUndefined( this.dependencyTriggers[ fieldId ] ) ) {
						this.dependencyTriggers[ fieldId ] = [];
					}

					this.dependencyTriggers[ fieldId ].push({
						targetId: id,
						prop: dependency.prop,
						callback: dependency.callback,
						fields: dependency.fields,
						args: dependency.args
					});
				}
			}, this ) );
		},

		removeFieldDependencies: function( id ) {
			_.each( this.dependencyTriggers, _.bind( function( dependencies, fieldId ) {
				var newDependencies = [];

				for ( var i in dependencies ) {
					if ( dependencies[ i ].targetId === id ) {
						continue;
					}

					newDependencies.push( dependencies[ i ] );
				}

				if ( newDependencies.length ) {
					this.dependencyTriggers[ fieldId ] = newDependencies;
				} else {
					delete this.dependencyTriggers[ fieldId ];
				}
			}, this ) );
		}
	});

	function _getObjectReplaceableFields( obj ) {
		var fields = {};

		_.each( obj, function( value, key ) {
			if ( _.isObject( value ) && ! _.isArray( value ) ) {
				value = _getObjectReplaceableFields( value );
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

	function _replaceObjectFields( obj, replacements, fields ) {
		if ( _.isUndefined( fields ) ) {
			fields = _getObjectReplaceableFields( obj );
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

				_replaceObjectFields( obj[ key ], replacements, value );
			} else {
				obj[ key ] = value.replace( /%([A-Za-z0-9]+)%/g, _doReplacements );
			}
		});
	}

	function _generateItem( itemInitial, index ) {
		var newItem = _.deepClone( itemInitial );

		_replaceObjectFields( newItem, {
			index: index,
			indexPlus1: index + 1
		});

		return newItem;
	}

	function _adjustRepeatableIndexes( itemInitial, items, startIndex ) {
		if ( ! startIndex ) {
			startIndex = 0;
		}

		var fields = _getObjectReplaceableFields( itemInitial );

		for ( var i = startIndex; i < items.length; i++ ) {
			_replaceObjectFields( items[ i ], {
				index: i,
				indexPlus1: i + 1
			}, fields );
		}

		return items;
	}

	/**
	 * pluginLibFieldsAPI.FieldView
	 *
	 * A field view.
	 *
	 * @class
	 * @augments Backbone.View
	 *
	 * @param {object} [options]       Options hash for the view.
	 * @param {object} [options.model] Field model.
	 *
	 */
	fieldsAPI.FieldView = Backbone.View.extend({
		/**
		 * @param {Object} [options={}]
		 */
		constructor: function( options ) {
			options = options || {};

			var model;

			if ( options.model ) {
				model = options.model;

				if ( ! options.el ) {
					options.el = '#' + model.get( 'id' ) + '-wrap';
				}

				if ( ! options.labelTemplate ) {
					options.labelTemplate = 'plugin-lib-field-' + model.get( 'slug' ) + '-label';
				}

				if ( ! options.contentTemplate ) {
					options.contentTemplate = 'plugin-lib-field-' + model.get( 'slug' ) + '-content';
				}

				if ( ! options.repeatableItemTemplate ) {
					options.repeatableItemTemplate = 'plugin-lib-field-' + model.get( 'slug' ) + '-repeatable-item';
				}

				var dependencies = model.get( 'dependencies' );
				if ( _.isArray( dependencies ) ) {
					var methodName;
					for ( var i in dependencies ) {
						methodName = 'apply' + ( '_' + dependencies[ i ].prop ).replace( /_([a-zA-Z0-9])/g, function( matches, part ) {
							return part.toUpperCase();
						});

						if ( _.isFunction( this[ methodName ] ) ) {
							model.on( 'change:' + dependencies[ i ].prop, this[ methodName ], this );
						}
					}
				}

				this.events = this.getEvents( model );
			}

			if ( options.labelTemplate ) {
				this.labelTemplate = wp.template( options.labelTemplate );
			}

			if ( options.contentTemplate ) {
				this.contentTemplate = wp.template( options.contentTemplate );
			}

			if ( options.repeatableItemTemplate ) {
				this.repeatableItemTemplate = wp.template( options.repeatableItemTemplate );
			}

			if ( this.preRender ) {
				this.on( 'preRender', this.preRender, this );
			}

			if ( this.postRender ) {
				this.on( 'postRender', this.postRender, this );
			}

			Backbone.View.apply( this, arguments );
		},

		initialize: function() {
			var view         = this;
			var $contentWrap = this.$( '.content-wrap' );
			var content      = $contentWrap.html();

			if ( _.isString( content ) && ! content.trim().length || ! content ) {
				return;
			}

			if ( this.model.get( 'repeatable' ) && $contentWrap.find( '.plugin-lib-repeatable-wrap' ).length ) {
				$contentWrap.find( '.plugin-lib-repeatable-item' ).each( function() {
					view.trigger( 'postRender', $( this ) );
				});
			} else {
				this.trigger( 'postRender', $contentWrap );
			}
		},

		renderLabel: function() {
			var $labelWrap;

			if ( this.labelTemplate ) {
				$labelWrap = this.$( '#' + this.model.get( 'id' ) + '-label-wrap' );

				$labelWrap.replaceWith( this.labelTemplate( this.model.toJSON() ) );
			}
		},

		renderContent: function() {
			var view = this;
			var $contentWrap;

			if ( this.contentTemplate ) {
				$contentWrap = this.$( '#' + this.model.get( 'id' ) + '-content-wrap' );

				if ( this.model.get( 'repeatable' ) && $contentWrap.find( '.plugin-lib-repeatable-wrap' ).length ) {
					$contentWrap.find( '.plugin-lib-repeatable-item' ).each( function() {
						view.trigger( 'preRender', $( this ) );
					});
				} else {
					this.trigger( 'preRender', $contentWrap );
				}
				this.undelegateEvents();

				$contentWrap.replaceWith( this.contentTemplate( this.model.toJSON() ) );

				$contentWrap = this.$( '#' + this.model.get( 'id' ) + '-content-wrap' );

				this.delegateEvents();
				if ( this.model.get( 'repeatable' ) && $contentWrap.find( '.plugin-lib-repeatable-wrap' ).length ) {
					$contentWrap.find( '.plugin-lib-repeatable-item' ).each( function() {
						view.trigger( 'postRender', $( this ) );
					});
				} else {
					this.trigger( 'postRender', $contentWrap );
				}
			}
		},

		changeValue: function( e ) {
			var value = this.getInputValue( this.$( e.target ) );

			this.model.set( 'currentValue', value );

			this.model.trigger( 'changeValue', this.model, value );
		},

		changeItemValue: function( e ) {
			var $itemInput = this.$( e.target );
			var $item      = $itemInput.parents( '.plugin-lib-repeatable-item' );
			var itemIndex  = $item.parent().children().index( $item );

			var items = this.model.get( 'items' );
			if ( ! items[ itemIndex ] ) {
				return;
			}

			items[ itemIndex ].currentValue = this.getInputValue( $itemInput );

			this.model.set( 'items', items );

			this.model.trigger( 'changeItemValue', this.model, items[ itemIndex ], items[ itemIndex ].currentValue );
		},

		addItem: function( e ) {
			var limit = this.model.get( 'repeatableLimit' );
			var items   = this.model.get( 'items' );
			if ( limit > 0 && items.length >= limit ) {
				return;
			}

			var $button   = this.$( e.target );
			var $wrap     = this.$( $button.data( 'target' ) );
			var itemIndex = $wrap.children().length;

			$button.prop( 'disabled', true );

			var newItem = _generateItem( this.model.get( 'itemInitial' ), itemIndex );

			items.push( newItem );

			var $newItem = $( this.repeatableItemTemplate( newItem ) );

			$wrap.append( $newItem );

			this.trigger( 'postRender', $newItem );

			this.model.set( 'items', items );

			if ( limit > 0 && items.length >= limit ) {
				$button.hide();
			} else {
				$button.prop( 'disabled', false );
			}

			this.model.trigger( 'addItem', this.model, newItem );
		},

		addItemOnEnter: function( e ) {
			if ( e.which !== 13 ) {
				return;
			}

			var $item = this.$( e.target ).parents( '.plugin-lib-repeatable-item' );

			if ( $item.find( '.plugin-lib-control' ).length > 1 ) {
				return;
			}

			e.preventDefault();
			e.stopPropagation();

			this.addItem({
				target: this.$( e.target ).parents( '.plugin-lib-repeatable-wrap' ).next( '.plugin-lib-repeatable-add-button' )[0]
			});

			if ( $item.next().length ) {
				$item.next().find( '.plugin-lib-control' ).focus();
			}
		},

		removeItem: function( e ) {
			var self = this;

			var limit = this.model.get( 'repeatableLimit' );
			var items = this.model.get( 'items' );

			var $button   = this.$( e.target );
			var $item     = this.$( $button.data( 'target' ) );
			var $wrap     = $item.parent();
			var itemIndex = $wrap.children().index( $item );
			var itemToRemove;

			$button.prop( 'disabled', true );

			if ( ! items[ itemIndex ] ) {
				return;
			}

			itemToRemove = items[ itemIndex ];

			items.splice( itemIndex, 1 );

			self.trigger( 'preRender', $item );

			$item.remove();

			if ( itemIndex < items.length ) {
				items = _adjustRepeatableIndexes( this.model.get( 'itemInitial' ), items, itemIndex );
				$wrap.children().each( function( index ) {
					if ( index < itemIndex ) {
						return;
					}

					var $itemToAdjust = $( this );
					var $newItem      = $( self.repeatableItemTemplate( items[ index ] ) );

					self.trigger( 'preRender', $itemToAdjust );
					self.undelegateEvents();

					$itemToAdjust.replaceWith( $newItem );

					self.delegateEvents();
					self.trigger( 'postRender', $newItem );
				});
			}

			this.model.set( 'items', items );

			if ( limit > 0 && items.length < limit ) {
				$( 'button[data-target="#' + $wrap.attr( 'id' ) + '"]' ).prop( 'disabled', false ).show();
			}

			this.model.trigger( 'removeItem', this.model, itemToRemove );
		},

		remove: function() {
			if ( this.preRender ) {
				this.off( 'preRender', this.preRender, this );
			}

			if ( this.postRender ) {
				this.off( 'postRender', this.postRender, this );
			}

			return Backbone.View.prototype.remove.apply( this, arguments );
		},

		getEvents: function( model ) {
			if ( model.get( 'repeatable' ) && _.isArray( model.get( 'items' ) ) ) {
				return {
					'click .plugin-lib-repeatable-add-button': 'addItem',
					'keydown .plugin-lib-repeatable-item .plugin-lib-control': 'addItemOnEnter',
					'click .plugin-lib-repeatable-remove-button': 'removeItem',
					'change :input': 'changeItemValue'
				};
			}

			return {
				'change :input': 'changeValue'
			};
		},

		getInputValue: function( $input ) {
			var currentValue = null;
			var dateParts;

			if ( ( $input.is( ':checkbox' ) && '[]' === $input.attr( 'name' ).substr( -2 ) ) ) {
				currentValue = [];

				$input.parent().each( ':checkbox:checked', _.bind( function( index, element ) {
					currentValue.push( this.$( element ).val() );
				}, this ) );
			} else if ( $input.is( ':radio' ) ) {
				currentValue = $input.parent().find( ':radio:checked' ).val();
			} else if ( $input.is( ':checkbox' ) ) {
				if ( $input.prop( 'checked' ) ) {
					currentValue = true;
				} else {
					currentValue = false;
				}
			} else if ( $input.is( 'select' ) && $input.prop( 'multiple' ) ) {
				currentValue = [];

				$input.each( 'option:selected', _.bind( function( index, element ) {
					currentValue.push( this.$( element ).val() );
				}, this ) );
			} else if ( $.fn.datetimepicker && $input.data( 'xdsoft_datetimepicker' ) ) {
				currentValue = $input.datetimepicker( 'getValue' );

				if ( currentValue ) {
					dateParts    = {
						year:   ( '' + currentValue.getFullYear() ).padStart( 4, '0' ),
						month:  ( '' + ( currentValue.getMonth() + 1 ) ).padStart( 2, '0' ),
						day:    ( '' + currentValue.getDate() ).padStart( 2, '0' ),
						hour:   ( '' + currentValue.getHours() ).padStart( 2, '0' ),
						minute: ( '' + currentValue.getMinutes() ).padStart( 2, '0' ),
						second: ( '' + currentValue.getSeconds() ).padStart( 2, '0' )
					};

					switch ( $input.data( 'store' ) ) {
						case 'date':
							currentValue = dateParts.year + '-' + dateParts.month + '-' + dateParts.day;
							break;
						case 'time':
							currentValue = dateParts.hour + ':' + dateParts.minute + ':' + dateParts.second;
							break;
						default:
							currentValue = dateParts.year + '-' + dateParts.month + '-' + dateParts.day + ' ' + dateParts.hour + ':' + dateParts.minute + ':' + dateParts.second;
					}
				} else {
					currentValue = null;
				}
			} else {
				currentValue = $input.val();
			}

			return currentValue;
		},

		applyLabel: function( field, label ) {
			var labelAttrs = field.get( 'labelAttrs' );
			var $label = this.$( labelAttrs.id ? '#' + labelAttrs.id : '.plugin-lib-label:first' );

			if ( $label.length ) {
				$label.html( label );
			} else {
				this.renderLabel();
			}
		},

		applyDescription: function( field, description ) {
			var $description = this.$( '#' + field.get( 'id' ) + '-description' );

			if ( $description.length ) {
				if ( description.length ) {
					$description.html( description );
				} else {
					$description.remove();
				}
			} else {
				if ( description.length ) {
					this.renderContent();
				}
			}
		},

		applyDisplay: function( field, display ) {
			var $wrap = this.$el;

			$wrap.find( '.plugin-lib-control' ).each( function() {
				var $this    = $( this );
				var $select2 = $this.data( 'select2' ) ? $this.data( 'select2' ).$selection : undefined;

				if ( $select2 ) {
					$select2.attr( 'tabindex', display ? '0' : '-1' );
				} else {
					$this.attr( 'tabindex', display ? '0' : '-1' );
				}

				if ( $this.data( 'required' ) ) {
					$( this ).prop( 'required', display );
				}
			});

			if ( display ) {
				$wrap.addClass( 'plugin-lib-toggling' ).removeClass( 'plugin-lib-hidden' ).attr( 'aria-hidden', 'false' );
				setTimeout( function() {
					$wrap.removeClass( 'plugin-lib-toggling' );
				}, 800 );
			} else {
				$wrap.addClass( 'plugin-lib-toggling' ).addClass( 'plugin-lib-hidden' ).attr( 'aria-hidden', 'true' );
				setTimeout( function() {
					$wrap.removeClass( 'plugin-lib-toggling' );
				}, 800 );
			}
		},

		applyDefault: function( field, defaultVal ) {
			// Do nothing.
		},

		applyChoices: function( field, choices ) {
			this.renderContent();
		},

		applyOptgroups: function( field, optgroups ) {
			this.renderContent();
		},

		applyUnit: function( field, unit ) {
			var $unit = this.$( '.plugin-lib-unit' );

			if ( $unit.length ) {
				if ( unit.length ) {
					$unit.html( unit );
				} else {
					$unit.remove();
				}
			} else {
				if ( unit.length ) {
					this.$( '#' + field.get( 'id' ) ).after( '<span class="plugin-lib-unit">' + unit + '</span>' );
				}
			}
		}
	});

	fieldsAPI.FieldView.WYSIWYGFieldView = fieldsAPI.FieldView.extend({
		preRender: function( $el ) {
			var editorId = $el.find( '.plugin-lib-control' ).attr( 'id' );
			var editor;

			if ( wp.editor && wp.editor.remove ) {
				wp.editor.remove( editorId );
				return;
			}

			editor = window.tinymce.get( editorId );
			if ( editor ) {
				editor.save();
				editor.remove();

				delete window.tinyMCEPreInit.mceInit[ editorId ];
				delete window.tinyMCEPreInit.qtInit[ editorId ];
			}
		},

		postRender: function( $el ) {
			var editorId = $el.find( '.plugin-lib-control' ).attr( 'id' );
			var editorData = $el.find( '.plugin-lib-control' ).data();
			var editorSettings;

			if ( wp.editor && wp.editor.initialize ) {
				editorSettings = _.extend( {}, wp.editor.getDefaultSettings() );
				editorSettings.tinymce.wpautop = !! editorData.wpautop;

				if ( 'simple' !== editorData.buttonMode ) {
					editorSettings.tinymce.toolbar1 = 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,spellchecker,wp_adv';
					editorSettings.tinymce.toolbar2 = 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo';
					editorSettings.quicktags.buttons = 'strong,em,link,block,del,ins,img,ul,ol,li,code,close';
				}

				if ( editorData.mediaButtons ) {
					editorSettings.tinymce.plugins += ',image';
					editorSettings.tinymce.setup = function() {
						var $tools = $el.find( '.wp-editor-wrap .wp-editor-tools' );
						var $mediaButtons = $( '<div>' ).attr({
							id: 'wp-' + editorId + '-media-buttons',
							class: 'wp-media-buttons'
						}).append(
							$( '<button>' ).attr({
								type: 'button',
								class: 'button insert-media add_media',
								'data-editor': editorId
							}).html( '<span class="wp-media-buttons-icon"></span> ' + fieldsAPIData.i18nWYSIWYGAddMediaButton )
						);

						$tools.prepend( $mediaButtons );

						$el.find( '.plugin-lib-control' ).trigger( 'editorInitialized' );
					};
				}

				wp.editor.initialize( editorId, editorSettings );
				return;
			}

			if ( _.isUndefined( window.tinyMCEPreInit.mceInit[ editorId ] ) || _.isUndefined( window.tinyMCEPreInit.qtInit[ editorId ] ) ) {
				window.tinyMCEPreInit.mceInit[ editorId ] = this.model.get( 'tinyMCESettings' );
				window.tinyMCEPreInit.qtInit[ editorId ]  = this.model.get( 'quickTagsSettings' );

				window.tinymce.init( window.tinyMCEPreInit.mceInit[ editorId ] );
				window.quicktags( window.tinyMCEPreInit.qtInit[ editorId ] );
			}
		}
	});

	fieldsAPI.FieldView.SelectFieldView = fieldsAPI.FieldView.extend({
		preRender: function( $el ) {
			$el.find( 'select.plugin-lib-control' ).select2( 'destroy' );
		},

		postRender: function( $el ) {
			$el.find( 'select.plugin-lib-control' ).select2({
				width: 'element',
				closeOnSelect: true,
				minimumResultsForSearch: 8
			});
		}
	});

	function getDataField( fieldPath, data ) {
		if ( _.isEmpty( fieldPath ) ) {
			return data;
		}

		if ( ! _.isObject( data ) ) {
			return undefined;
		}

		var currentField = fieldPath.shift();

		if ( ! data[ currentField ] ) {
			return undefined;
		}

		return getDataField( fieldPath, data[ currentField ] );
	}

	function replacePlaceholdersWithData( placeholderString, data ) {
		var replaced = placeholderString.replace( /\%([A-Za-z0-9_\.]+)\%/g, function( match, placeholder ) {
			var fieldPath = placeholder.split( '.' );

			var value = getDataField( fieldPath, data );
			if ( _.isObject( value ) ) {
				return match;
			}

			value = '' + value;

			if ( _.isEmpty( value ) ) {
				return match;
			}

			return value;
		});

		if ( -1 < replaced.search( '%' ) ) {
			return '';
		}

		return replaced;
	}

	fieldsAPI.FieldView.AutocompleteFieldView = fieldsAPI.FieldView.extend({
		preRender: function( $el ) {
			$el.find( '.plugin-lib-control' ).autocomplete( 'destroy' );

			this.model.set( 'currentLabel', '' );
		},

		postRender: function( $el ) {
			var autocompleteArgs = this.model.get( 'autocomplete' );
			if ( ! autocompleteArgs ) {
				autocompleteArgs = this.model.get( 'itemInitial' ).autocomplete;
			}

			$el.find( '.plugin-lib-control' ).autocomplete({
				source: function( request, response ) {
					var restUrl = fieldsAPIData.restUrl + autocompleteArgs.restPlaceholderSearchRoute.replace( '%search%', request.term );
					$.ajax( restUrl, {
						method: 'GET',
						dataType: 'json',
						headers: {
							'X-WP-Nonce': fieldsAPIData.restNonce
						},
						success: function( data ) {
							var results = [];
							for ( var i in data ) {
								var value = replacePlaceholdersWithData( autocompleteArgs.valueGenerator, data[ i ] );
								var label = replacePlaceholdersWithData( autocompleteArgs.labelGenerator, data[ i ] );

								if ( ! _.isEmpty( value ) && ! _.isEmpty( label ) ) {
									results.push({
										value: value,
										label: label
									});
								}
							}

							response( results );
						},
						error: function( xhr ) {
							if ( _.isObject( xhr.responseJSON ) && null !== xhr.responseJSON && ! _.isEmpty( xhr.responseJSON.message ) ) {
								console.error( xhr.responseJSON.message );
							}

							response([]);
						},
						complete: function( xhr ) {
							var newNonce = xhr.getResponseHeader( 'X-WP-Nonce' );
							if ( newNonce ) {
								fieldsAPIData.restNonce = newNonce;
							}
						}
					});
				},
				select: function( e, ui ) {
					e.preventDefault();

					$el.find( '.plugin-lib-control' ).val( ui.item.label );
					$el.find( '.plugin-lib-control' ).prev().val( ui.item.value ).trigger( 'change' );
				},
				focus: function( e, ui ) {
					e.preventDefault();

					$el.find( '.plugin-lib-control' ).val( ui.item.label );
				}
			});
		}
	});

	var dtpLocaleSet = false;

	fieldsAPI.FieldView.DatetimeFieldView = fieldsAPI.FieldView.extend({
		constructor: function() {
			if ( ! dtpLocaleSet ) {
				$.datetimepicker.setLocale( fieldsAPIData.language );
				dtpLocaleSet = true;
			}

			fieldsAPI.FieldView.apply( this, arguments );
		},

		preRender: function( $el ) {
			$el.find( '.plugin-lib-control' ).datetimepicker( 'destroy' );
		},

		postRender: function( $el ) {
			var $hidden = $el.find( '.plugin-lib-control' ).prev();
			var store   = $el.find( '.plugin-lib-control' ).data( 'store' );
			var options = {
				closeOnDateSelect: true,
				formatDate: 'Y-m-d',
				formatTime: 'H:i',
				dayOfWeekStart: fieldsAPIData.startOfWeek,
				scrollMonth: false,
				scrollTime: false,
				scrollInput: false,
				validateOnBlur: false
			};

			switch ( store ) {
				case 'time':
					options.format     = fieldsAPIData.timeFormat;
					options.datepicker = false;
					break;
				case 'date':
					options.format     = fieldsAPIData.dateFormat;
					options.timepicker = false;
					break;
				default:
					options.format = fieldsAPIData.datetimeFormat;
			}

			function parseDateTimeDefault( dateInstance ) {
				var currentValue = '';
				var dateParts;

				if ( dateInstance ) {
					dateParts    = {
						year:   ( '' + dateInstance.getFullYear() ).padStart( 4, '0' ),
						month:  ( '' + ( dateInstance.getMonth() + 1 ) ).padStart( 2, '0' ),
						day:    ( '' + dateInstance.getDate() ).padStart( 2, '0' ),
						hour:   ( '' + dateInstance.getHours() ).padStart( 2, '0' ),
						minute: ( '' + dateInstance.getMinutes() ).padStart( 2, '0' ),
						second: ( '' + dateInstance.getSeconds() ).padStart( 2, '0' )
					};

					switch ( store ) {
						case 'date':
							currentValue = dateParts.year + '-' + dateParts.month + '-' + dateParts.day;
							break;
						case 'time':
							currentValue = dateParts.hour + ':' + dateParts.minute + ':' + dateParts.second;
							break;
						default:
							currentValue = dateParts.year + '-' + dateParts.month + '-' + dateParts.day + ' ' + dateParts.hour + ':' + dateParts.minute + ':' + dateParts.second;
					}
				}

				return currentValue;
			}

			options.onShow = function( ct, $input ) {
				var newOptions = {};
				var value      = $hidden.val();
				var helper;

				if ( value ) {
					newOptions.value = new Date( value );
				}

				if ( $input.attr( 'min' ) ) {
					helper = $input.attr( 'min' ).split( ' ' );
					if ( helper.length === 2 ) {
						newOptions.minDate = helper[0];
						newOptions.minTime = helper[1];
					} else if( helper.length === 1 ) {
						if ( 'time' === store ) {
							newOptions.minTime = helper[0];
						} else {
							newOptions.minDate = helper[0];
						}
					}
				}

				if ( $input.attr( 'max' ) ) {
					helper = $input.attr( 'max' ).split( ' ' );
					if ( helper.length === 2 ) {
						newOptions.maxDate = helper[0];
						newOptions.maxTime = helper[1];
					} else if( helper.length === 1 ) {
						if ( 'time' === store ) {
							newOptions.maxTime = helper[0];
						} else {
							newOptions.maxDate = helper[0];
						}
					}
				}

				if ( 'date' !== store && $input.attr( 'step' ) ) {
					newOptions.step = parseInt( $input.attr( 'step' ), 10 );
				}

				this.setOptions( newOptions );
			};

			options.onChangeDateTime = function( currentValue ) {
				currentValue = parseDateTimeDefault( currentValue );
				$hidden.val( currentValue ).trigger( 'change' );
			};

			$el.find( '.plugin-lib-control' )
				.datetimepicker( options )
				.on( 'change', function( e ) {
					var formattedValue = this.value;
					var currentValue;

					if ( ! formattedValue ) {
						currentValue = '';
					} else {
						formattedValue = formattedValue.trim();

						if ( ! formattedValue.length ) {
							currentValue = '';
						} else {
							formattedValue = new Date( formattedValue );
							if ( ! isNaN( formattedValue.valueOf() ) ) {
								currentValue = parseDateTimeDefault( formattedValue );
							}
						}
					}

					if ( 'undefined' !== typeof currentValue ) {
						$hidden.val( currentValue ).trigger( 'change' );
					}
				});
		}
	});

	fieldsAPI.FieldView.ColorFieldView = fieldsAPI.FieldView.extend({
		preRender: function( $el ) {
			$el.find( '.plugin-lib-control' ).wpColorPicker( 'destroy' );
		},

		postRender: function( $el ) {
			$el.find( '.plugin-lib-control' ).wpColorPicker({
				mode: 'hsv'
			});
		}
	});

	fieldsAPI.FieldView.MediaFieldView = fieldsAPI.FieldView.extend({
		preRender: function( $el ) {
			$el.find( '.plugin-lib-control' ).wpMediaPicker( 'destroy' );
		},

		postRender: function( $el ) {
			$el.find( '.plugin-lib-control' ).wpMediaPicker({
				filterable: false,
				label_add: fieldsAPIData.i18nMediaAddButton,
				label_replace: fieldsAPIData.i18nMediaReplaceButton,
				label_remove: fieldsAPIData.i18nMediaRemoveButton,
				label_modal: fieldsAPIData.i18nMediaModalHeading,
				label_button: fieldsAPIData.i18nMediaModalButton
			});
		}
	});

	fieldsAPI.FieldView.MapFieldView = fieldsAPI.FieldView.extend({
		preRender: function( $el ) {
			$el.find( '.plugin-lib-control' ).wpMapPicker( 'destroy' );
		},

		postRender: function( $el ) {
			$el.find( '.plugin-lib-control' ).wpMapPicker();
		}
	});

	fieldsAPI.FieldView.GroupFieldView = fieldsAPI.FieldView.extend({
		changeValue: function( e ) {
			var $fieldInput = this.$( e.target );
			var fieldId = $fieldInput.attr( 'id' );
			var newValue = this.getInputValue( $fieldInput );

			var currentValue = this.model.get( 'currentValue' );
			var fields = this.model.get( 'fields' );

			var fieldKeys = Object.keys( fields );
			var data, id;
			for ( var i in fieldKeys ) {
				id = fieldKeys[ i ];
				data = fields[ id ];

				if ( data.id === fieldId ) {
					fields[ id ].currentValue = newValue;
					currentValue[ id ] = newValue;
					break;
				}
			}

			this.model.set( 'fields', fields );
			this.model.set( 'currentValue', currentValue );
		},

		changeItemValue: function( e ) {
			var $itemFieldInput = this.$( e.target );
			var itemFieldId = $itemFieldInput.attr( 'id' );
			var newValue = this.getInputValue( $itemFieldInput );

			var $itemGroup = $itemFieldInput.parents( '.plugin-lib-group-control' );
			var $item      = $itemGroup.parents( '.plugin-lib-repeatable-item' );
			var itemIndex  = $item.parent().children().index( $item );

			var items = this.model.get( 'items' );
			if ( ! items[ itemIndex ] ) {
				return;
			}

			var fieldKeys = Object.keys( items[ itemIndex ].fields );
			var data, id;
			for ( var i in fieldKeys ) {
				id = fieldKeys[ i ];
				data = items[ itemIndex ].fields[ id ];

				if ( data.id === itemFieldId ) {
					items[ itemIndex ].fields[ id ].currentValue = newValue;
					items[ itemIndex ].currentValue[ id ] = newValue;
					break;
				}

				id = undefined;
			}

			if ( ! id ) {
				return;
			}

			this.model.set( 'items', items );

			this.model.trigger( 'changeItemValue', this.model, items[ itemIndex ], items[ itemIndex ].currentValue );
			this.model.trigger( 'changeItemValue:' + id, this.model, items[ itemIndex ], items[ itemIndex ].currentValue );
		},

		preRender: function( $el ) {
			var fields;

			if ( this.model.get( 'repeatable' ) && _.isArray( this.model.get( 'items' ) ) ) {
				var itemIndex = $el.parent().children().index( $el );
				var items = this.model.get( 'items' );
				if ( items[ itemIndex ] ) {
					fields = items[ itemIndex ].fields;
				}
			} else {
				fields = this.model.get( 'fields' );
			}

			if ( ! fields ) {
				return;
			}

			_.each( fields, function( data ) {
				if ( ! data.backboneView || 'FieldView' === data.backboneView ) {
					return;
				}

				if ( ! fieldsAPI.FieldView[ data.backboneView ] ) {
					return;
				}

				if ( ! fieldsAPI.FieldView[ data.backboneView ].prototype.preRender ) {
					return;
				}

				var $subel = $el.find( '#' + data.wrapAttrs.id );

				fieldsAPI.FieldView[ data.backboneView ].prototype.preRender.apply( this, [ $subel ] );
			});
		},

		postRender: function( $el ) {
			var fields;

			if ( this.model.get( 'repeatable' ) && _.isArray( this.model.get( 'items' ) ) ) {
				var itemIndex = $el.parent().children().index( $el );
				var items = this.model.get( 'items' );
				if ( items[ itemIndex ] ) {
					fields = items[ itemIndex ].fields;
				}
			} else {
				fields = this.model.get( 'fields' );
			}

			if ( ! fields ) {
				return;
			}

			_.each( fields, function( data ) {
				if ( ! data.backboneView || 'FieldView' === data.backboneView ) {
					return;
				}

				if ( ! fieldsAPI.FieldView[ data.backboneView ] ) {
					return;
				}

				if ( ! fieldsAPI.FieldView[ data.backboneView ].prototype.postRender ) {
					return;
				}

				var $subel = $el.find( '#' + data.wrapAttrs.id );

				fieldsAPI.FieldView[ data.backboneView ].prototype.postRender.apply( this, [ $subel ] );
			});
		}
	});

	fieldsAPI.FieldManager.instances = {};

	$( document ).ready( function() {
		fieldsAPI.DependencyResolver.loadCallbacks();

		_.each( fieldsAPIData.field_managers, function( instance, instanceId ) {
			if ( instance.skip_initialization ) {
				return;
			}

			fieldsAPI.FieldManager.instances[ instanceId ] = new fieldsAPI.FieldManager( _.values( instance.fields ), {
				instanceId: instanceId
			});
			fieldsAPI.FieldManager.instances[ instanceId ].setupDependencyTriggers();

			_.each( fieldsAPI.FieldManager.instances[ instanceId ].models, function( field ) {
				var viewClassName = field.get( 'backboneView' );
				var FieldView     = fieldsAPI.FieldView;

				if ( viewClassName && 'FieldView' !== viewClassName && fieldsAPI.FieldView[ viewClassName ] ) {
					FieldView = fieldsAPI.FieldView[ viewClassName ];
				}

				new FieldView({
					model: field
				});
			});
		});
	});

	exports.pluginLibFieldsAPI = fieldsAPI;

}( window, jQuery, _, Backbone, wp, pluginLibFieldsAPIData ) );
