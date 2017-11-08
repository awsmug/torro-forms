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
		 * Element type object.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {object}
		 */
		element_type: null,

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
		 * Identifier of the currently active section.
		 *
		 * @since 1.0.0
		 * @access public
		 * @property {string}
		 */
		active_section: undefined,

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

			this.listenTypeChanged( this, this.get( 'type' ) );

			this.on( 'change:type', this.listenTypeChanged, this );

			// TODO: Retrieve element type here and automatically populate element_settings where missing.
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
		},

		setActiveSection: function( section ) {
			if ( section === this.active_section ) {
				return;
			}

			this.active_section = section;

			this.trigger( 'changeActiveSection', this, this.active_section );
		},

		getActiveSection: function() {
			return this.active_section;
		},

		listenTypeChanged: function( element, type ) {
			var sections;

			element.element_type = torroBuilder.getInstance().elementTypes.get( type );
			if ( ! element.element_type ) {
				return;
			}

			this.trigger( 'changeElementType', element, element.element_type );

			sections = element.element_type.getSections();
			if ( sections.length ) {
				element.setActiveSection( sections[0].slug );
			}
		}
	});

})( window.torro.Builder, window._ );
