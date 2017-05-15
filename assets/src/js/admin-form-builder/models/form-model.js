( function( torroBuilder, _, Backbone ) {
	'use strict';

	/**
	 * A single form.
	 *
	 * This model has no persistence with the server.
	 *
	 * @class
	 * @augments Backbone.Model
	 */
	torroBuilder.FormModel = Backbone.Model.extend({

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
		 * Synchronizes the form with the server.
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
