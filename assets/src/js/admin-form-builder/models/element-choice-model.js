( function( torroBuilder, _ ) {
	'use strict';

	/**
	 * A single element choice.
	 *
	 * @class
	 * @augments torro.Builder.BaseModel
	 */
	torroBuilder.ElementChoiceModel = torroBuilder.BaseModel.extend({

		/**
		 * Returns element choice defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Element choice defaults.
		 */
		defaults: function() {
			return _.extend( _.clone({
				id: 0,
				element_id: 0,
				field: '',
				value: '',
				sort: 0
			}), this.collection.getDefaultAttributes() );
		}
	});

})( window.torro.Builder, window._ );
