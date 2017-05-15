/*!
 * Torro Forms Version 1.0.0-beta.8 (http://torro-forms.com)
 * Licensed under GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
window.torro = window.torro || {};

( function( torro, $, _, wp ) {
	var apiPromise;

	torro.api = {
		collections: {},

		models: {},

		versionString: 'torro/v1/',

		init: function() {
			var deferred;

			if ( ! apiPromise ) {
				deferred = $.Deferred();
				apiPromise = deferred.promise();

				wp.api.init({ versionString: torro.api.versionString })
					.done( function() {
						var origUrl = this.collections.ElementsTypes.prototype.url;

						torro.api.collections = _.extend( torro.api.collections, {
							Forms: this.collections.Forms,
							FormCategories: this.collections.Form_categories,
							Containers: this.collections.Containers,
							Elements: this.collections.Elements,
							ElementTypes: this.collections.ElementsTypes.extend({
								url: function() {
									/* Fix bug in element types URL. */
									return origUrl.call( this ).replace( 'elements//types', 'elements/types' );
								}
							}),
							ElementChoices: this.collections.Element_choices,
							ElementSettings: this.collections.Element_settings,
							Submissions: this.collections.Submissions,
							SubmissionValues: this.collections.Submission_values,
							Participants: this.collections.Participants
						});

						torro.api.models = _.extend( torro.api.models, {
							Form: this.models.Forms,
							FormCategory: this.models.Form_categories,
							Container: this.models.Containers,
							Element: this.models.Elements,
							ElementType: this.models.ElementsTypes,
							ElementChoice: this.models.Element_choices,
							ElementSetting: this.models.Element_settings,
							Submission: this.models.Submissions,
							SubmissionValue: this.models.Submission_values,
							Participant: this.models.Participants
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
}( window.torro, window.jQuery, window._, window.wp ) );
