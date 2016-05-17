/*!
 * Torro Forms Version 1.0.0alpha1 (http://torro-forms.com)
 * Licensed under GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
( function( exports, $ ) {
	'use strict';

	$.torro_text_height = function( text, font, width ) {
		if ( ! $.torro_text_height.fakeEl ) {
			$.torro_text_height.fakeEl = $( '<div>' ).hide().appendTo( document.body );
		}

		$.torro_text_height.fakeEl.width( width );

		$.torro_text_height.fakeEl.text( text || this.val() || this.text() ).css( 'font', font || this.css( 'font' ) );
		return $.torro_text_height.fakeEl.height();
	};

	function Result_Charts_C3() {
		this.selectors = {
			tab_content: '#c3',
			tab_dynamic_content: '#c3 .torro-chart'
		};
	}

	Result_Charts_C3.prototype = {
		init: function() {
			this.init_results_deletion();
		},

		init_results_deletion: function() {
			var self = this;

			$( document ).on( 'torro.delete_results', function( e, data ) {
				$( self.selectors.tab_dynamic_content ).remove();
				$( self.selectors.tab_content ).prepend( data.html );
			});
		}
	};

	if ( 'function' === typeof exports.add_extension ) {
		exports.add_extension( 'result_charts_c3', new Result_Charts_C3() );
	} else {
		exports.result_charts_c3 = new Result_Charts_C3();
		$( document ).ready( function() {
			exports.result_charts_c3.init();
		});
	}
}( window.form_builder || window, jQuery ) );
