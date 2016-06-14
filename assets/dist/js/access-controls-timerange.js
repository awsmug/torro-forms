/*!
 * Torro Forms Version 1.0.0-beta.5 (http://torro-forms.com)
 * Licensed under GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
( function( exports, wp, $, translations ) {
	'use strict';

	function Restriction_Timerange( translations ) {
		this.translations = translations;

		this.selectors = {
			start_date: '#start_date',
			end_date: '#end_date'
		};
	}

	Restriction_Timerange.prototype = {
		init: function() {
			var datepicker_settings = {
				dateFormat : this.translations.dateformat,
				monthNames: [
					this.translations.january,
					this.translations.february,
					this.translations.march,
					this.translations.april,
					this.translations.may,
					this.translations.june,
					this.translations.july,
					this.translations.august,
					this.translations.september,
					this.translations.october,
					this.translations.november,
					this.translations.december
				],
				dayNamesMin: [
					this.translations.min_sun,
					this.translations.min_mon,
					this.translations.min_tue,
					this.translations.min_wed,
					this.translations.min_thu,
					this.translations.min_fri,
					this.translations.min_sat
				],
				showOn: "both",
				buttonImage: this.translations.calendar_icon_url,
				buttonImageOnly: true,
				buttonText: this.translations.select_date
			};

			$( this.selectors.start_date ).datepicker( datepicker_settings );
			$( this.selectors.end_date ).datepicker( datepicker_settings );
		}
	};

	exports.add_extension( 'access_control_timerange', new Restriction_Timerange( translations ) );
}( form_builder, wp, jQuery, translation_tr ) );
