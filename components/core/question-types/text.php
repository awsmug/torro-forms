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

class SurveyVal_QuestionType_Text extends SurveyVal_QuestionType{
	
	public function __construct( $id = null ){
		$this->slug = 'Text';
		$this->title = __( 'Text', 'surveyval-locale' );
		$this->description = __( 'Add a question which can be answered within a text field.', 'surveyval-locale' );
		$this->icon = SURVEYVAL_URLPATH . '/assets/images/icon-textfield.png';
		
		$this->has_answers = FALSE;
		$this->multiple_answers = FALSE;
		
		$this->answer_syntax = '<p><input type="text" name="%s" value="%s" /></p>';
		$this->answer_params = array( 'name', 'value' );
		
		parent::__construct( $id );
	}
	
	public function settings_fields(){
		$this->settings_fields = array(
			'max_length' => array(
				'title'			=> __( 'Maximum length', 'surveyval-locale' ),
				'type'			=> 'text',
				'descripton' 	=> __( 'The maximum number of chars which can be typed in.' ),
				'default'		=> ''
			), 
		);
	}
	
	public function validate( $input ){
		return $input;
	}
}
sv_register_question_type( 'SurveyVal_QuestionType_Text' );

class SurveyVal_QuestionType_MultipleChoice extends SurveyVal_QuestionType{
	
	public function __construct( $id = null ){
		$this->slug = 'MultipleChoice';
		$this->title = __( 'Multiple Choice', 'surveyval-locale' );
		$this->description = __( 'Add a question which can be answered by selecting one ore more given answers.', 'surveyval-locale' );
		$this->icon = SURVEYVAL_URLPATH . '/assets/images/icon-multiplechoice.png';
		
		$this->has_answers = TRUE;
		$this->multiple_answers = TRUE;
		
		$this->answer_syntax = '<p><input type="checkbox" name="%s" value="%s" /> %s</p>';
		$this->answer_params = array( 'name', 'value', 'answer' );
		
		$this->create_answer_syntax = '<p><input type="text" name="%s" value="%s" /></p>';
		$this->create_answer_params = array( 'name', 'answer' );
		
		parent::__construct( $id );
	}
	
}
sv_register_question_type( 'SurveyVal_QuestionType_MultipleChoice' );

class SurveyVal_QuestionType_OneChoice extends SurveyVal_QuestionType{
	
	public function __construct( $id = null ){
		$this->slug = 'OneChoice';
		$this->title = __( 'One Choice', 'surveyval-locale' );
		$this->description = __( 'Add a question which can be answered by selecting one of the given answers.', 'surveyval-locale' );
		$this->icon = SURVEYVAL_URLPATH . '/assets/images/icon-onechoice.png';
		
		$this->has_answers = TRUE;
		$this->multiple_answers = TRUE;
		
		$this->answer_syntax = '<p><input type="radio" name="%s" value="%s" /> %s</p>';
		$this->answer_params = array( 'name', 'value', 'answer' );
		
		$this->create_answer_syntax = '<p><input type="text" name="%s" value="%s" /></p>';
		$this->create_answer_params = array( 'name', 'answer' );
		
		parent::__construct( $id );
	}
	
}
sv_register_question_type( 'SurveyVal_QuestionType_OneChoice' );


