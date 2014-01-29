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
		
		$sql = $wpdb->prepare( "SELECT * FROM {$surveyval->tables->questions} WHERE surveyval_id = %s ORDER BY sort ASC", $id );
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
	
	public function question( $question, $question_type, $answers = array(), $order = null, $question_id = null ){
		global $surveyval;
		
		if( !array_key_exists( $question_type, $surveyval->question_types ) )
			return FALSE;
		
		$class = 'SurveyVal_QuestionType_' . $question_type;
		
		if( null == $question_id )
			$object = new $class();
		else
			$object = new $class( $question_id );
		
		$object->question( $question, $order );
		
		if( count( $answers ) > 0 )
			foreach( $answers AS $answer )
				$object->answer( $answer['text'], $answer['order'], $answer['id'] );
			
		
		if( $this->add_question_obj( $object, $order ) ):
			return TRUE;
		else:
			return FALSE;
		endif;
	}
	
	public function add_question_obj( $question_object, $order = null ){
		if( !is_object( $question_object ) || 'SurveyVal_QuestionType' != get_parent_class( $question_object ) )
			return FALSE;
		
		if( null == $order )
			$order = count( $this->questions );
		
		$this->questions[$order] = $question_object;
		
		return TRUE;
	}
	
	public function save(){
		global $surveyval, $wpdb;
		
		if( '' == $this->id )
			return FALSE;
		
		foreach( $this->questions AS $question ):
			$question->save( $this->id );
		endforeach;
	}
	
	public function reset(){
		$this->questions = array();
	}
}
