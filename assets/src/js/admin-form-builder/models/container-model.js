( function( torroBuilder, _, Backbone ) {
	'use strict';

	/**
	 * A single container.
	 *
	 * This model has no persistence with the server.
	 *
	 * @class
	 * @augments Backbone.Model
	 */
	torroBuilder.ContainerModel = Backbone.Model.extend({

		/**
		 * Container defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		defaults: {
			id: 0,
			form_id: 0,
			label: '',
			sort: 0
		},

		/**
		 * Synchronizes the container with the server.
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
