( function( torroBuilder, _ ) {
	'use strict';

	/**
	 * A single element setting.
	 *
	 * @class
	 * @augments torro.Builder.BaseModel
	 */
	torroBuilder.ElementSettingModel = torroBuilder.BaseModel.extend({

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
				name: '',
				value: ''
			}), this.collection.getDefaultAttributes() );
		}
	});

})( window.torro.Builder, window._ );
