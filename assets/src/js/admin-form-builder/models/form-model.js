( function( torroBuilder ) {
	'use strict';

	/**
	 * A single form.
	 *
	 * @class
	 * @augments torro.Builder.BaseModel
	 */
	torroBuilder.FormModel = torroBuilder.BaseModel.extend({

		/**
		 * Form defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		defaults: {
			id: 0,
			title: '',
			slug: '',
			author: 0,
			status: 'draft',
			timestamp: 0,
			timestamp_modified: 0
		},

		/**
		 * Container collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		containers: undefined,

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
			var containerProps;

			torroBuilder.BaseModel.apply( this, [ attributes, options ] );

			containerProps = {
				form_id: this.get( 'id' )
			};

			this.containers = new torroBuilder.ContainerCollection([], {
				props: containerProps,
				comparator: 'sort'
			});
		}
	});

})( window.torro.Builder );
