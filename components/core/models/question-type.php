<?php

abstract class SurveyVal_QuestionType{
	var $slug;
	var $title;
	var $description;
	var $icon;
	
	var $question;
	
	var $has_answers = TRUE;
	var $multiple_answers = FALSE;
	var $answer_syntax = '<p>%1$s</p>';
	
	var $answers = array();
	var $answer_order = 0;

	var $initialized = FALSE;
	
	public function _register() {
		global $surveyval;
		
		if( TRUE == $this->initialized )
			return FALSE;
		
		if( !is_object( $surveyval ) )
			return FALSE;
		
		if( '' == $this->slug )
			$this->slug = get_class( $this );
		
		if( '' == $this->title )
			$this->title = ucwords( get_class( $this ) );
		
		if( '' == $this->description )
			$this->description =  __( 'This is a SurveyVal Question-Type.', 'surveyval-locale' );
		
		if( '' == $this->multiple_answers )
			$this->multiple_answers = FALSE;
		
		if( array_key_exists( $this->slug, $surveyval->question_types ) )
			return FALSE;
		
		if( !is_array( $surveyval->question_types ) )
			$surveyval->question_types = array();
		
		$this->initialized = TRUE;
		
		return $surveyval->add_question_type( $this->slug, $this );
	}
	
	public function set_question( $question ){
		if( '' == $question )
			return FALSE;
		
		$this->question = $question;
		
		return TRUE;
	}
	
	public function add_answer( $text, $order = FALSE ){
		if( '' == $text )
			return FALSE;
		
		if( FALSE == $this->multiple_answers && count( $this->answers ) > 0 )
			return FALSE;
		
		if( FALSE == $order )
			$order = $this->answer_order++;
		
		$this->answers[ $order ] = $text;
	}
	
	public function show_form_element(){
		if( '' == $this->question )
			return FALSE;
		
		if( 0 == count( $this->answers )  && $this->has_answers == TRUE )
			return FALSE;
		
		$html = '<p>' . $this->question . '<p>';
		
		foreach( $this->answers AS $answer ):
			$html.= sprintf( $this->answer_syntax, $answer, $this->slug );
		endforeach;
		
		echo $html;
	}
}

/**
 * Register a new Group Extension.
 *
 * @param string Name of the Question type class.
 * @return bool|null Returns false on failure, otherwise null.
 */
function sv_register_question_type( $question_type_class = '' ) {
	if ( ! class_exists( $question_type_class ) )
		return false;

	// Register the group extension on the bp_init action so we have access
	// to all plugins.
	add_action( 'plugins_loaded', create_function( '', '
		$extension = new ' . $question_type_class . ';
		add_action( "plugins_loaded", array( &$extension, "_register" ), 10 );
	' ), 1 );
}