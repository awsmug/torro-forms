<?php
/*
 * Questions Shortcodes
 *
 * This should be used as parent class for Question-Answers.
 *
 * @author awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2

  Copyright 2015 awesome.ug (support@awesome.ug)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

if ( !defined( 'ABSPATH' ) ) exit;

class QuestionsChartsShortCodes{

    /**
     * Adding Shortcodes and Actionhooks
     */
	public static function init(){
		add_shortcode( 'survey_results', array( __CLASS__ , 'survey_results' ) );
		add_shortcode( 'question_results', array( __CLASS__ , 'question_results' ) );
		
		add_action( 'questions_survey_options', array( __CLASS__ , 'show_survey_result_shortcode' ) );
		add_action( 'questions_element_admin_tabs_content', array( __CLASS__ , 'show_question_result_shortcode' ) );
	}

    /**
     * Showing all results of a survey
     * @param $atts
     * @return string|void
     */
	public static function survey_results( $atts ){
		$atts = shortcode_atts( array(
					'id' => ''
				), $atts );
		
		if( '' == $atts[ 'id' ] ):
			_e( 'Please enter a survey id in the survey shortcode!', 'questions-locale' );
			return;
		endif;
		
		$survey = new Questions_Survey( $atts[ 'id' ] );
		$response = $survey->get_responses( FALSE, FALSE );

		$ordered_data = Questions_AbstractData::order_for_charting( $response );

		$html = '';
        $count_bars = 0;
		foreach ( $ordered_data[ 'questions' ] as $question_id => $question ):
            if( !array_key_exists( $question_id, $ordered_data['data']  ) )
                continue;

			$html.= Questions_ChartCreator_Dimple::show_bars( $question, $ordered_data['data'][ $question_id ] );
            $count_bars++;
		endforeach;

        if( 0 == $count_bars )
            _e( 'There are no results to show.', 'questions-locale' );
		
		return $html;
	}

    /**
     * Showing results of a question
     * @param array $atts Arguments which can be added to the shortcode
     * @return string $html HTML of results
     */
	public static function question_results( $atts ){
		global $wpdb, $questions_global;
		
		$atts = shortcode_atts( array(
					'id' => '',
				), $atts );
				
		if( '' == $atts[ 'id' ] ):
			_e( 'Please enter a question id in the survey shortcode!', 'questions-locale' );
			return;
		endif;
		
		$sql = $wpdb->prepare( "SELECT questions_id FROM {$questions_global->tables->questions} WHERE id = %d", $atts[ 'id' ] );
		$survey_id = $wpdb->get_var( $sql );
		
		$survey = new Questions_Survey( $survey_id );
		$ordered_data = Questions_AbstractData::order_for_charting( $survey->get_responses( $atts[ 'id' ], FALSE ) );
		
		$html = '';
		foreach ( $ordered_data[ 'questions' ] as $question_id => $question ):
			$html.= Questions_ChartCreator_Dimple::show_bars( $question, $ordered_data['data'][ $question_id ] );
		endforeach;
		
		return $html;
	}

    /**
     * Showing survey result schortcodes in admin area for copy&paste
     * @param int $survey_id Id of the survey
     * @return string $html HTML for shortcode summary in admon
     */
	public static function show_survey_result_shortcode( $survey_id ){
		$html = '<div class="questions-options shortcode">';
		$html.= '<label for="survey_results_shortcode">' . __( 'Results Shortcode:', 'questions-locale' ) . '</label><br />';
		$html.= '<input type="text" id="survey_questions_shortcode" value="[survey_results id=' . $survey_id . ']" />';
		$html.= '</div>';
		
		echo $html;	
	}

    /**
     * Showing question result schortcodes in admin area for copy&paste
     * @param object $object Object element
     * @return string $html HTML for shortcode summary in admin
     */
	public static function show_question_result_shortcode( $object ){
		if( $object->id != '' && $object->is_analyzable ):
			$small = '<small>' . __( '(CTRL+C and paste into post to embed question results in post)', 'questions-locale' ) . '</small>';
			echo sprintf( '<div class="shortcode"><label for="question_shortcode_%d">' . __( 'Shortcode:', 'questions-locale' ) . '</label><input class="shortcode_input" type="text" id="question_shortcode_%d" value="[question_results id=%d]" /> %s</div>', $object->id, $object->id, $object->id, $small );
		endif;
	}
}
QuestionsChartsShortCodes::init();