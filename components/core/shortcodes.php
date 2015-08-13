<?php
/**
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

class QuestionsShortCodes{
	var $tables;
	var $components = array();
	var $question_types = array();
	
	public static function init(){
		add_shortcode( 'survey', array( __CLASS__, 'survey' ) );
		add_action( 'questions_survey_options', array( __CLASS__ , 'show_survey_shortcode' ), 5 );
	}
	
	public static function survey( $atts ){
		global $Questions_FormProcess;
		
		$atts = shortcode_atts( array(
				'id' => '',
				'title' => __( 'Survey', 'questions-locale' )
			),
			$atts );
		
		if( '' == $atts[ 'id' ] ):
			_e( 'Please enter an id in the survey shortcode!', 'questions-locale' );
			return;
		endif;
		
		if( !qu_form_exists( $atts[ 'id' ] ) ):
			_e( 'Survey not found. Please enter another ID in your shortcode.', 'questions-locale' );
			return;
		endif;
		
		return $Questions_FormProcess->show_survey( $atts[ 'id' ] );
	}
	
	public static function show_survey_shortcode( $survey_id ){
		$html = '<div class="questions-options shortcode">';
		$html.= '<label for="survey_results_shortcode">' . __( 'Survey Shortcode:', 'questions-locale' ) . '</label><br />';
		$html.= '<input type="text" id="survey_results_shortcode" value="[survey id=' . $survey_id . ']" />';
		$html.= '</div>';
		
		echo $html;	
	}
}
QuestionsShortCodes::init();