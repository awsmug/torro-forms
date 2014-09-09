<?php

class SurveyVal_PostSurvey extends SurveyVal_Post{
	var $questions;
	var $settings;
	var $participiants;
	var $question_transfers = array();
	var $answer_transfers = array();
	
	
	public function __construct( $survey_id ){
		parent::__construct( $survey_id );
		
		$this->questions = $this->get_questions( $survey_id );
		$this->participiants = $this->get_participiants( $survey_id );
	}
	
	public function dublicate( $copy_meta = TRUE, $copy_comments = TRUE, $copy_questions = TRUE, $copy_answers = TRUE, $copy_participiants = TRUE, $draft = FALSE ){
		$new_survey_id = parent::dublicate( $copy_meta, $copy_comments, $draft );
		
		if( $copy_questions ):
			$this->dublicate_questions( $new_survey_id, $copy_answers );
		endif;
		
		if( $copy_participiants ):
			$this->dublicate_participiants( $new_survey_id );
		endif;
		
		do_action( 'surveyval_dublicate_survey', $this->post, $new_survey_id, $this->question_transfers, $this->answer_transfers );
		
		return $new_survey_id;
	}
	
	public function dublicate_questions( $new_survey_id, $copy_answers = TRUE, $copy_settings = TRUE ){
		global $wpdb, $surveyval_global;
		
		if( empty( $new_survey_id ) )
			return FALSE;
		
		// Dublicate answers
		if( is_array( $this->questions ) && count( $this->questions ) ):
			foreach( $this->questions AS $question ):
				$data = (array) $question;
				$old_question_id = $data[ 'id' ];
				$data[ 'surveyval_id' ] = $new_survey_id;
				
				unset( $data[ 'id' ] );
				unset( $data[ 'answers' ] );
				unset( $data[ 'settings' ] );
				
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
				
				$this->question_transfers[ $old_question_id ] = $new_question_id;
				
				unset( $data );
				
				// Dublicate answers
				if( is_array( $question->answers ) && count( $question->answers ) && $copy_answers ):
					foreach( $question->answers AS $answer ):
						$data = (array) $answer;
						$old_answer_id = $data[ 'id' ];
						
						$data[ 'question_id' ] = $new_question_id;
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
						
						$new_answer_id = $wpdb->insert_id;
						$this->answer_transfers[ $old_answer_id ] = $new_answer_id;
						
					endforeach;
				endif;
				
				// Dublicate Settings
				if( is_array( $question->settings ) && count( $question->settings ) && $copy_settings ):
					foreach( $question->settings AS $setting ):
						$data = (array) $setting;
						$data[ 'question_id' ] = $new_question_id;
						unset( $data[ 'id' ] );
						
						$wpdb->insert( 
							$surveyval_global->tables->settings, 
							$data,
							array( 
								'%d', 
								'%s',
								'%s'
							)
						);
					endforeach;
				endif;
				
				do_action( 'surveyval_dublicate_survey_question', $question, $new_question_id );
				
			endforeach;	
		endif;	
	}

	public function dublicate_participiants( $new_survey_id ){
		global $wpdb, $surveyval_global;
		
		if( empty( $new_survey_id ) )
			return FALSE;
		
		// Dublicate answers
		if( is_array( $this->participiants ) && count( $this->participiants ) ):
			foreach( $this->participiants AS $participiant ):
				$data = (array) $participiant;
				$data[ 'survey_id' ] = $new_survey_id;
				
				unset( $data[ 'id' ] );
				
				$wpdb->insert( 
					$surveyval_global->tables->participiants, 
					$data, 
					array( 
						'%d',
						'%d',
					)
				);
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
			$result->settings = $this->get_settings( $result->id );
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
	
	public function get_settings( $question_id ){
		global $wpdb, $surveyval_global;
		
		if( empty( $question_id ) )
			return FALSE;
		
		$sql = $wpdb->prepare( "SELECT * FROM {$surveyval_global->tables->settings} WHERE question_id = %d", $question_id );
		return $wpdb->get_results( $sql );
	}
	
	public function get_participiants( $survey_id ){
		global $wpdb, $surveyval_global;
		
		if( empty( $survey_id ) )
			return FALSE;
		
		$sql = $wpdb->prepare( "SELECT user_id FROM {$surveyval_global->tables->participiants} WHERE survey_id = %d", $survey_id );
		return $wpdb->get_results( $sql );
	}
}