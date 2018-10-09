/*!
 * Torro Forms Version 1.0.3 (https://torro-forms.com)
 * Licensed under GNU General Public License v2 (or later) (http://www.gnu.org/licenses/gpl-2.0.html)
 */
( function( torro, fieldsAPI, $, apiActionsData ) {
	'use strict';

	var apiAction              = apiActionsData.action;
	var i18n                   = apiActionsData.i18n;
	var fieldManagerInstanceId = $( '#torro_module_actions-field-manager-instance' );
	var $connectionsWrap       = $( '.plugin-lib-repeatable-group-' + apiAction + '__connections-wrap' );
	var connections;

	if ( ! fieldManagerInstanceId.length || ! $connectionsWrap.length ) {
		return;
	}

	function generateSlug( title ) {
		return title.replace( /\s+/g, '-' ).replace( /[^A-Za-z0-9_-]+/g, '' ).toLowerCase();
	}

	$( document ).ready( function() {
		fieldManagerInstanceId = fieldManagerInstanceId.val();
		connections            = fieldsAPI.FieldManager.instances[ fieldManagerInstanceId ].get( fieldManagerInstanceId + '_' + apiAction.replace( '_', '-' ) + '--connections' );

		connections.on( 'changeItemValue:title', function( model, connection, values ) {
			var $slugField;
			var newSlug;

			// Never change an existing slug.
			if ( values.slug && values.slug.length ) {
				return;
			}

			// Skip if title is empty.
			if ( ! values.title || ! values.title.length ) {
				return;
			}

			$slugField = $( '#' + connection.id + '-slug' );
			if ( ! $slugField.length ) {
				return;
			}

			newSlug = generateSlug( values.title );
			if ( ! newSlug.length ) {
				return;
			}

			$slugField.val( newSlug ).trigger( 'change' );
		});

		torro.api.init()
			.done( function() {
				( new torro.api.models.ApiAction({
					action: apiAction
				}) ).fetch({
					data: {
						context: 'view'
					},
					context: this,
					success: function( apiActionInstance ) {
						var baseConnectionFields = [ 'title', 'slug', 'structure' ];
						var authenticationFields = {};
						apiActionInstance.get( 'structures' ).forEach( function( structure ) {
							authenticationFields[ structure.slug ] = structure.authentication_fields;
						});

						function updateAuthenticatorFieldsDisplay( structure, connection ) {
							var fieldSlugs      = Object.keys( connection.fields );
							var $requiredMarkup = $( '#' + connection.fields.title.labelAttrs.id ).children( 'span' );

							fieldSlugs.forEach( function( fieldSlug ) {
								var fieldData     = connection.fields[ fieldSlug ];
								var shouldDisplay = false;

								// Never alter visibility of the default fields.
								if ( baseConnectionFields.includes( fieldSlug ) ) {
									return;
								}

								if ( authenticationFields[ structure ] ) {
									shouldDisplay = authenticationFields[ structure ].includes( fieldSlug );
								}

								if ( shouldDisplay === fieldData.display ) {
									return;
								}

								fieldData.display                  = shouldDisplay;
								fieldData.required                 = fieldData.display;
								fieldData.inputAttrs.tabindex      = fieldData.display ? '0' : '-1';
								fieldData.inputAttrs.required      = fieldData.required ? true : false;
								fieldData.wrapAttrs['class']       = fieldData.display ? fieldData.wrapAttrs['class'].replace( ' plugin-lib-hidden', '' ) : fieldData.wrapAttrs['class'] + ' plugin-lib-hidden';
								fieldData.wrapAttrs['aria-hidden'] = fieldData.display ? 'false' : 'true';

								$( '#' + fieldData.inputAttrs.id ).attr( 'tabindex', fieldData.inputAttrs.tabindex );
								$( '#' + fieldData.inputAttrs.id ).prop( 'required', fieldData.inputAttrs.required );
								$( '#' + fieldData.wrapAttrs.id ).attr( 'class', fieldData.wrapAttrs['class'] );
								$( '#' + fieldData.wrapAttrs.id ).attr( 'aria-hidden', fieldData.wrapAttrs['aria-hidden'] );
								if ( fieldData.required ) {
									$( '#' + fieldData.labelAttrs.id ).append( $requiredMarkup.clone() );
								} else {
									$( '#' + fieldData.labelAttrs.id ).children( 'span' ).remove();
								}
							});
						}

						connections.get( 'items' ).forEach( function( connection ) {
							updateAuthenticatorFieldsDisplay( connection.fields.structure.currentValue, connection );
						});

						connections.on( 'addItem', function( model, connection ) {
							updateAuthenticatorFieldsDisplay( connection.fields.structure.currentValue, connection );
						});

						connections.on( 'changeItemValue:structure', function( model, connection, values ) {
							if ( ! values.structure || ! values.structure.length ) {
								return;
							}

							updateAuthenticatorFieldsDisplay( values.structure, connection );
						});
					},
					error: function() {
						console.error( i18n.couldNotLoadData ); // eslint-disable-line no-console
					}
				});
			})
			.fail( function() {
				console.error( i18n.couldNotLoadData ); // eslint-disable-line no-console
			});
	});

}( window.torro, window.pluginLibFieldsAPI, window.jQuery, window.torroAPIActionsData ) );
