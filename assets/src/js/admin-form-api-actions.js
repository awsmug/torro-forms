( function( torro, fieldsAPI, $, apiActionsData ) {
	'use strict';

	var apiActions               = apiActionsData.actions;
	var i18n                     = apiActionsData.i18n;
	var apiActionConnectionCache = {};
	var fieldManagerInstanceId   = $( '#torro_module_actions-field-manager-instance' );

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

			integrations.on( 'changeItemValue:connection', function( model, integration, values ) {
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
					var routeValue    = $routeSelect.val();

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

					if ( routeValue && ! routeChoices[ routeValue ] ) {
						$routeSelect.val( '' ).trigger( 'change' );
					}
				});
			});

			integrations.on( 'changeItemValue:route', function( model, integration, values ) {
				getActionConnection( apiAction, values.connection, function( connectionData ) {
					getConnectionRoute( connectionData, values.route, function( routeData ) {

						// TODO: Update field mappings.
						console.log( routeData );
					});
				});
			});
		});
	}

	apiActions.forEach( initializeAction );

}( window.torro, window.pluginLibFieldsAPI, window.jQuery, window.torroAPIActionsData || {} ) );
