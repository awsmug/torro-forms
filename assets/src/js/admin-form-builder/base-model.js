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
