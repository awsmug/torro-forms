<?php

class SurveyVal_QuestionType_Text extends SurveyVal_QuestionType{
	
	public function __construct( $id = null ){
		$this->slug = 'Text';
		$this->title = __( 'Text', 'surveyval-locale' );
		$this->description = __( 'Add a question which can be answered within a text field.', 'surveyval-locale' );
		
		$this->multiple_answers = FALSE;
		$this->has_answers = FALSE;
		
		$this->answer_syntax = '<p><input type="text" name="%s" value="%s" /></p>';
		$this->answer_params = array( 'name', 'value' );
		
		parent::__construct( $id );
	}
	
}
sv_register_question_type( 'SurveyVal_QuestionType_Text' );

class SurveyVal_QuestionType_MultipleChoice extends SurveyVal_QuestionType{
	
	public function __construct( $id = null ){
		$this->slug = 'MultipleChoice';
		$this->title = __( 'Multiple Choice', 'surveyval-locale' );
		$this->description = __( 'Add a question which can be answered by selecting one ore more given answers.', 'surveyval-locale' );
		
		$this->multiple_answers = TRUE;
		$this->has_answers = TRUE;
		
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
		
		$this->multiple_answers = TRUE;
		$this->has_answers = TRUE;
		
		$this->answer_syntax = '<p><input type="radio" name="%s" value="%s" /> %s</p>';
		$this->answer_params = array( 'name', 'value', 'answer' );
		
		$this->create_answer_syntax = '<p><input type="text" name="%s" value="%s" /></p>';
		$this->create_answer_params = array( 'name', 'answer' );
		
		parent::__construct( $id );
	}
	
}
sv_register_question_type( 'SurveyVal_QuestionType_OneChoice' );


