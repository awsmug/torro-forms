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
		urlEndpoint: 'element_settings',

		/**
		 * Default properties for the collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		defaultProps: {
			element_id: 0
		},

		/**
		 * Returns element setting defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Element setting defaults.
		 */
		getDefaultAttributes: function() {
			return {
				element_id: this.props.get( 'element_id' ),
				sort:       this.length
			};
		}
	});

})( window.torro.Builder );
