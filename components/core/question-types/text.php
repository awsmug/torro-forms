<?php

class SurveyVal_QuestionType_Text extends SurveyVal_QuestionType{
	
	public function __construct(){
		$this->slug = 'text';
		$this->title = __( 'Text', 'surveyval-locale' );
		$this->description = __( 'Add a question which can be answered within a text field.', 'surveyval-locale' );
		$this->icon = '';
		$this->has_answers = FALSE;
		$this->answer_syntax = '<p><input type="text" name="%2$s" value="%3$s" /></p>';
	}
	
}
sv_register_question_type( 'SurveyVal_QuestionType_Text' );
