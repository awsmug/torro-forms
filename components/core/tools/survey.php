<?php

class SurveyVal_PostSurvey extends SurveyVal_Post{
	var $questions;
	
	public function __construct( $survey_id ){
		parent::__construct( $survey_id );
		
		$this->questions = $this->get_questions( $survey_id );
	}
	
	public function dublicate( $copy_meta = TRUE, $copy_comments = TRUE, $copy_questions = TRUE, $copy_answers = TRUE, $draft = FALSE ){
		$new_survey_id = parent::dublicate( $copy_meta, $copy_comments, $draft );
		
		if( $copy_questions ):
			$this->dublicate_questions( $new_survey_id, $copy_answers );
		endif;
		
		return $new_post_id;
	}
	
	public function dublicate_questions( $new_survey_id, $copy_answers = TRUE ){
		global $wpdb, $surveyval_global;
		
		if( empty( $new_survey_id ) )
			return FALSE;
		
		// Dublicate answers
		if( is_array( $this->questions ) && count( $this->questions ) ):
			foreach( $this->questions AS $question ):
				$data = (array) $question;
				$data[ 'surveyval_id' ] = $new_survey_id;
				
				unset( $data[ 'id' ] );
				unset( $data[ 'answers' ] );
				
				$wpdb->insert( 
					$surveyval_global->tables->questions, 
					$data, 
					array( 
						'%d',
						'%s',
						'%d',
						'%s'
					)
				);
				
				$new_question_id = $wpdb->insert_id;
				
				// Dublicate answers
				if( is_array( $question->answers ) && count( $question->answers ) && $copy_answers ):
					foreach( $question->answers AS $answer ):
						$data = (array) $question;
				
						unset( $data[ 'id' ] );
				
						$wpdb->insert( 
							$surveyval_global->tables->answers, 
							$data,
							array( 
								'%d', 
								'%s',
								'%s',
								'%d',
							)
						);
					endforeach;
				endif;
			endforeach;	
		endif;	
	}
	
	public function get_questions( $survey_id ){
		global $wpdb, $surveyval_global;
		
		if( empty( $survey_id ) )
			return FALSE;
		
		$sql = $wpdb->prepare( "SELECT * FROM {$surveyval_global->tables->questions} WHERE surveyval_id = %d", $survey_id );
		$results =  $wpdb->get_results( $sql );
		
		foreach( $results AS $result ):
			$result->answers = $this->get_answers( $result->id );
		endforeach;
			
		return $results;
	}
	
	public function get_answers( $question_id ){
		global $wpdb, $surveyval_global;
		
		if( empty( $question_id ) )
			return FALSE;
		
		$sql = $wpdb->prepare( "SELECT * FROM {$surveyval_global->tables->answers} WHERE question_id = %d", $question_id );
		return $wpdb->get_results( $sql );
	}
}


/*
$test = new SurveyVal_PostSurvey( 350 );
$test->dublicate();
echo '<pre>';
print_r( $test );
echo '</pre>';
