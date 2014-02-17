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

class SurveyVal_QuestionType_RangeEmotional extends SurveyVal_QuestionType{
	
	public function __construct( $id = null ){
		$this->slug = 'RangeEmotional';
		$this->title = __( 'Range Emotional', 'surveyval-locale' );
		$this->description = __( 'Add a question which can be answered within a  emotional range scale.', 'surveyval-locale' );
		$this->icon = SURVEYVAL_URLPATH . '/assets/images/icon-range-emotional.png';
		
		$this->answer_syntax = '<p><input type="range" min="0" max="1000" name="%s" value="%s" /></p>';
		$this->answer_params = array( 'name', 'value' );
		
		parent::__construct( $id );
	}
	
	public function settings_fields(){
		$this->settings_fields = array(
			'min_length' => array(
				'title'			=> __( 'Range from', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'This value will be shown at the beginning of the scale.', 'surveyval-locale' ),
				'default'		=> 'Good'
			), 
			'max_length' => array(
				'title'			=> __( 'Range to', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'This value will be shown at the end of the scale', 'surveyval-locale' ),
				'default'		=> 'Bad'
			), 
		);
	}
	
	public function validate( $input ){
		return TRUE;
	}
}
sv_register_question_type( 'SurveyVal_QuestionType_RangeEmotional' );






