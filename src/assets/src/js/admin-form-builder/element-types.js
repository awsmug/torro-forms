( function( torroBuilder, _ ) {
	'use strict';

	/**
	 * A list of available element types.
	 *
	 * @class
	 *
	 * @param {torro.Builder.ElementType[]} elementTypes Registered element type objects.
	 */
	function ElementTypes( elementTypes ) {
		var i;

		this.types = {};

		for ( i in elementTypes ) {
			this.types[ elementTypes[ i ].getSlug() ] = elementTypes[ i ];
		}
	}

	_.extend( ElementTypes.prototype, {

		/**
		 * Returns a specific element type.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {torro.Builder.ElementType|undefined} Element type object, or undefined if not available.
		 */
		get: function( slug ) {
			if ( _.isUndefined( this.types[ slug ] ) ) {
				return undefined;
			}

			return this.types[ slug ];
		},

		/**
		 * Returns all element types.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {torro.Builder.ElementType[]} All element type objects.
		 */
		getAll: function() {
			return this.types;
		}
	});

	/**
	 * Generates an element types list instance from a REST API response.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @returns {torro.Builder.ElementTypes} Element types object.
	 */
	ElementTypes.fromApiCollection = function( collection ) {
		var elementTypes = [];

		collection.each( function( model ) {
			var attributes = _.extend({}, model.attributes );
			if ( attributes._links ) {
				delete attributes._links;
			}
			if ( attributes._embedded ) {
				delete attributes._embedded;
			}

			elementTypes.push( new torroBuilder.ElementType( attributes ) );
		});

		return new ElementTypes( elementTypes );
	};

	torroBuilder.ElementTypes = ElementTypes;

})( window.torro.Builder, window._ );
