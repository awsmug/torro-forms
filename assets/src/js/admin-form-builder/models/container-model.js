( function( torroBuilder, _ ) {
	'use strict';

	/**
	 * A single container.
	 *
	 * @class
	 * @augments torro.Builder.BaseModel
	 */
	torroBuilder.ContainerModel = torroBuilder.BaseModel.extend({

		/**
		 * Returns container defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Container defaults.
		 */
		defaults: function() {
			return _.extend( _.clone({
				id: 0,
				form_id: 0,
				label: '',
				sort: 0
			}), this.collection.getDefaultAttributes() );
		},

		/**
		 * Element collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		elements: undefined,

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
			attributes = attributes || {};

			torroBuilder.BaseModel.apply( this, [ attributes, options ] );

			this.elements = new torroBuilder.ElementCollection([], {
				props: {
					container_id: this.get( 'id' )
				},
				comparator: 'sort'
			});
		}
	});

})( window.torro.Builder, window._ );
