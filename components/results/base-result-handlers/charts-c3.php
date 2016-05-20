<?php
/**
 * Components: Torro_Result_Charts_C3 class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.2
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Torro_Result_Charts_C3 extends Torro_Result_Charts {
	/**
	 * Instance
	 *
	 * @var Torro_Result_Charts_C3
	 */
	private static $instance = null;

	/**
	 * Singleton
	 *
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->name = 'c3';
		$this->title = __( 'Charts', 'torro-forms' );
		$this->description = __( 'Chart creating with C3.', 'torro-forms' );
	}

	/**
	 * Showing bars
	 *
	 * @param string $title Title
	 * @param array  $answers
	 * @param array  $attr
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function bars( $title, $results, $params = array() ) {
		$defaults = array(
			'id'		=> 'c3' . md5( rand() ),
			'title_tag' => 'h3',
		);

		$params = wp_parse_args( $defaults, $params );

		$id = $params['id'];
		$title_tag = $params['title_tag'];

		$value_text = __( 'Count', 'torro-forms' );

		/**
		 * Preparing Data for JS
		 */
		$data = array( "'values'" );
		foreach ( $results as $value ) {
			$data[] = $value;
		}
		$column_data = '[ [' . implode( ',', $data ) . ' ] ]';

		$categories = array_keys( $results );
		$c3_categories = array();
		foreach ( $categories AS $key => $category ) {
			$c3_categories[ $key ] = '\'' . $category . '\'';
		}
		$categories = implode( '###', $categories );
		$c3_categories = implode( ',', $c3_categories );

		/**
		 * C3 Chart Script
		 */
		$html  = '<div id="' . $id . '" class="chart chart-c3">';
		$html .= '<' . $title_tag . '>' . $title . '</' . $title_tag . '>';
		$html .= '<div id="' . $id . '-chart"></div>';
		$html .= "<script>
	jQuery(document).ready( function($){

		var chart_width = '';
		var label_height = '';

		if ( $( '#form-result-handlers-tabs' ).length ) {
			var tab_width = $( '#form-result-handlers-tabs' ).width();
			chart_width = Math.round( ( tab_width / 100 * 95 ) );
		}

		var categories = '{$categories}';
		categories = categories.split( '###' );
		var category_width = Math.round( ( chart_width / categories.length ) );


		var highest = 0;
		for( i = 0; i < categories.length; i++ ){
			var height = $.torro_text_height( categories[ i ], '13px Clear Sans', category_width  );

			if( highest < height )
			{
				highest = height;
			}
		}
		var category_height = highest;

		var chart_{$id} = c3.generate({
			bindto: '#{$id}-chart',
			size: {
				width: chart_width
			},
			data: {
				columns: {$column_data},
				type: 'bar',
				keys: {
					value: [ 'value' ],
				},
				colors: {
					values: '#0073aa',
				}
			},
			axis: {
				x: {
					type: 'category',
					categories: [{$c3_categories}]
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
					name: function (name, ratio, id, index) {
						return '{$value_text}';
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
</script>";

		$html .= '</div>';

		return $html;
	}

	public function pies( $title, $results, $params = array() ) {}

	/**
	 * Loading Admin Styles
	 *
	 * @since 1.0.0
	 */
	public function admin_styles() {
		wp_enqueue_style( 'c3', torro()->get_asset_url( 'c3/c3', 'vendor-css' ) );
		wp_enqueue_style( 'torro-results-charts-c3', torro()->get_asset_url( 'results-charts-c3', 'css' ), array( 'torro-form-edit' ) );
	}

	/**
	 * Loading Admin Scripts
	 *
	 * @since 1.0.0
	 */
	public function admin_scripts() {
		wp_enqueue_script( 'd3', torro()->get_asset_url( 'd3/d3', 'vendor-js' ) );
		wp_enqueue_script( 'c3', torro()->get_asset_url( 'c3/c3', 'vendor-js' ) );
		wp_enqueue_script( 'torro-results-charts-c3', torro()->get_asset_url( 'results-charts-c3', 'js' ), array( 'torro-form-edit', 'd3', 'c3' ) );
	}

	/**
	 * Loading Frontend Styles
	 *
	 * @since 1.0.0
	 */
	public function frontend_styles() {
		$load = apply_filters( 'torro_load_frontend_charts_css', true );
		if ( ! $load ) {
			return;
		}

		wp_enqueue_style( 'c3', torro()->get_asset_url( 'c3/c3', 'vendor-css' ) );
		wp_enqueue_style( 'torro-results-charts-c3', torro()->get_asset_url( 'results-charts-c3', 'css' ), array() );
	}

	/**
	 * Loading Frontend Scripts
	 *
	 * @since 1.0.0
	 */
	public function frontend_scripts() {
		$load = apply_filters( 'torro_load_frontend_charts_js', true );
		if ( ! $load ) {
			return;
		}

		wp_enqueue_script( 'd3', torro()->get_asset_url( 'd3/d3', 'vendor-js' ) );
		wp_enqueue_script( 'c3', torro()->get_asset_url( 'c3/c3', 'vendor-js' ) );
		wp_enqueue_script( 'torro-results-charts-c3', torro()->get_asset_url( 'results-charts-c3', 'js' ), array( 'jquery', 'd3', 'c3' ) );
	}
}

torro()->resulthandlers()->register( 'Torro_Result_Charts_C3' );
