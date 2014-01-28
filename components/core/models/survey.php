<?php

class SurveyVal_Survey{
	var $id;
	var $title;
	
	var $questions = array();
	
	public function __construct( $id = null ){
		if( null != $id )
			$this->populate( $id );
	}
	
	public function populate( $id ){
		global $wpdb, $surveyval;
		
		$this->reset();
		
		$survey = get_post( $id );
		
		$this->id = $id;
		$this->title = $survey->post_title;
		
		$this->questions = $this->get_questions( $id );
	}
	
	public function get_questions( $id = null ){
		global $surveyval, $wpdb;
		
		if( null == $id )
			$id = $this->id;
		
		if( '' == $id )
			return FALSE;
		
		$sql = $wpdb->prepare( "SELECT * FROM {$surveyval->tables->questions} WHERE surveyval_id = %s", $id );
		$results = $wpdb->get_results( $sql );
		
		$questions = array();
		
		if( is_array( $results ) ):
			foreach( $results AS $result ):
				$class = 'SurveyVal_QuestionType_' . $result->type;
				$object = new $class( $result->id );
				$questions[] = $object;
			endforeach;
		endif;
		
		return $questions;
	}
	
	public function get_answers( $question_id ){
		global $surveyval, $wpdb;
		
		if( '' == $question_id )
			return FALSE;
		
		$sql = $wpdb->prepare( "SELECT * FROM {$surveyval->tables->answers} WHERE question_id = %s", $question_id );
		$results = $wpdb->get_results( $sql );
		
		return $results;
	}
	
	public function add_question_obj( $question_object, $order = null ){
		if( !is_object( $question_object ) || 'SurveyVal_QuestionType' != get_parent_class( $question_object ) )
			return FALSE;
		
		if( null == $order )
			$order = count( $this->questions );
		
		$this->questions[$order] = $question_object;
		
		return TRUE;
	}
	
	public function add_question( $question, $question_type, $answers = array(), $order = null ){
		global $surveyval;
		
		if( !array_key_exists( $question_type, $surveyval->question_types ) )
			return FALSE;
		
		$surveyval->question_types[ $question_type ]->add_question( $question );
		
		if( count( $answers ) > 0 )
			foreach( $answers AS $answer )
				$surveyval->question_types[ $question_type ]->add_answer( $answer );
			
		
		if( $this->add_question_obj( clone $surveyval->question_types[ $question_type ], $order ) ):
			$surveyval->question_types[ $question_type ]->reset();
			return TRUE;
		else:
			$surveyval->question_types[ $question_type ]->reset();
			return FALSE;
		endif;
	}
	
	public function save(){
		global $surveyval, $wpdb;
	}
	
	public function reset(){
		$this->questions = array();
	}
}
