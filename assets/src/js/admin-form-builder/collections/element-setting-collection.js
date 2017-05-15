( function( torroBuilder, _, Backbone ) {
	'use strict';

	/**
	 * A collection of element settings.
	 *
	 * This collection has no persistence with the server.
	 *
	 * @class
	 * @augments Backbone.Collection
	 */
	torroBuilder.ElementSettingCollection = Backbone.Collection.extend({

		/**
		 * Model class for the element setting collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {function}
		 */
		model: torroBuilder.ElementSettingModel,

		/**
		 * Synchronizes the element setting collection with the server.
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

})( window.torro.Builder, window._, window.Backbone );
