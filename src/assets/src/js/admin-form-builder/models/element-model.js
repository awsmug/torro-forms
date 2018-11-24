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

			this.element_choices = new torroBuilder.ElementChoiceCollection([], {
				props: {
					element_id: this.get( 'id' )
				},
				comparator: 'sort'
			});

			this.element_settings = new torroBuilder.ElementSettingCollection([], {
				props: {
					element_id: this.get( 'id' )
				}
			});

			this.listenTypeChanged( this, this.get( 'type' ) );

			this.on( 'change:type', this.listenTypeChanged, this );
		},

		setElementSetting: function( elementSetting ) {
			var existingSetting, index;

			if ( elementSetting.attributes ) {
				elementSetting = elementSetting.attributes;
			}

			existingSetting = this.element_settings.findWhere({
				name: elementSetting.name
			});
			if ( ! existingSetting ) {
				return false;
			}

			index = this.element_settings.indexOf( existingSetting );

			this.element_settings.remove( existingSetting );
			this.element_settings.add( elementSetting, {
				at: index
			});

			return true;
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
			var sections, settingFields, settingNames, oldSettings = {};

			element.element_type = torroBuilder.getInstance().elementTypes.get( type );
			if ( ! element.element_type ) {
				return;
			}

			this.trigger( 'changeElementType', element, element.element_type );

			sections = element.element_type.getSections();
			if ( sections.length ) {
				element.setActiveSection( sections[0].slug );
			}

			settingFields = element.element_type.getFields().filter( function( field ) {
				return ! field.is_label && ! field.is_choices;
			});

			settingNames = settingFields.map( function( settingField ) {
				return settingField.slug;
			});

			element.element_settings.each( function( elementSetting ) {
				if ( settingNames.includes( elementSetting.name ) ) {
					oldSettings[ elementSetting.name ] = elementSetting.attributes;
				}
			});
			element.element_settings.reset();

			_.each( settingFields, function( settingField ) {
				if ( oldSettings[ settingField.slug ] ) {
					element.element_settings.add( oldSettings[ settingField.slug ] );
				} else {
					element.element_settings.create({
						name: settingField.slug,
						value: settingField['default'] || null
					});
				}
			});
		}
	});

})( window.torro.Builder, window._ );
