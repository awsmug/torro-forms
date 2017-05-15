( function( torroBuilder, _, Backbone ) {
	'use strict';

	/**
	 * A single element choice.
	 *
	 * This model has no persistence with the server.
	 *
	 * @class
	 * @augments Backbone.Model
	 */
	torroBuilder.ElementChoiceModel = Backbone.Model.extend({

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
		},

		/**
		 * Synchronizes the element choice with the server.
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
