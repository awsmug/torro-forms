<?php

class SurveyVal_Survey{
	public $id;
	public $title;
	
	public $questions = array();
	public $response_errors = array();
	
	public function __construct( $id = null ){
		if( null != $id )
			$this->populate( $id );
	}
	
	private function populate( $id ){
		global $wpdb, $surveyval_global;
		
		$this->reset();
		
		$survey = get_post( $id );
		
		$this->id = $id;
		$this->title = $survey->post_title;
		
		$this->questions = $this->get_questions( $id );
	}
	
	private function get_questions( $id = null ){
		global $surveyval_global, $wpdb;
		
		if( null == $id )
			$id = $this->id;
		
		if( '' == $id )
			return FALSE;
		
		$sql = $wpdb->prepare( "SELECT * FROM {$surveyval_global->tables->questions} WHERE surveyval_id = %s ORDER BY sort ASC", $id );
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
		global $surveyval_global;
		
		if( !array_key_exists( $question_type, $surveyval_global->question_types ) )
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
		global $surveyval_global, $wpdb;
		
		if( '' == $this->id )
			return FALSE;
		
		$survey_questions = $_POST['surveyval'];
		$survey_deleted_questions = $_POST['surveyval_deleted_questions'];
		$survey_deleted_answers = $_POST['surveyval_deleted_answers'];
		
		// mail( 'sven@deinhilden.de', 'Test', print_r( $_POST, TRUE ) . print_r( $surveyval_global, TRUE ) );
		
		$survey_deleted_questions = explode( ',', $survey_deleted_questions );
		
		/*
		 * Deleting deleted answers
		 */
		if( is_array( $survey_deleted_questions ) && count( $survey_deleted_questions ) > 0 ):
			foreach( $survey_deleted_questions AS $deleted_question ):
				$wpdb->delete( 
					$surveyval_global->tables->questions, 
					array( 'id' => $deleted_question ) 
				);
				$wpdb->delete( 
					$surveyval_global->tables->answers, 
					array( 'question_id' => $deleted_question ) 
				);
			endforeach;
		endif;
		
		$survey_deleted_answers = explode( ',', $survey_deleted_answers );
		
		/*
		 * Deleting deleted answers
		 */
		if( is_array( $survey_deleted_answers ) && count( $survey_deleted_answers ) > 0 ):
			foreach( $survey_deleted_answers AS $deleted_answer ):
				$wpdb->delete( 
					$surveyval_global->tables->answers, 
					array( 'id' => $deleted_answer ) 
				);
			endforeach;
		endif;
		
		/*
		 * Saving questions
		 */
		foreach( $survey_questions AS $key => $survey_question ):
			$question_id = $survey_question['id'];
			$question = $survey_question['question'];
			$sort = $survey_question['sort'];
			$type = $survey_question['type'];
			$answers = array();
			$settings = array();
			
			$new_question = FALSE;
			
			if( array_key_exists( 'answers', $survey_question ) )
				$answers = $survey_question['answers'];
			
			if( array_key_exists( 'settings', $survey_question ) )
				$settings = $survey_question['settings'];
			
			// Saving question
			if( '' != $question_id ):
				// Updating if question already exists
				$wpdb->update(
					$surveyval_global->tables->questions,
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
					$surveyval_global->tables->questions,
					array(
						'surveyval_id' => $this->id,
						'question' => $question,
						'sort' => $sort,
						'type' => $type  )
				);
				
				$new_question = TRUE;
				$question_id = $wpdb->insert_id;
			endif;
			
			/*
			 * Saving answers
			 */
			if( is_array( $answers )  && count( $answers ) >  0 ):
				foreach( $answers AS $answer ):
					$answer_id = $answer['id'];
					$answer_text = $answer['answer'];
					$answer_sort = $answer['sort'];
					
					if( '' != $answer_id ):
						$wpdb->update(
							$surveyval_global->tables->answers,
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
							$surveyval_global->tables->answers,
							array(
								'question_id' => $question_id,
								'answer' => $answer_text,
								'sort' => $answer_sort
							)
						);
					endif;
				endforeach;
			endif;
			
			/*
			 * Saving answers
			 */
			if( is_array( $settings )  && count( $settings ) >  0 ):
				foreach( $settings AS $name => $setting ):
					$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$surveyval_global->tables->settings} WHERE question_id = %d AND name = %s", $question_id, $name );
					$count = $wpdb->get_var( $sql );
					
					if( $count > 0 ):
						$wpdb->update(
							$surveyval_global->tables->settings,
							array( 
								'value' => $settings[ $name ]
							),
							array(
								'question_id' => $question_id,
								'name' => $name
							)
						);
					else:
						$wpdb->insert(
							$surveyval_global->tables->settings,
							array(
								'name' => $name,
								'question_id' => $question_id,
								'value' => $settings[ $name ]
							)
						);
						
					endif;
				endforeach;
			endif;

		endforeach;
		
