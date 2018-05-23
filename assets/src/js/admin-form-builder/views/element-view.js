( function( torro, $, _, fieldsAPI, dummyFieldManager ) {
	'use strict';

	function deepClone( input ) {
		var output = _.clone( input );

		_.each( output, function( value, key ) {
			var temp, i;

			if ( _.isArray( value ) ) {
				temp = [];

				for ( i = 0; i < value.length; i++ ) {
					if ( _.isObject( value[ i ] ) ) {
						temp.push( deepClone( value[ i ] ) );
					} else {
						temp.push( value[ i ] );
					}
				}

				output[ key ] = temp;
			} else if ( _.isObject( value ) ) {
				output[ key ] = deepClone( value );
			}
		});

		return output;
	}

	function getObjectReplaceableFields( obj ) {
		var fields = {};

		_.each( obj, function( value, key ) {
			if ( _.isObject( value ) && ! _.isArray( value ) ) {
				value = getObjectReplaceableFields( value );
				if ( ! _.isEmpty( value ) ) {
					fields[ key ] = value;
				}
			} else if ( _.isString( value ) ) {
				if ( value.match( /%([A-Za-z0-9]+)%/g ) ) {
					fields[ key ] = value;
				}
			}
		});

		return fields;
	}

	function replaceObjectFields( obj, replacements, fields ) {
		if ( _.isUndefined( fields ) ) {
			fields = getObjectReplaceableFields( obj );
		}

		function _doReplacements( match, name ) {
			if ( ! _.isUndefined( replacements[ name ] ) ) {
				return replacements[ name ];
			}

			return match;
		}

		_.each( fields, function( value, key ) {
			if ( _.isObject( value ) ) {
				if ( ! _.isObject( obj[ key ] ) ) {
					obj[ key ] = {};
				}

				replaceObjectFields( obj[ key ], replacements, value );
			} else {
				obj[ key ] = value.replace( /%([A-Za-z0-9]+)%/g, _doReplacements );
			}
		});
	}

	function generateItem( itemInitial, index ) {
		var newItem = _.deepClone( itemInitial );

		replaceObjectFields( newItem, {
			index: index,
			indexPlus1: index + 1
		});

		return newItem;
	}

	function getElementFieldId( element, field ) {
		return 'torro_element_' + element.get( 'id' ) + '_' + field;
	}

	function parseFields( fields, element ) {
		var parsedFields = [];
		var hasLabel = false;

		_.each( fields, function( field ) {
			var parsedField;
			var elementChoices;
			var elementSetting;
			var tempId;

			if ( _.isUndefined( field.type ) || _.isUndefined( dummyFieldManager.fields[ 'dummy_' + field.type ] ) ) {
				return;
			}

			parsedField = deepClone( dummyFieldManager.fields[ 'dummy_' + field.type ] );

			parsedField.section     = field.section;
			parsedField.label       = field.label;
			parsedField.description = field.description;
			parsedField['default']  = field['default'] || null;

			if ( field.is_choices ) {
				elementChoices = element.element_choices.where({
					field: _.isString( field.is_choices ) ? field.is_choices : '_main'
				});

				tempId = torro.generateTempId();

				parsedField.repeatable = true;
				parsedField.repeatableLimit = 0;

				parsedField.id = getElementFieldId( element, field.slug );
				parsedField.labelAttrs.id = parsedField.id + '-label';

				parsedField.itemInitial.currentValue = parsedField['default'];
				parsedField.itemInitial['default']   = parsedField['default'];
				parsedField.itemInitial.element_id   = element.get( 'id' );
				parsedField.itemInitial.field        = _.isString( field.is_choices ) ? field.is_choices : '_main';
				parsedField.itemInitial.id           = parsedField.id + '-%indexPlus1%';
				parsedField.itemInitial.label        = torro.Builder.i18n.elementChoiceLabel.replace( '%s', '%indexPlus1%' );
				parsedField.itemInitial.name         = 'torro_element_choices[' + tempId + '_%index%][value]';
				parsedField.itemInitial.section      = parsedField.section;
				parsedField.itemInitial.sort         = '%index%';

				parsedField.itemInitial.inputAttrs.id   = parsedField.itemInitial.id;
				parsedField.itemInitial.inputAttrs.name = parsedField.itemInitial.name;

				if ( _.isArray( field.input_classes ) ) {
					parsedField.itemInitial.inputAttrs['class'] += ' ' + field.input_classes.join( ' ' );
				}

				parsedField.itemInitial.labelAttrs.id     = parsedField.itemInitial.id + '-label';
				parsedField.itemInitial.labelAttrs['for'] = parsedField.itemInitial.id;

				parsedField.itemInitial.wrapAttrs.id = parsedField.itemInitial.id + '-wrap';

				parsedField.wrapAttrs = deepClone( parsedField.itemInitial.wrapAttrs );
				parsedField.wrapAttrs.id = parsedField.id + '-wrap';

				_.each( elementChoices, function( elementChoice, index ) {
					var newItem = generateItem( parsedField.itemInitial, index );

					newItem.name = torro.getFieldName( elementChoice, 'value' );
					newItem.inputAttrs.name = newItem.name;

					newItem.currentValue = elementChoice.get( 'value' );

					parsedField.items.push( newItem );
				});
			} else {
				if ( field.repeatable ) {

					// Repeatable fields are currently not supported.
					return;
				}

				if ( field.is_label ) {

					// Only one label field is allowed.
					if ( hasLabel ) {
						return;
					}

					hasLabel = true;

					parsedField.id = getElementFieldId( element, 'label' );
					parsedField.name = torro.getFieldName( element, 'label' );
					parsedField.currentValue = element.get( 'label' );
				} else {

					elementSetting = element.element_settings.findWhere({
						name: field.slug
					});

					if ( ! elementSetting ) {
						return;
					}

					parsedField.id = getElementFieldId( element, elementSetting.get( 'id' ) );
					parsedField.name = torro.getFieldName( elementSetting, 'value' );
					parsedField.currentValue = elementSetting.get( 'value' );

					parsedField._element_setting = _.clone( elementSetting.attributes );

					parsedField.inputAttrs['data-element-setting-id'] = elementSetting.get( 'id' );
				}

				// Manage special fields per type.
				switch ( parsedField.slug ) {
					case 'autocomplete':
						if ( ! _.isUndefined( field.autocomplete ) ) {
							parsedField.autocomplete = deepClone( field.autocomplete );
						}
						break;
					case 'color':
						parsedField.inputAttrs.maxlength = 7;
						break;
					case 'datetime':
						if ( ! _.isUndefined( field.store ) ) {
							parsedField.store = field.store;
							parsedField.inputAttrs['data-store'] = field.store;
						}
						if ( ! _.isUndefined( field.min ) ) {
							parsedField.inputAttrs.min = field.min;
						}
						if ( ! _.isUndefined( field.max ) ) {
							parsedField.inputAttrs.max = field.max;
						}
						break;
					case 'map':
					case 'media':
						if ( ! _.isUndefined( field.store ) ) {
							parsedField.store = field.store;
							parsedField.inputAttrs['data-store'] = field.store;
						}
						break;
					case 'number':
					case 'range':
						if ( ! _.isUndefined( field.min ) ) {
							parsedField.inputAttrs.min = field.min;
						}
						if ( ! _.isUndefined( field.max ) ) {
							parsedField.inputAttrs.max = field.max;
						}
						if ( ! _.isUndefined( field.step ) ) {
							parsedField.inputAttrs.step = field.step;
						}
						if ( ! _.isUndefined( field.unit ) ) {
							parsedField.unit = field.unit;
						}
						break;
					case 'radio':
					case 'multibox':
					case 'select':
					case 'multiselect':
						if ( ! _.isUndefined( field.choices ) ) {
							parsedField.choices = deepClone( field.choices );
						} else {
							parsedField.choices = {};
						}
						break;
					case 'text':
						if ( ! _.isUndefined( field.maxlength ) ) {
							parsedField.inputAttrs.maxlength = field.maxlength;
						}
						if ( ! _.isUndefined( field.pattern ) ) {
							parsedField.inputAttrs.pattern = field.pattern;
						}
						break;
					case 'textarea':
						if ( ! _.isUndefined( field.rows ) ) {
							parsedField.inputAttrs.rows = field.rows;
						}
						break;
					case 'wysiwyg':
						if ( ! _.isUndefined( field.wpautop ) ) {
							parsedField.wpautop = field.wpautop;
							parsedField.inputAttrs['data-wpautop'] = field.wpautop;
						}
						if ( ! _.isUndefined( field.media_buttons ) ) {
							parsedField.media_buttons = field.media_buttons;
							parsedField.inputAttrs['data-media-buttons'] = field.media_buttons;
						}
						if ( ! _.isUndefined( field.button_mode ) ) {
							parsedField.button_mode = field.button_mode;
							parsedField.inputAttrs['data-button-mode'] = field.button_mode;
						}
						break;
				}

				if ( null === parsedField.currentValue ) {
					switch ( parsedField.slug ) {
						case 'media':
							if ( 'url' === parsedField.store ) {
								parsedField.currentValue = '';
							} else {
								parsedField.currentValue = 0;
							}
							break;
						case 'number':
						case 'range':
							if ( ! _.isUndefined( parsedField.inputAttrs.min ) ) {
								parsedField.currentValue = parsedField.inputAttrs.min;
							} else {
								parsedField.currentValue = 0;
							}
							break;
						case 'multibox':
						case 'multiselect':
							parsedField.currentValue = [];
							break;
						default:
							parsedField.currentValue = '';
					}
				}

				parsedField.inputAttrs.id = parsedField.id;
				parsedField.inputAttrs.name = parsedField.name;

				if ( _.isArray( field.input_classes ) ) {
					parsedField.inputAttrs['class'] += ' ' + field.input_classes.join( ' ' );
				}

				if ( parsedField.description.length ) {
					parsedField.inputAttrs['aria-describedby'] = parsedField.id + '-description';
				}

				parsedField.labelAttrs.id = parsedField.id + '-label';
				parsedField.labelAttrs['for'] = parsedField.id;

				parsedField.wrapAttrs.id = parsedField.id + '-wrap';
			}

			parsedFields.push( parsedField );
		});

		return parsedFields;
	}

	function sanitizeElementLabelForElementHeader( label ) {
		var tmp;

		// Strip HTML tags.
		if ( label.length && -1 !== label.search( '<' ) ) {
			tmp = document.createElement( 'div' );
			tmp.innerHTML = label;
			label  = tmp.textContent.trim();
		}

		// Limit maximum length.
		if ( label.length > 50 ) {
			label = label.substring( 0, 47 ) + '...';
		}

		return label;
	}

	/**
	 * An element view.
	 *
	 * @class
	 *
	 * @param {torro.Builder.Element} element Element model.
	 * @param {object}                options View options.
	 */
	function ElementView( element, options ) {
		var id = element.get( 'id' );

		this.element = element;
		this.options = options || {};

		this.wrapTemplate = torro.template( 'element' );
		this.sectionTabTemplate = torro.template( 'element-section-tab' );
		this.sectionPanelTemplate = torro.template( 'element-section-panel' );
		this.fieldTemplate = torro.template( 'element-field' );

		this.$wrap = $( '<div />' );
		this.$wrap.attr( 'id', 'torro-element-' + id );
		this.$wrap.addClass( 'torro-element' );
	}

	_.extend( ElementView.prototype, {
		render: function() {
			var templateData            = this.element.attributes;
			templateData.elementHeader  = templateData.label ? sanitizeElementLabelForElementHeader( templateData.label ) : '';
			templateData.type           = this.element.element_type.attributes;
			templateData.active         = this.element.collection.props.get( 'active' ).includes( this.element.get( 'id' ) );
			templateData.active_section = this.element.getActiveSection();

			this.$wrap.html( this.wrapTemplate( templateData ) );

			this.initializeSections();
			this.initializeFields();

			this.attach();
		},

		destroy: function() {
			this.detach();

			this.deinitializeFields();
			this.deinitializeSections();

			this.$wrap.remove();
		},

		initializeSections: function() {
			var $sectionTabsWrap   = this.$wrap.find( '.torro-element-content-tabs' );
			var $sectionPanelsWrap = this.$wrap.find( '.torro-element-content-panels' );

			var sections = this.element.element_type.getSections();
			var element = this.element;

			_.each( sections, _.bind( function( section ) {
				var templateData = _.clone( section );

				templateData.elementId = element.get( 'id' );
				templateData.active = element.getActiveSection() === templateData.slug;

				$sectionTabsWrap.append( this.sectionTabTemplate( templateData ) );
				$sectionPanelsWrap.append( this.sectionPanelTemplate( templateData ) );
			}, this ) );
		},

		deinitializeSections: function() {
			var $sectionTabsWrap   = this.$wrap.find( '.torro-element-content-tabs' );
			var $sectionPanelsWrap = this.$wrap.find( '.torro-element-content-panels' );

			$sectionTabsWrap.empty();
			$sectionPanelsWrap.empty();
		},

		initializeFields: function() {
			this.fieldManager = new fieldsAPI.FieldManager( parseFields( this.element.element_type.getFields(), this.element ), {
				instanceId: 'torro_element_' + this.element.get( 'id' )
			});
			this.fieldViews = [];

			_.each( this.fieldManager.models, _.bind( function( field ) {
				var viewClassName      = field.get( 'backboneView' );
				var FieldView          = fieldsAPI.FieldView;
				var $sectionFieldsWrap = this.$wrap.find( '#element-panel-' + this.element.get( 'id' ) + '-' + field.get( 'section' ) + ' > .torro-element-fields' );
				var view;

				if ( ! $sectionFieldsWrap.length ) {
					return;
				}

				$sectionFieldsWrap.append( this.fieldTemplate( field.attributes ) );

				if ( viewClassName && 'FieldView' !== viewClassName && fieldsAPI.FieldView[ viewClassName ] ) {
					FieldView = fieldsAPI.FieldView[ viewClassName ];
				}

				view = new FieldView({
					model: field
				});

				view.renderLabel();
				view.renderContent();

				this.fieldViews.push( view );
			}, this ) );
		},

		deinitializeFields: function() {
			_.each( this.fieldViews, function( fieldView ) {
				fieldView.remove();
			});

			this.$wrap.find( '.torro-element-fields' ).each( function() {
				$( this ).empty();
			});

			this.fieldViews = [];
			this.fieldManager = null;
		},

		attach: function() {
			var updateElementChoicesSorted = _.bind( this.updateElementChoicesSorted, this );

			this.element.on( 'remove', this.listenRemove, this );
			this.element.on( 'change:label', this.listenChangeLabel, this );
			this.element.on( 'change:type', this.listenChangeType, this );
			this.element.on( 'change:sort', this.listenChangeSort, this );
			this.element.on( 'changeElementType', this.listenChangeElementType, this );
			this.element.on( 'changeActiveSection', this.listenChangeActiveSection, this );
			this.element.collection.props.on( 'toggleActive', this.listenChangeActive, this );

			_.each( this.fieldViews, _.bind( function( fieldView ) {
				if ( fieldView.model.get( '_element_setting' ) ) {
					fieldView.model.on( 'changeValue', _.bind( this.listenChangeElementSettingFieldValue, this ) );
				} else if ( 'torrochoices' === fieldView.model.get( 'slug' ) ) {
					fieldView.model.on( 'addItem', _.bind( this.listenAddElementChoiceField, this ) );
					fieldView.model.on( 'removeItem', _.bind( this.listenRemoveElementChoiceField, this ) );
					fieldView.model.on( 'changeItemValue', _.bind( this.listenChangeElementChoiceFieldValue, this ) );
				}
			}, this ) );

			this.$wrap.on( 'click', '.torro-element-header', _.bind( this.toggleActive, this ) );
			this.$wrap.on( 'click', '.torro-element-expand-button', _.bind( this.toggleActive, this ) );
			this.$wrap.on( 'click', '.delete-element-button', _.bind( this.deleteElement, this ) );
			this.$wrap.on( 'click', '.torro-element-content-tab', _.bind( this.changeActiveSection, this ) );
			this.$wrap.on( 'keyup change', 'input[type="text"]#' + getElementFieldId( this.element, 'label' ), _.bind( this.updateLabel, this ) );
			this.$wrap.find( '.plugin-lib-repeatable-torrochoices-wrap' ).each( function() {
				$( this ).sortable({
					containment: 'parent',
					handle: '.torro-element-choice-move',
					items: '.plugin-lib-repeatable-item',
					placeholder: 'torro-element-choice-placeholder',
					tolerance: 'pointer',
					update: updateElementChoicesSorted
				});
			});
		},

		detach: function() {
			this.$wrap.find( '.plugin-lib-repeatable-torrochoices-wrap' ).each( function() {
				$( this ).sortable( 'destroy' );
			});
			this.$wrap.off( 'keyup change', 'input[type="text"]#' + getElementFieldId( this.element, 'label' ), _.bind( this.updateLabel, this ) );
			this.$wrap.off( 'click', '.torro-element-content-tab', _.bind( this.changeActiveSection, this ) );
			this.$wrap.off( 'click', '.delete-element-button', _.bind( this.deleteElement, this ) );
			this.$wrap.off( 'click', '.torro-element-expand-button', _.bind( this.toggleActive, this ) );
			this.$wrap.off( 'click', '.torro-element-header', _.bind( this.toggleActive, this ) );

			_.each( this.fieldViews, _.bind( function( fieldView ) {
				if ( fieldView.model.get( '_element_setting' ) ) {
					fieldView.model.off( 'changeValue', _.bind( this.listenChangeElementSettingFieldValue, this ) );
				} else if ( 'torrochoices' === fieldView.model.get( 'slug' ) ) {
					fieldView.model.off( 'addItem', _.bind( this.listenAddElementChoiceField, this ) );
					fieldView.model.off( 'removeItem', _.bind( this.listenRemoveElementChoiceField, this ) );
					fieldView.model.off( 'changeItemValue', _.bind( this.listenChangeElementChoiceFieldValue, this ) );
				}
			}, this ) );

			this.element.collection.props.off( 'toggleActive', this.listenChangeActive, this );
			this.element.off( 'changeActiveSection', this.listenChangeActiveSection, this );
			this.element.off( 'changeElementType', this.listenChangeElementType, this );
			this.element.off( 'change:sort', this.listenChangeSort, this );
			this.element.off( 'change:type', this.listenChangeType, this );
			this.element.off( 'change:label', this.listenChangeLabel, this );
			this.element.off( 'remove', this.listenRemove, this );
		},

		listenRemove: function() {
			var id = this.element.get( 'id' );

			if ( ! torro.isTempId( id ) ) {
				$( '#torro-deleted-wrap' ).append( '<input type="hidden" name="' + torro.getDeletedFieldName( this.element ) + '" value="' + id + '" />' );
			}

			this.destroy();

			torro.Builder.getInstance().trigger( 'removeElement', [ this.element, this ] );
		},

		listenChangeLabel: function( element, label ) {
			var name          = torro.escapeSelector( torro.getFieldName( this.element, 'label' ) );
			var elementHeader = label;

			this.$wrap.find( 'input[name="' + name + '"]' ).val( label );

			if ( elementHeader ) {
				elementHeader = sanitizeElementLabelForElementHeader( elementHeader );

				if ( elementHeader.length ) {
					this.$wrap.find( '.torro-element-header-title' ).text( elementHeader );
					return;
				}
			}

			this.$wrap.find( '.torro-element-header-title' ).text( this.element.element_type.getTitle() );
		},

		listenChangeType: function( element, type ) {
			var name = torro.escapeSelector( torro.getFieldName( this.element, 'type' ) );

			this.$wrap.find( 'input[name="' + name + '"]' ).val( type );
		},

		listenChangeSort: function( element, sort ) {
			var name = torro.escapeSelector( torro.getFieldName( this.element, 'sort' ) );

			this.$wrap.find( 'input[name="' + name + '"]' ).val( sort );
		},

		listenChangeElementType: function() {
			this.deinitializeFields();
			this.deinitializeSections();

			this.initializeSections();
			this.initializeFields();
		},

		listenChangeActiveSection: function( element, activeSection ) {
			var $button = this.$wrap.find( '.torro-element-content-tab[data-slug="' + activeSection + '"]' );

			this.$wrap.find( '.torro-element-content-tab' ).attr( 'aria-selected', 'false' );
			this.$wrap.find( '.torro-element-content-panel' ).attr( 'aria-hidden', 'true' );

			if ( $button.length ) {
				$button.attr( 'aria-selected', 'true' );
				this.$wrap.find( '#' + $button.attr( 'aria-controls' ) ).attr( 'aria-hidden', 'false' );
			}
		},

		listenChangeActive: function( props, active ) {
			if ( active.includes( this.element.get( 'id' ) ) ) {
				this.$wrap.find( '.torro-element-expand-button' ).attr( 'aria-expanded', 'true' ).find( '.screen-reader-text' ).text( torro.Builder.i18n.hideContent );
				this.$wrap.find( '.torro-element-content' ).addClass( 'is-expanded' );
			} else {
				this.$wrap.find( '.torro-element-expand-button' ).attr( 'aria-expanded', 'false' ).find( '.screen-reader-text' ).text( torro.Builder.i18n.showContent );
				this.$wrap.find( '.torro-element-content' ).removeClass( 'is-expanded' );
			}

			this.$wrap.find( '.plugin-lib-repeatable-torrochoices-wrap' ).each( function() {
				var $repeatableWrap = $( this );

				if ( $repeatableWrap.sortable( 'instance' ) ) {
					$repeatableWrap.sortable( 'refresh' );
				}
			});
		},

		listenChangeElementSettingFieldValue: function( model, value ) {
			var elementSettingId = model.get( '_element_setting' ).id;
			var elementSetting = this.element.element_settings.get( elementSettingId );

			if ( ! elementSetting ) {
				return;
			}

			elementSetting.set( 'value', value );
		},

		listenAddElementChoiceField: function( model, addedChoiceItem ) {
			var elementChoiceId = addedChoiceItem.name.replace( 'torro_element_choices[', '' ).replace( '][value]', '' );
			var $elementChoicesRepeatableWrap = $( '#torro_element_' + this.element.get( 'id' ) + '_choices_' + addedChoiceItem.field + '-repeatable-wrap' );

			this.element.element_choices.create({
				id: elementChoiceId,
				field: addedChoiceItem.field
			});

			if ( $elementChoicesRepeatableWrap.sortable( 'instance' ) ) {
				$elementChoicesRepeatableWrap.sortable( 'refresh' );
			}
		},

		listenRemoveElementChoiceField: function( model, removedChoiceItem ) {
			var elementChoiceId = removedChoiceItem.name.replace( 'torro_element_choices[', '' ).replace( '][value]', '' );
			var elementChoice = this.element.element_choices.get( elementChoiceId );

			this.element.element_choices.remove( elementChoiceId );

			if ( ! torro.isTempId( elementChoiceId ) ) {
				$( '#torro-deleted-wrap' ).append( '<input type="hidden" name="' + torro.getDeletedFieldName( elementChoice ) + '" value="' + elementChoiceId + '" />' );
			}
		},

		listenChangeElementChoiceFieldValue: function( model, changedChoiceItem, value ) {
			var elementChoiceId = changedChoiceItem.name.replace( 'torro_element_choices[', '' ).replace( '][value]', '' );
			var elementChoice = this.element.element_choices.get( elementChoiceId );

			elementChoice.set( 'value', value );
		},

		toggleActive: function( e ) {
			e.stopPropagation();

			this.element.collection.toggleActive( this.element.get( 'id' ) );
		},

		deleteElement: function() {
			torro.askConfirmation( torro.Builder.i18n.confirmDeleteElement, _.bind( function() {
				this.element.collection.remove( this.element );
			}, this ) );
		},

		changeActiveSection: function( e ) {
			var $button = $( e.target || e.delegateTarget );

			this.element.setActiveSection( $button.data( 'slug' ) );
		},

		updateLabel: function( e ) {
			var $input = $( e.target || e.delegateTarget );

			this.element.set( 'label', $input.val() );
		},

		updateElementChoicesSorted: function( e, ui ) {
			var element = this.element;

			ui.item.parent().find( '.plugin-lib-repeatable-item' ).each( function( index ) {
				var elementChoiceId = $( this ).find( 'input[type="text"]' ).attr( 'name' ).replace( 'torro_element_choices[', '' ).replace( '][value]', '' );
				var elementChoice   = element.element_choices.get( elementChoiceId );

				elementChoice.set( 'sort', index );

				// This is far from optimal, but we don't have element choice listeners at this point.
				$( this ).find( 'input[name="' + torro.escapeSelector( 'torro_element_choices[' + elementChoiceId + '][sort]' ) + '"]' ).val( index );
			});

			element.element_choices.sort();
		}
	});

	torro.Builder.ElementView = ElementView;

})( window.torro, window.jQuery, window._, window.pluginLibFieldsAPI, window.pluginLibFieldsAPIData.field_managers.torro_dummy_1 );
