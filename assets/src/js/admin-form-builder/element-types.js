( function( torroBuilder, _ ) {
	'use strict';

	function ElementTypes( elementTypes ) {
		var i;

		this.types = {};

		for ( i in elementTypes ) {
			this.types[ elementTypes[ i ].getSlug() ] = elementTypes[ i ];
		}
	}

	_.extend( ElementTypes.prototype, {

		get: function( slug ) {
			if ( _.isUndefined( this.types[ slug ] ) ) {
				return undefined;
			}

			return this.types[ slug ];
		},

		getAll: function() {
			return this.types;
		}
	});

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