		return TRUE;
	}

	public function get_survey_html(){
		if( $this->has_participated() ):
			return $this->dialog_already_participated();
		endif;
		
		if( array_key_exists( 'surveyval_submission', $_POST ) ):
			if( '' != $_POST['surveyval_submission'] ):
				if( $this->save_response() ):
					return $this->dialog_thank_participation();
				endif;
			endif;
		endif;
		
		return $this->get_survey_form();
	}
	
	public function get_survey_form(){
		$html = '<form name="surveyval" id="surveyval" action="' . $_SERVER[ 'REQUEST_URI' ] . '" method="POST">';
		
		if( is_array( $this->response_errors ) && count( $this->response_errors ) > 0 ):
			echo '<div id="surveyval_errors" class="surveyval_errors">';
			foreach( $this->response_errors AS $error ):
				echo '<span>' . $error['message'] . '</span>';
			endforeach;
			echo '</div>';
		endif;
		
		if( is_array( $this->questions ) && count( $this->questions ) > 0 ):
			foreach( $this->questions AS $question ):
				$html.= $question->get_html();
			endforeach;
		else:
			return FALSE;
		endif;
		
		$html.= '<input type="submit" name="surveyval_submission" value="' . __( 'Send your answers', 'surveyval-locale' ) . '">';
		
		$html.= '</form>';
		
		return $html;
	}
	
	public function participated_polls( $user_id = NULL ){
		global $wpdb, $current_user, $surveyval_global;
		
		if( '' == $user_id ):
			get_currentuserinfo();
			$user_id = $user_id = $current_user->ID;
		endif;
		
		$sql = $wpdb->prepare( "SELECT id FROM {$surveyval_global->tables->responds} WHERE  user_id=%s", $user_id );
		return $wpdb->get_col( $sql );
	}

	public function has_participated( $user_id = NULL, $surveyval_id = NULL ){
		global $wpdb, $current_user, $surveyval_global;
		
		// Setting up user ID
		if( NULL == $user_id ):
			get_currentuserinfo();
			$user_id = $user_id = $current_user->ID;
		endif;
				
		// Setting up Survey ID
		if( NULL == $surveyval_id )
			if( !empty( $this->id ) )
				$surveyval_id = $this->id;
			else 
				return FALSE;
		
		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$surveyval_global->tables->responds} WHERE surveyval_id=%d AND user_id=%s", $surveyval_id, $user_id );
		$count = $wpdb->get_var( $sql );
		
		if( 0 == $count ):
			return FALSE;
		else:
			return TRUE;
		endif;
	}
	
	public function save_response(){
		global $wpdb, $surveyval_global, $current_user;
		
		$response = $_POST['surveyval_response'];
		$this->response_errors = array();
		$answer_error = FALSE;
		
		if( $this->has_participated() ):
			$this->response_errors[] = array(
				'message' => sprintf( __( 'You already have participated this poll!', 'surveyval-locale' ), $question->question )
			);
			$answer_error = TRUE;
		endif;
		
		// Are there any questions?
		if( is_array( $this->questions ) && count( $this->questions ) > 0 ):
			
			// Running thru all answers
			foreach( $this->questions AS $key => $question ):
				
				// Checking if question have been answered
				if( !array_key_exists( $question->id, $response ) ):
					$this->response_errors[] = array(
						'message' => sprintf( __( 'You missed to answer question "%s"!', 'surveyval-locale' ), $question->question ),
						'question_id' =>  $question->id
					);
					$this->questions[ $key ]->error = TRUE;
					$answer_error = TRUE;
				endif;
				
				$answer = '';
				
				if( array_key_exists( $question->id, $response) )
					$answer = $response[ $question->id ];
				
				// Taking response
				$this->questions[ $key ]->response = $answer;
				
				// Validating answer with custom validation
				if( !$question->validate( $answer ) ):
					
					// Gettign every error of question back
					foreach( $question->validate_errors AS $error ):
						$this->response_errors[] = array(
							'message' => $error,
							'question_id' =>  $question->id
						);
					endforeach;
					
					$this->questions[ $key ]->error = TRUE;
					$answer_error = TRUE;
					
				endif;
				
			endforeach;
			
		else:
			$this->response_errors[] = array(
				'message' => __( 'There are now questions to save in survey', 'surveyval-locale' ),
				'question_id' =>  0
			);
			$answer_error = TRUE;
		endif;
		
		// Saving answers if no error occured
		if( !$answer_error ):
			
			get_currentuserinfo();
			$user_id = $user_id = $current_user->ID;
			
			// Adding new question
			$wpdb->insert(
				$surveyval_global->tables->responds,
				array(
					'surveyval_id' => $this->id,
					'user_id' => $user_id,
					'timestamp' => time()  )
			);
			
			$respond_id = $wpdb->insert_id;
			
			foreach( $response AS $question_id => $answers ):
				
				if( is_array( $answers ) ):
					
					foreach( $answers AS $answer ):
						$wpdb->insert(
							$surveyval_global->tables->respond_answers,
							array(
								'respond_id' => $respond_id,
								'question_id' => $question_id,
								'value' => $answer
							)
						);
					endforeach;
					
				else:
					
					$wpdb->insert(
						$surveyval_global->tables->respond_answers,
						array(
							'respond_id' => $respond_id,
							'question_id' => $question_id,
							'value' => $answer
						)
					);
					
				endif;
			endforeach;
				
		endif;
			
			/*
		echo '<pre>';
		print_r( $_POST );
		echo '</pre>';
		
		echo '<pre>';
		print_r( $this->questions );
		echo '</pre>';
			 */
	}
	
	public function dialog_thank_participation(){
		$html = '<div id="surveyval-thank-participation">';
		$html.= __( 'Thank you for participating this survey!', 'surveyval-locale' );
		$html.= '</div>';
		return $html;
	}
	
	public function dialog_already_participated(){
		$html = '<div id="surveyval-already-participated">';
		$html.= __( 'You already have participated this poll!', 'surveyval-locale' );
		$html.= '</div>';
		return $html;
	}

	private function reset(){
		$this->questions = array();
	}
}

function sv_save_by_postdata( $id = null ){
	$survey = new SurveyVal_Survey( $id );
	return $survey->save_by_postdata();
}

function sv_user_has_participated( $user_id, $surveyval_id ){
	$survey = new SurveyVal_Survey();
	return $survey->has_participated( $user_id, $surveyval_id );
}






