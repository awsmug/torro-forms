( function( torroBuilder, _ ) {
	'use strict';

	/**
	 * A single element.
	 *
	 * @class
	 * @augments torro.Builder.BaseModel
	 */
	torroBuilder.ElementModel = torroBuilder.BaseModel.extend({

		/**
		 * Returns element defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Element defaults.
		 */
		defaults: function() {
			return _.extend( _.clone({
				id: 0,
				container_id: 0,
				label: '',
				sort: 0,
				type: 'textfield'
			}), this.collection.getDefaultAttributes() );
		},

		/**
		 * Element choice collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		element_choices: null,

		/**
		 * Element setting collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		element_settings: undefined,

		/**
		 * Instantiates a new model.
		 *
		 * Overrides constructor in order to strip out unnecessary attributes.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {object} [attributes] Model attributes.
		 * @param {object} [options]    Options for the model behavior.
		 */
		constructor: function( attributes, options ) {
			torroBuilder.BaseModel.apply( this, [ attributes, options ] );

			this.element_choices = new torroBuilder.ElementChoiceCollection([], {
				props: {
					element_id: this.get( 'id' )
				}
			});

			this.element_settings = new torroBuilder.ElementSettingCollection([], {
				props: {
					element_id: this.get( 'id' )
				}
			});
		}
	});

})( window.torro.Builder, window._ );
