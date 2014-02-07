<?php

class SurveyVal_Survey{
	public $id;
	public $title;
	
	public $questions = array();
	
	public function __construct( $id = null ){
		if( null != $id )
			$this->populate( $id );
	}
	
	private function populate( $id ){
		global $wpdb, $surveyval;
		
		$this->reset();
		
		$survey = get_post( $id );
		
		$this->id = $id;
		$this->title = $survey->post_title;
		
		$this->questions = $this->get_questions( $id );
	}
	
	private function get_questions( $id = null ){
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
	
	private function add_question( $question, $question_type, $order = null ){
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
			
		
		if( !$this->add_question_obj( $object, $order ) ):
			return FALSE;
		else:
			
		endif;
	}
	
	private function add_question_obj( $question_object, $order = null ){
		if( !is_object( $question_object ) || 'SurveyVal_QuestionType' != get_parent_class( $question_object ) )
			return FALSE;
		
		if( null == $order )
			$order = count( $this->questions );
		
		$this->questions[$order] = $question_object;
		
		return TRUE;
	}
	
	
	public function save_by_postdata(){
		global $surveyval, $wpdb;
		
		if( '' == $this->id )
			return FALSE;
		
		$survey_questions = $_POST['surveyval'];
		
		mail( 'sven@deinhilden.de', 'Test', print_r( $survey_questions, TRUE ) . print_r( $surveyval, TRUE ) );
		
		foreach( $survey_questions AS $key => $survey_question ):
			$question_id = $survey_question['id'];
			$question = $survey_question['question'];
			$sort = $survey_question['sort'];
			$type = $survey_question['type'];
			$answers = array();
			
			if( array_key_exists( 'answers', $survey_question ) )
				$answers = $survey_question['answers'];
			
			// Saving question
			if( '' != $question_id ):
				// Updating if question already exists
				$wpdb->update( 
					$surveyval->tables->questions,
					array(
						'question' => $question,
						'sort' => $sort,
						'type' => $type
					),
					array(
						'id' => $question_id
					)
				);
			else:
				if( '' == $question ) // Questions can't not be empty
					continue;
				
				// Adding new question
				$wpdb->insert(
					$surveyval->tables->questions,
					array(
						'surveyval_id' => $this->id,
						'question' => $question,
						'sort' => $sort,
						'type' => $type  )
				);
				
				$question_id = $wpdb->insert_id;
			endif;
			
			// Saving answers
			if( is_array( $answers )  && count( $answers ) >  0 ):
				foreach( $answers AS $answer ):
					$answer_id = $answer['id'];
					$answer_text = $answer['answer'];
					$answer_sort = $answer['sort'];
					
					if( '' != $answer_id ):
						$wpdb->update(
							$surveyval->tables->answers,
							array( 
								'answer' => $answer_text,
								'sort' => $answer_sort
							),
							array(
								'id' => $answer_id
							)
						);
					else:
						$wpdb->insert(
							$surveyval->tables->answers,
							array(
								'question_id' => $question_id,
								'answer' => $answer_text,
								'sort' => $answer_sort
							)
						);
					endif;
				endforeach;
			endif;

		endforeach;
		
		return TRUE;
	}
	
	private function reset(){
		$this->questions = array();
	}
}

function sv_save_by_postdata( $id = null ){
	$survey = new SurveyVal_Survey( $id );
	return $survey->save_by_postdata();
}