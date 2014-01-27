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
		global $surveyval;
		
		$survey = get_post( $id );
		
		$this->id = $id;
		$this->title = $survey->post_title;
	}
	
	public function get_questions( $id = null ){
		global $surveyval, $wpdb;
		
		if( null == $id )
			$id = $this->id;
		
		if( '' != $id ):
			$sql = $wpdb->prepare( 'SELECT * FROM %s WHERE surveyval_id = "%s"', $surveyval->tables->questions, $id );
			$results = $wpdb->query( $sql );
		endif;
		
		return $results;
	}
	
	public function add_question( $question_object, $order = null ){
		if( !is_object( $question_object ) || 'SurveyVal_QuestionType' != get_parent_class( $question_object ) )
			return FALSE;
		
		if( null == $order )
			$order = count( $this->questions );
		
		$this->questions[ $order ] = $question_object;
		
		return TRUE;
	}
	
	public function save(){
		
	}
}
