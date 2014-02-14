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

class SurveyVal_QuestionType_MultipleChoice extends SurveyVal_QuestionType{
	
	public function __construct( $id = null ){
		$this->slug = 'MultipleChoice';
		$this->title = __( 'Multiple Choice', 'surveyval-locale' );
		$this->description = __( 'Add a question which can be answered by selecting one ore more given answers.', 'surveyval-locale' );
		$this->icon = SURVEYVAL_URLPATH . '/assets/images/icon-multiplechoice.png';
		
		$this->preset_of_answers = TRUE;
		$this->preset_is_multiple = TRUE;
		$this->answer_is_multiple = TRUE;
		
		$this->answer_syntax = '<p><input type="checkbox" name="%s" value="%s" /> %s</p>';
		$this->answer_selected_syntax = '<p><input type="checkbox" name="%s" value="%s" checked /> %s</p>';
		$this->answer_params = array( 'name', 'value', 'answer' );
		
		$this->create_answer_syntax = '<p><input type="text" name="%s" value="%s" /></p>';
		$this->create_answer_params = array( 'name', 'answer' );
		
		parent::__construct( $id );
	}
	
	public function validate( $input ){
		return TRUE;
	}
}
sv_register_question_type( 'SurveyVal_QuestionType_MultipleChoice' );