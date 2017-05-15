( function( torroBuilder, _ ) {
	'use strict';

	function ElementType( attributes ) {
		this.attributes = attributes;
	}

	_.extend( ElementType.prototype, {

		getSlug: function() {
			return this.attributes.slug;
		},

		getTitle: function() {
			return this.attributes.title;
		},

		getDescription: function() {
			return this.attributes.description;
		},

		getIconUrl: function() {
			return this.attributes.icon_url;
		},

		isEvaluable: function() {
			return this.attributes.evaluable;
		},

		isMultiField: function() {
			return this.attributes.multifield;
		}
	});

	torroBuilder.ElementType = ElementType;

})( window.torro.Builder, window._ );
