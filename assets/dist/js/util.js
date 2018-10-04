/*!
 * Torro Forms Version 1.0.3 (https://torro-forms.com)
 * Licensed under GNU General Public License v2 (or later) (http://www.gnu.org/licenses/gpl-2.0.html)
 */
window.torro = window.torro || {};

( function( torro, $, _, wp, wpApiSettings ) {
	var apiPromise;

	torro.api = {
		collections: {},

		models: {},

		root: wpApiSettings.root || window.location.origin + '/wp-json/',

		versionString: 'torro/v1/',

		init: function() {
			var deferred;

			if ( ! apiPromise ) {
				deferred = $.Deferred();
				apiPromise = deferred.promise();

				wp.api.init({ versionString: torro.api.versionString })
					.done( function() {
						torro.api.collections = _.extend( torro.api.collections, {
							Forms: wp.api.collections.Forms,
							FormCategories: wp.api.collections.Form_categories,
							Containers: wp.api.collections.Containers,
							Elements: wp.api.collections.Elements,
							ElementTypes: wp.api.collections.ElementsTypes.extend({
								url: function() {
									/* Fix bug in element types URL. */
									return wp.api.collections.ElementsTypes.prototype.url.call( this ).replace( 'elements//types', 'elements/types' );
								}
							}),
							ElementChoices: wp.api.collections.Element_choices,
							ElementSettings: wp.api.collections.Element_settings,
							Submissions: wp.api.collections.Submissions,
							SubmissionValues: wp.api.collections.Submission_values,
							Participants: wp.api.collections.Participants,
							ApiActions: wp.api.collections.Api_actions,
							ApiActionConnections: wp.api.collections.Api_actionsConnections.extend({
								initialize: function( models, options ) {
									wp.api.collections.Api_actionsConnections.prototype.initialize.apply( this, arguments );
									if ( ! _.isUndefined( options ) && options.action ) {
										this.parent = options.action;
									}
								}
							})
						});

						torro.api.models = _.extend( torro.api.models, {
							Form: wp.api.models.Forms,
							FormCategory: wp.api.models.Form_categories,
							Container: wp.api.models.Containers,
							Element: wp.api.models.Elements,
							ElementType: wp.api.models.ElementsTypes,
							ElementChoice: wp.api.models.Element_choices,
							ElementSetting: wp.api.models.Element_settings,
							Submission: wp.api.models.Submissions,
							SubmissionValue: wp.api.models.Submission_values,
							Participant: wp.api.models.Participants,
							ApiAction: wp.api.models.Api_actions.extend({
								url: function() {
									var url = wp.api.models.Api_actions.prototype.url.call( this );
									if ( ! _.isUndefined( this.get( 'action' ) ) ) {
										url +=  '/' + this.get( 'action' );
									}

									return url;
								}
							}),
							ApiActionConnection: wp.api.models.Api_actionsConnections.extend({
								url: function() {
									var url = wp.api.models.Api_actionsConnections.prototype.url.call( this );
									if ( ! _.isUndefined( this.get( 'action' ) ) ) {
										url = url.replace( '/connections', '/' + this.get( 'action' ) + '/connections' );
									}
									if ( ! _.isUndefined( this.get( 'connection' ) ) ) {
										url +=  '/' + this.get( 'connection' );
									}

									return url;
								}
							})
						});

						deferred.resolveWith( torro.api );
					})
					.fail( function() {
						deferred.rejectWith( torro.api );
					});
			}

			return apiPromise;
		}
	};

	torro.template = function( id ) {
		return wp.template( 'torro-' + id );
	};

	torro.isTempId = function( id ) {
		return _.isString( id ) && 'temp_id_' === id.substring( 0, 8 );
	};

	torro.generateTempId = function() {
		var random = Math.floor( Math.random() * ( 10000 - 10 + 1 ) ) + 10;

		random = random * ( new Date() ).getTime();
		random = random.toString();

		return ( 'temp_id_' + random ).substring( 0, 14 );
	};

	torro.escapeSelector = function( selector ) {
		var pattern, replacement;

		if ( 'function' === typeof $.escapeSelector ) {
			return $.escapeSelector( selector );
		}

		pattern = /([\0-\x1f\x7f]|^-?\d)|^-$|[^\x80-\uFFFF\w-]/g;

		replacement = function( ch, asCodePoint ) {
			if ( asCodePoint ) {
				if ( '\0' === ch ) {
					return '\uFFFD';
				}

				return ch.slice( 0, -1 ) + '\\' + ch.charCodeAt( ch.length - 1 ).toString( 16 ) + ' ';
			}

			return '\\' + ch;
		};

		return selector.replace( pattern, replacement );
	};

}( window.torro, window.jQuery, window._, window.wp, window.wpApiSettings || {} ) );
