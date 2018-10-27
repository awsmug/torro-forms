/*!
 * Torro Forms Version 1.0.3 (https://torro-forms.com)
 * Licensed under GNU General Public License v2 (or later) (http://www.gnu.org/licenses/gpl-2.0.html)
 */
( function( torro, fieldsAPI, $, apiActionsData ) {
	'use strict';

	var apiActions               = apiActionsData.actions;
	var i18n                     = apiActionsData.i18n;
	var apiActionConnectionCache = {};
	var fieldManagerInstanceId   = $( '#torro_module_actions-field-manager-instance' );
	var fieldMappingsViews       = {};

	var builder       = torro.Builder.getInstance();
	var elementModels = {};

	function toggleTemplateTagList( $button ) {
		if ( 'true' === $button.attr( 'aria-expanded' ) ) {
			$button.attr( 'aria-expanded', 'false' );
		} else {
			$button.attr( 'aria-expanded', 'true' );
			$button.next( '.template-tag-list' ).focus();

			$( 'html' ).one( 'click', function() {
				$button.attr( 'aria-expanded', 'false' ).focus();
			});
		}
	}

	fieldsAPI.FieldView.FieldmappingsFieldView = fieldsAPI.FieldView.extend({
		getEvents: function() {

			// Disable default events for now.
			return {};
		},

		applyParams: function() {
			this.renderContent();
		},

		preRender: function( $el ) {
			$el.find( '.template-tag-list-toggle' ).off( 'click' );
			$el.find( '.template-tag-button' ).off( 'click' );
		},

		postRender: function( $el ) {
			var $templateTagListToggles = $el.find( '.template-tag-list-toggle' );

			if ( ! $templateTagListToggles.length ) {
				return;
			}

			$templateTagListToggles.on( 'click', function( e ) {
				var $button = $( this );

				toggleTemplateTagList( $button );

				e.stopPropagation();
				e.preventDefault();
			});

			$el.on( 'click', '.template-tag-button', function() {
				var tag    = '{' + $( this ).data( 'tag' ) + '}';
				var $input = $( '#' + $( this ).data( 'targetId' ) );

				$input.val( $input.val() + tag );
			});
		}
	});

	if ( ! apiActions.length ) {
		return;
	}

	if ( ! fieldManagerInstanceId.length ) {
		return;
	}

	function removeTemplateTagForElement( model, $list ) {
		var templateTagSlug  = apiActionsData.templateTagSlug.replace( '%element_id%', model.get( 'id' ) );
		var templateTagGroup = apiActionsData.templateTagGroup;
		var $tag             = $list.find( '.template-tag-' + templateTagSlug );

		if ( $tag ) {
			$tag.remove();

			if ( ! $list.find( '.template-tag-list-group-' + templateTagGroup + ' > ul > li' ).length ) {
				$list.find( '.template-tag-list-group-' + templateTagGroup ).remove();
			}
		}
	}

	function addTemplateTagForElement( model, $list ) {
		var targetId = $list.attr( 'id' ).replace( '-template-tag-list', '' );

		var templateTag = {
			slug: apiActionsData.templateTagSlug.replace( '%element_id%', model.get( 'id' ) ),
			group: apiActionsData.templateTagGroup,
			label: apiActionsData.templateTagLabel.replace( '%element_label%', model.get( 'label' ) ),
			description: apiActionsData.templateTagDescription.replace( '%element_label%', model.get( 'label' ) )
		};

		var templateTagGroup = {
			slug: apiActionsData.templateTagGroup,
			label: apiActionsData.templateTagGroupLabel
		};

		var $tag = $( apiActionsData.templateTagTemplate
			.replace( /%slug%/g, templateTag.slug )
			.replace( /%group%/g, templateTag.group )
			.replace( /%label%/g, templateTag.label )
			.replace( /%description%/g, templateTag.description )
			.replace( /%target_id%/g, targetId ) );

		var $group = $list.find( '.template-tag-list-group-' + templateTag.group + ' > ul' );

		if ( ! $group.length ) {
			$group = $( apiActionsData.templateTagGroupTemplate
				.replace( /%slug%/g, templateTagGroup.slug )
				.replace( /%label%/g, templateTagGroup.label ) );

			$list.append( $group );

			$group = $group.children( 'ul' );
		}

		$group.append( $tag );

		model.on( 'change:label', function( element, label ) {
			var newLabel = apiActionsData.templateTagLabel.replace( '%element_label%', label );

			$tag.html( $tag.html().replace( templateTag.label, newLabel ) );

			templateTag.label = newLabel;
		});
	}

	builder.on( 'addElement', function( model ) {
		elementModels[ model.get( 'id' ) ] = model;

		$( '.field-mappings-template-tag-list' ).each( function() {
			addTemplateTagForElement( model, $( this ) );
		});
	});

	builder.on( 'removeElement', function( model ) {
		if ( elementModels[ model.get( 'id' ) ] ) {
			delete elementModels[ model.get( 'id' ) ];
		}

		$( '.field-mappings-template-tag-list' ).each( function() {
			removeTemplateTagForElement( model, $( this ) );
		});
	});

	fieldManagerInstanceId = fieldManagerInstanceId.val();

	function getActionConnection( apiAction, connection, callback ) {
		if ( ! connection || ! connection.length ) {
			callback( undefined );
			return;
		}

		if ( apiActionConnectionCache[ apiAction ] && apiActionConnectionCache[ apiAction ][ connection ] ) {
			callback( apiActionConnectionCache[ apiAction ][ connection ] );
			return;
		}

		torro.api.init()
			.done( function() {
				( new torro.api.models.ApiActionConnection({
					action: apiAction,
					connection: connection
				}) ).fetch({
					data: {
						context: 'view'
					},
					context: this,
					success: function( connectionInstance ) {
						if ( ! apiActionConnectionCache[ apiAction ] ) {
							apiActionConnectionCache[ apiAction ] = {};
						}

						apiActionConnectionCache[ apiAction ][ connection ] = connectionInstance.attributes;

						callback( connectionInstance.attributes );
					},
					error: function() {
						console.error( i18n.couldNotLoadData ); // eslint-disable-line no-console
					}
				});
			})
			.fail( function() {
				console.error( i18n.couldNotLoadData ); // eslint-disable-line no-console
			});
	}

	function getConnectionRoute( connection, route, callback ) {
		var i;

		if ( ! connection || ! route || ! route.length ) {
			callback( undefined );
			return;
		}

		for ( i = 0; i < connection.routes.length; i++ ) {
			if ( connection.routes[ i ].slug === route ) {
				callback( connection.routes[ i ] );
				return;
			}
		}

		callback( undefined );
	}

	function initializeAction( apiAction ) {
		var $integrationsWrap = $( '.plugin-lib-repeatable-group-' + apiAction + '__integrations-wrap' );

		if ( ! $integrationsWrap.length ) {
			return;
		}

		$( document ).ready( function() {
			var integrations = fieldsAPI.FieldManager.instances[ fieldManagerInstanceId ].get( fieldManagerInstanceId + '_' + apiAction.replace( '_', '-' ) + '--integrations' );

			function onChangeConnection( model, integration, values ) {
				var index = integration.name.match( /\[(\d+)\]$/ );
				var items = integrations.get( 'items' );

				if ( ! index || ! items ) {
					return;
				}

				index = parseInt( index[1], 10 );

				if ( ! items[ index ] ) {
					return;
				}

				getActionConnection( apiAction, values.connection, function( connectionData ) {
					var routeChoices  = {};
					var selectOptions = [];
					var $routeSelect  = $( '#' + items[ index ].fields.route.id );
					var routeValue    = $routeSelect;

					routeChoices[ '' ] = i18n.selectARoute;
					selectOptions.push({
						id: '',
						text: i18n.selectARoute
					});

					if ( connectionData ) {
						connectionData.routes.forEach( function( route ) {
							routeChoices[ route.slug ] = route.title;
							selectOptions.push({
								id: route.slug,
								text: route.title
							});
						});
					}

					items[ index ].fields.route.choices = routeChoices;

					$routeSelect.empty().select2({
						data: selectOptions
					});

					if ( values.route ) {
						if ( ! routeChoices[ values.route ] ) {
							$routeSelect.val( '' ).trigger( 'change' );
							return;
						}

						if ( values.route !== routeValue ) {
							$routeSelect.val( values.route ).trigger( 'change' );
						}
					}
				});
			}

			function onChangeRoute( model, integration, values ) {
				getActionConnection( apiAction, values.connection, function( connectionData ) {
					getConnectionRoute( connectionData, values.route, function( routeData ) {
						var params  = {};
						var display = false;
						var $el     = $( '#' + integration.fields.mappings.wrapAttrs.id );
						var customModel;
						var customView;

						if ( routeData ) {
							routeData.fields.forEach( function( paramData ) {
								if ( paramData.readonly ) {
									return;
								}

								// Arrays (i.e. repeatable parameters) are currently not supported.
								if ( 'array' === paramData.type ) {
									return;
								}

								params[ paramData.slug ] = paramData;
							});

							if ( params ) {
								display = true;
							}
						}

						integration.fields.mappings.params  = params;
						integration.fields.mappings.display = display;

						// The Fields API itself is currently not able to deal with dynamic nested fields, therefore we need some extra logic.
						if ( $el.data( 'torroView' ) && fieldMappingsViews[ $el.data( 'torroView' ) ] ) {
							customView  = fieldMappingsViews[ $el.data( 'torroView' ) ];
							customModel = customView.model;
						} else {
							customModel = new fieldsAPI.Field( integration.fields.mappings );
							customView  = new fieldsAPI.FieldView.FieldmappingsFieldView({
								model: customModel
							});

							fieldMappingsViews[ customView.cid ] = customView;
							$el.data( 'torroView', customView.cid );
						}

						customView.applyParams( customModel, params );
						customView.applyDisplay( customModel, display );

						$el.find( '.field-mappings-template-tag-list' ).each( function() {
							var keys = Object.keys( elementModels );
							var i;

							for ( i = 0; i < keys.length; i++ ) {
								addTemplateTagForElement( elementModels[ keys[ i ] ], $( this ) );
							}
						});
					});
				});
			}

			integrations.on( 'changeItemValue:connection', function( model, integration, values ) {
				onChangeConnection( model, integration, values );
			});

			integrations.on( 'changeItemValue:route', function( model, integration, values ) {
				onChangeRoute( model, integration, values );
			});

			integrations.get( 'items' ).forEach( function( integration ) {
				var $connectionSelect = $( '#' + integration.fields.connection.id );
				var connectionValue   = $connectionSelect.val();

				if ( connectionValue && connectionValue.length ) {
					$connectionSelect.trigger( 'change' );

					onChangeConnection( integrations, integration, integration.currentValue );
				}
			});
		});
	}

	apiActions.forEach( initializeAction );

}( window.torro, window.pluginLibFieldsAPI, window.jQuery, window.torroAPIActionsData || {} ) );
