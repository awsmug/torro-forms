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
