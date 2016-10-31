<?php
/**
 * Components: Torro_Result_Charts_C3 class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.7
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

		$categories = array_keys( $results );

		$html  = '<div id="' . $id . '" class="chart chart-c3" data-categories="' . implode( '###', $categories ) . '" data-results="' . implode( '###', $results ) . '" data-value-text="' . $value_text . '">';
		$html .= '<' . $title_tag . '>' . $title . '</' . $title_tag . '>';
		$html .= '<div id="' . $id . '-chart"></div>';
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
		$load = apply_filters( 'torro_load_frontend_charts_css', torro()->is_chart() );
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
		$load = apply_filters( 'torro_load_frontend_charts_js', torro()->is_chart() );
		if ( ! $load ) {
			return;
		}

		wp_enqueue_script( 'd3', torro()->get_asset_url( 'd3/d3', 'vendor-js' ) );
		wp_enqueue_script( 'c3', torro()->get_asset_url( 'c3/c3', 'vendor-js' ) );
		wp_enqueue_script( 'torro-results-charts-c3', torro()->get_asset_url( 'results-charts-c3', 'js' ), array( 'jquery', 'd3', 'c3' ) );
	}
}

torro()->resulthandlers()->register( 'Torro_Result_Charts_C3' );
