<?php
/**
* @package WordPress
* @subpackage SurveyVal
* @author Sven Wagener
* @copyright 2014, Rheinschmiede
* @link http://rheinschmiede.de
* @license http://www.opensource.org/licenses/gpl-2.0.php GPL License
*/

// No direct access is allowed
if( ! defined( 'ABSPATH' ) ) exit;

class SurveyVal_SurveyElement_RangeEmotional extends SurveyVal_SurveyElement{
	
	public function __construct( $id = null ){
		$this->slug = 'RangeEmotional';
		$this->title = __( 'Range Emotional', 'surveyval-locale' );
		$this->description = __( 'Add a question which can be answered within a  emotional range scale.', 'surveyval-locale' );
		$this->icon = SURVEYVAL_URLPATH . '/assets/images/icon-range-emotional.png';
		
		$this->answer_syntax = '<input type="range" min="0" max="1000" name="%s" value="%s" />';
		$this->answer_params = array( 'name', 'value' );
		
		parent::__construct( $id );
	}
	
	public function settings_fields(){
		$this->settings_fields = array(
			'range_from' => array(
				'title'			=> __( 'Range from', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'This value will be shown at the beginning of the scale.', 'surveyval-locale' ),
				'default'		=> 'Good'
			), 
			'range_to' => array(
				'title'			=> __( 'Range to', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'This value will be shown at the end of the scale', 'surveyval-locale' ),
				'default'		=> 'Bad'
			), 
		);
	}
	
	public function before_answer( $html, $question_slug = NULL, $question_id = NULL ){
		$html.= '<span class="surveyval-range-from">' . $this->settings['range_from'] . '</span> ';
		return $html;
	}
	
	public function after_answer( $html, $question_slug = NULL, $question_id = NULL ){
		$html.= ' <span class="surveyval-range-to">' . $this->settings['range_to'] . '</span>';
		return $html;
	}	
	
	public function validate( $input ){
		return TRUE;
	}
}
sv_register_survey_element( 'SurveyVal_SurveyElement_RangeEmotional' );






