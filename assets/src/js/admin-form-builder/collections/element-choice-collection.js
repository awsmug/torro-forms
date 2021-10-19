( function( torroBuilder ) {
	'use strict';

	/**
	 * A collection of element choices.
	 *
	 * @class
	 * @augments torro.Builder.BaseCollection
	 */
	torroBuilder.ElementChoiceCollection = torroBuilder.BaseCollection.extend({

		/**
		 * Model class for the element choice collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {function}
		 */
		model: torroBuilder.ElementChoiceModel,

		/**
		 * REST endpoint URL part for accessing element choices.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		urlEndpoint: 'element_choices',

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
		 * Returns element choice defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Element choice defaults.
		 */
		getDefaultAttributes: function() {
			return {
				element_id: this.props.get( 'element_id' ),
				sort:       this.length
			};
		}
	});

})( window.torro.Builder );
