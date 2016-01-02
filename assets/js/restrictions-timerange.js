( function( $ ) {
	'use strict';

	/**
	 * Datepicker
	 */
	$( document ).ready( function() {
		var datepicker_settings = {
			dateFormat : translation_admin.dateformat,
			monthNames: [
				translation_admin.january,
				translation_admin.february,
				translation_admin.march,
				translation_admin.april,
				translation_admin.may,
				translation_admin.june,
				translation_admin.july,
				translation_admin.august,
				translation_admin.september,
				translation_admin.october,
				translation_admin.november,
				translation_admin.december
			],
			dayNamesMin: [
				translation_admin.min_sun,
				translation_admin.min_mon,
				translation_admin.min_tue,
				translation_admin.min_wed,
				translation_admin.min_thu,
				translation_admin.min_fri,
				translation_admin.min_sat
			],
			showOn: "both",
			buttonImage: translation_admin.calendar_icon_url,
			buttonImageOnly: true,
			buttonText: translation_admin.select_date
		};

		$( '#start_date' ).datepicker( datepicker_settings );
		$( '#end_date' ).datepicker( datepicker_settings );
	});
}( jQuery ) );
