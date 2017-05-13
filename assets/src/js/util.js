window.torro = window.torro || {};

( function( exports, $, _, wp ) {
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
						torro.api.collections = _.extend( torro.api.collections, {
							Forms: this.collections.Forms,
							FormCategories: this.collections.Form_categories,
							Containers: this.collections.Containers,
							Elements: this.collections.Elements,
							ElementTypes: this.collections.ElementsTypes,
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

						deferred.rejectWith( torro.api );
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
}( window.torro, window.jQuery, window._, window.wp ) );
