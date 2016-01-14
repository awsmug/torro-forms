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
		this.selectors = {};
	}

	Result_Charts_C3.prototype = {
		init: function() {}
	};

	exports.add_extension( 'result_charts_c3', new Result_Charts_C3() );
}( form_builder, jQuery ) );
