( function( torroBuilder, _, Backbone ) {
	'use strict';

	/**
	 * A single element setting.
	 *
	 * This model has no persistence with the server.
	 *
	 * @class
	 * @augments Backbone.Model
	 */
	torroBuilder.ElementSettingModel = Backbone.Model.extend({

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
		},

		/**
		 * Synchronizes the element setting with the server.
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
