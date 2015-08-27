<?php
/**
 * Questions Shortcodes
 *
 * Adding charts shortcodes into Questions
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
 * @version 1.0.0
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

if( !defined( 'ABSPATH' ) ){
	exit;
}

class QuestionsChartsShortCodes
{

	/**
	 * Adding Shortcodes and Actionhooks
	 */
	public static function init()
	{
		add_shortcode( 'survey_results', array( __CLASS__, 'survey_results' ) ); // @todo: Delete later, because it's deprecated
		add_shortcode( 'form_results', array( __CLASS__, 'form_results' ) );

		add_shortcode( 'question_results', array( __CLASS__, 'element_results' ) ); // @todo: Delete later, because it's deprecated
		add_shortcode( 'element_results', array( __CLASS__, 'element_results' ) );

		add_action( 'edit_form_after_title', array( __CLASS__, 'show_form_result_shortcode' ), 20 );
		add_action( 'questions_element_admin_tabs_bottom', array( __CLASS__, 'show_element_result_shortcode' ) );
	}


	/**
	 * Showing all results of a form
	 *
	 * @param $atts
	 *
	 * @return string|void
	 */
	public static function survey_results( $atts ){
		_deprecated_function( 'Shortcode [survey_results]', '1.0.0 beta 20', '[form_results]' );
		return self::form_results( $atts );
	}

	/**
	 * Showing all results of a form
	 *
	 * @param $atts
	 * @return string|void
	 */
	public static function question_results( $atts ){
		_deprecated_function( 'Shortcode [question_results]', '1.0.0 beta 20', '[element_results]' );
		return self::element_results( $atts );
	}

	/**
	 * Showing all results of a form
	 *
	 * @param $atts
	 *
	 * @return string|void
	 */
	public static function form_results( $atts )
	{
		$atts = shortcode_atts( array( 'id' => '' ), $atts );
		$form_id = $atts[ 'id' ];

		if( '' == $form_id || !qu_form_exists( $form_id ) ){
			return esc_attr( 'Please enter a valid form id into the shortcode!', 'questions-locale' );
		}

		$results = new Questions_Results( $form_id );

		$ordered_data = Questions_AbstractData::order_for_charting( $results->get_responses( FALSE, FALSE ) );

		$html = '';

		$count_bars = 0;
		foreach( $ordered_data[ 'questions' ] as $element_id => $question ):
			if( !array_key_exists( $element_id, $ordered_data[ 'data' ] ) ){
				continue;
			}

			$html .= Questions_ChartCreator_Dimple::show_bars( $question, $ordered_data[ 'data' ][ $element_id ] );
			$count_bars++;
		endforeach;

		if( 0 == $count_bars ){
			$html.= esc_attr( 'There are no results to show.', 'questions-locale' );
		}

		return $html;
	}

	/**
	 * Showing results of a question
	 *
	 * @param array $atts Arguments which can be added to the shortcode
	 * @return string $html HTML of results
	 */
	public static function element_results( $atts )
	{
		global $wpdb, $questions_global;

		$atts = shortcode_atts( array( 'id' => '', ), $atts );
		$element_id = $atts[ 'id' ];

		$sql = $wpdb->prepare( "SELECT id FROM {$questions_global->tables->questions} WHERE id = %d", $element_id );
		$element_id = $wpdb->get_var( $sql );

		if( '' == $element_id ){
			return esc_attr( 'Please enter a valid element id into the shortcode!', 'questions-locale' );
		}

		$sql = $wpdb->prepare( "SELECT questions_id FROM {$questions_global->tables->questions} WHERE id = %d", $element_id );
		$form_id = $wpdb->get_var( $sql );

		$results = new Questions_Results( $form_id );
		$ordered_data = Questions_AbstractData::order_for_charting( $results->get_responses( $element_id, FALSE ) );

		$html = '';
		foreach( $ordered_data[ 'questions' ] as $element_id => $question ){
			$html .= Questions_ChartCreator_Dimple::show_bars( $question, $ordered_data[ 'data' ][ $element_id ] );
		}

		return $html;
	}

	/**
	 * Showing survey result schortcodes in admin area for copy&paste
	 *
	 * @param int $survey_id Id of the survey
	 *
	 * @return string $html HTML for shortcode summary in admon
	 */
	public static function show_form_result_shortcode()
	{
		global $post;

		$html = '<div class="questions-options shortcode">';
		$html .= '<label for="form_results_shortcode">' . __( 'Charts Shortcode:', 'questions-locale' ) . '</label><br />';
		$html .= '<input type="text" id="form_results_shortcode" value="[form_results id=' . $post->ID . ']" />';
		$html .= '</div>';

		echo $html;
	}

	/**
	 * Showing question result schortcodes in admin area for copy&paste
	 *
	 * @param object $object Object element
	 *
	 * @return string $html HTML for shortcode summary in admin
	 */
	public static function show_element_result_shortcode( $object )
	{
		if( $object->id != '' && $object->is_analyzable ):
			$small = '<small>' . __( '(CTRL+C and paste into a post to embed element result charts in a post)', 'questions-locale' ) . '</small>';
			echo sprintf( '<div class="shortcode"><label for="element_result_shortcode_%d">' . __( 'Charts Shortcode:', 'questions-locale' ) . '</label><input class="shortcode_input" type="text" id="element_result_shortcode_%d" value="[element_results id=%d]" /> %s</div>', $object->id, $object->id, $object->id, $small );
		endif;
	}
}

QuestionsChartsShortCodes::init();