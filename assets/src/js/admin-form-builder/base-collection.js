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
