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

	fieldsAPI.FieldView.FieldmappingsFieldView = fieldsAPI.FieldView.extend({
		getEvents: function() {

			// Disable default events for now.
			return {};
		},

		applyParams: function() {
			this.renderContent();
		}
	});

	if ( ! apiActions.length ) {
		return;
	}

	if ( ! fieldManagerInstanceId.length ) {
		return;
	}

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
