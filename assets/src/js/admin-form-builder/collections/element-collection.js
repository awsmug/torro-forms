( function( torroBuilder ) {
	'use strict';

	/**
	 * A collection of elements.
	 *
	 * @class
	 * @augments torro.Builder.BaseCollection
	 */
	torroBuilder.ElementCollection = torroBuilder.BaseCollection.extend({

		/**
		 * Model class for the element collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {function}
		 */
		model: torroBuilder.ElementModel,

		/**
		 * REST endpoint URL part for accessing elements.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		urlEndpoint: 'elements',

		/**
		 * Default properties for the collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		defaultProps: {
			active:       [],
			container_id: 0
		},

		/**
		 * Returns element defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Element defaults.
		 */
		getDefaultAttributes: function() {
			return {
				container_id: this.props.get( 'container_id' ),
				sort:         this.length
			};
		},

		toggleActive: function( id ) {
			var active = this.props.get( 'active' );
			var index = active.indexOf( id );

			if ( index > -1 ) {
				active.splice( index, 1 );
			} else {
				active.push( id );
			}

			this.props.set( 'active', active );

			this.props.trigger( 'toggleActive', this, active, {});
		}
	});

})( window.torro.Builder );
