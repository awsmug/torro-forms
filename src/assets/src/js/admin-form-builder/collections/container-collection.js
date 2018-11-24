( function( torroBuilder, _ ) {
	'use strict';

	/**
	 * A collection of containers.
	 *
	 * @class
	 * @augments torro.Builder.BaseCollection
	 */
	torroBuilder.ContainerCollection = torroBuilder.BaseCollection.extend({

		/**
		 * Model class for the container collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {function}
		 */
		model: torroBuilder.ContainerModel,

		/**
		 * REST endpoint URL part for accessing containers.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		urlEndpoint: 'containers',

		/**
		 * Default properties for the collection.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		defaultProps: {
			selected: false,
			form_id:  0
		},

		/**
		 * Returns container defaults.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Container defaults.
		 */
		getDefaultAttributes: function() {
			var labelPlaceholder = torroBuilder.i18n.defaultContainerLabel;
			var labelNumber      = this.length + 1;
			var sort             = this.length;
			var last;

			if ( this.length ) {
				last = this.at( this.length - 1 );

				if ( last ) {
					sort = last.get( 'sort' ) + 1;

					if ( last.get( 'label' ) === labelPlaceholder.replace( '%s', sort ) ) {
						labelNumber = sort + 1;
					}
				}
			}

			return {
				form_id: this.props.get( 'form_id' ),
				label:   labelPlaceholder.replace( '%s', labelNumber ),
				sort:    sort
			};
		},

		initialize: function() {
			this.on( 'add', _.bind( this.maybeUpdateSelectedOnAdd, this ) );
			this.on( 'remove', _.bind( this.maybeUpdateSelectedOnRemove, this ) );
		},

		maybeUpdateSelectedOnAdd: function( container ) {
			if ( container ) {
				this.props.set( 'selected', container.get( 'id' ) );
			}
		},

		maybeUpdateSelectedOnRemove: function( container, containers, options ) {
			var index = options.index ? options.index - 1 : options.index;

			if ( container && this.props.get( 'selected' ) === container.get( 'id' ) ) {
				if ( this.length ) {
					this.props.set( 'selected', this.at( index ).get( 'id' ) );
				} else {
					this.props.set( 'selected', false );
				}
			}
		}
	});

})( window.torro.Builder, window._ );
