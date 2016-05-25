/*!
 * Torro Forms Version 1.0.0-beta.3 (http://torro-forms.com)
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
			this.init_charts();
			this.init_results_deletion();
		},

		init_charts: function() {
			var chart_width = '';
			var label_height = '';

			if ( $( '#form-result-handlers-tabs' ).length ) {
				var tab_width = $( '#form-result-handlers-tabs' ).width();
				chart_width = Math.round( ( tab_width / 100 * 95 ) );
			}

			$( '.chart-c3' ).each( function() {
				var $this = $( this );
				var id = $this.attr( 'id' );

				var categories = $this.data( 'categories' ).split( '###' );

				var results = $this.data( 'results' ).split( '###' ).map( function( value ) {
					return parseInt( value, 10 );
				});
				results.unshift( 'values' );

				var value_text = $this.data( 'value-text' );

				var category_width = Math.round( ( chart_width / categories.length ) );

				var category_height = 0;
				for ( var i = 0; i < categories.length; i++ ) {
					var height = $.torro_text_height( categories[ i ], '13px Clear Sans', category_width );

					if ( category_height < height ) {
						category_height = height;
					}
				}

				var chart = c3.generate({
					bindto: '#' + id + '-chart',
					size: {
						width: chart_width
					},
					data: {
						columns: [ results ],
						type: 'bar',
						keys: {
							value: [ 'value' ]
						},
						colors: {
							values: '#0073aa'
						}
					},
					axis: {
						x: {
							type: 'category',
							categories: categories
						},
						y: {
							tick: {
								format: function(x) {
									return ( x == Math.floor(x)) ? x : '';
								}
							}
						}
					},
					legend: {
						show: false
					},
					tooltip: {
						format: {
							name: function( name, ratio, id, index ) {
								return value_text;
							}
						},
						position: function( data, width, height, element ) {
							return {
								top: 0,
								left: 0
							};
						}
					},
					padding: {
						bottom: category_height
					}
				});
			});
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
