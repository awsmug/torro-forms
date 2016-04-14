<?php
/**
 * Torro Forms Chart Shortcodes
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Charts
 * @version 1.0.0alpha1
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_ChartsShortCodes {

	/**
	 * Adding Shortcodes and Actionhooks
	 */
	public static function init() {
		add_shortcode( 'survey_results', array( __CLASS__, 'survey_results' ) ); // @todo Delete later, because it's deprecated
		add_shortcode( 'form_charts', array( __CLASS__, 'form_charts' ) );

		add_shortcode( 'question_results', array( __CLASS__, 'element_results' ) ); // @todo Delete later, because it's deprecated
		add_shortcode( 'element_chart', array( __CLASS__, 'element_chart' ) );

		add_action( 'torro_result_charts_postbox_bottom', array( __CLASS__, 'show_form_result_shortcode' ) );
		add_action( 'torro_result_charts_postbox_element', array( __CLASS__, 'show_element_result_shortcode' ) );
	}

	/**
	 * Showing all results of a form
	 *
	 * @param $atts
	 *
	 * @return string|void
	 */
	public static function survey_results( $atts ) {
		_deprecated_function( 'Shortcode [survey_results]', '1.0.0beta20', '[form_results]' );

		return self::form_charts( $atts );
	}

	/**
	 * Showing all results of a form
	 *
	 * @param $atts
	 *
	 * @return string|void
	 */
	public static function form_charts( $atts ) {
		$atts = shortcode_atts( array( 'id' => '' ), $atts );
		$form_id = $atts[ 'id' ];

		if ( empty( $form_id ) || ! torro()->forms()->get( $form_id )->exists() ) {
			return __( 'Please enter a valid form id into the shortcode!', 'torro-forms' );
		}

		$form_results = new Torro_Form_Results( $form_id );
		$results = $form_results->results();
		$results = Torro_Result_Charts::format_results_by_element( $results );

		$html = '';

		foreach ( $results as $headline => $element_result ) {
			$headline_arr = explode( '_', $headline );

			$element_id = (int) $headline_arr[ 1 ];
			$element = torro()->elements()->get_registered( $element_id );

			// Skip collecting Data if there is no analyzable Data
			if ( ! $element->input_answers ) {
				continue;
			}

			$chart_creator = Torro_Result_Charts_C3::instance();

			$html .= $chart_creator->bars( $element->label, $element_result );
		}

		return $html;
	}

	/**
	 * Showing all results of a form
	 *
	 * @param $atts
	 *
	 * @return string|void
	 */
	public static function question_results( $atts ) {
		_deprecated_function( 'Shortcode [question_results]', '1.0.0beta20', '[element_results]' );

		return self::element_chart( $atts );
	}

	/**
	 * Showing results of an Element
	 *
	 * @param array $atts Arguments which can be added to the shortcode
	 *
	 * @return string $html HTML of results
	 */
	public static function element_chart( $atts ) {
		global $wpdb;

		$atts = shortcode_atts( array( 'id' => '' ), $atts );

		$element = torro()->elements()->get( $atts['id'] );
		if ( is_wp_error( $element ) ) {
			return __( 'Please enter a valid element id into the shortcode.', 'torro-forms' );
		}

		$container = torro()->containers()->get( $element->container_id );
		if ( is_wp_error( $container ) ) {
			return __( 'It looks like the container for this element has been removed. Please enter a different element id into the shortcode.', 'torro-forms' );
		}

		$form_results = new Torro_Form_Results( $container->form_id );
		$results = $form_results->element_results( $element->id );
		$results = Torro_Result_Charts::format_results_by_element( $results );

		$chart_creator = new Torro_Result_Charts_C3();

		$html = $chart_creator->bars( $element->label, $results[ 'element_' . $element->id ] );

		return $html;
	}

	/**
	 * Showing Form result Shortcodes in Admin for copy&paste
	 *
	 * @param int $form_id Id of the form
	 *
	 * @return string $html HTML for shortcode summary in admon
	 */
	public static function show_form_result_shortcode() {
		global $post;

		if ( ! torro_is_formbuilder() ) {
			return;
		}

		$html  = '<div class="in-postbox-one-third">' . torro_clipboard_field( __( 'Charts Shortcode', 'torro-forms' ), '[form_charts id=' . $post->ID . ']' ) . '</div>';

		echo $html;
	}

	/**
	 * Showing Element result schortcodes in admin area for copy&paste
	 *
	 * @param object $object Object element
	 *
	 * @return string $html HTML for shortcode summary in admin
	 */
	public static function show_element_result_shortcode( $object ) {
		if ( ! empty( $object->id ) && $object->input_answers ) {
			echo torro_clipboard_field( __( 'Element Charts Shortcode', 'torro-forms' ), '[element_chart id=' .  $object->id . ']' );
		}
	}
}

Torro_ChartsShortCodes::init();
