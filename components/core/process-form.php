<?php
/*
 * Processing form
 *
 * This class initializes the component.
 *
 * @author rheinschmiede.de, Author <kontakt@rheinschmiede.de>
 * @package PluginName/Admin
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2
 * 

  Copyright 2013 (kontakt@rheinschmiede.de)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

if ( !defined( 'ABSPATH' ) ) exit;

class SurveyVal_ProcessResponse{
	var $response_errors = array();
	
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	public function __construct() {
		if( !is_admin() ):
			add_filter( 'the_content', array( $this, 'the_content' ) );
			add_action( 'init', array( $this, 'save_response_cookies' ) );
		endif;
	} // end constructor
	
	public function the_content( $content ){
		global $post, $surveyval_global;
		
		if( 'surveyval' != $post->post_type )
			return $content;
		
		$content = $this->get_survey( $post->ID );
		
		return $content;
	}
	
	public function get_survey( $survey_id ){
		global $surveyval_survey_id;
		
		$surveyval_survey_id = $survey_id;
		
		if( $this->has_participated( $survey_id ) ):
			return $this->text_already_participated();
		endif;
		
		return $this->get_survey_form( $survey_id );
	}
	
	public function get_survey_form( $survey_id ){
		
		if( array_key_exists( 'surveyval_next_step', $_POST ) && 0 == count( $this->response_errors ) ):
			$next_step = $_POST[ 'surveyval_next_step' ];
		else:
			$next_step = $_POST[ 'surveyval_actual_step' ];
		endif;
		
		if( array_key_exists( 'surveyval_submission_back', $_POST ) ):
			$next_step = $_POST[ 'surveyval_actual_step' ] - 1;
		endif;
		
		$actual_step = $next_step;
		
		$html = '<form name="surveyval" id="surveyval" action="' . $_SERVER[ 'REQUEST_URI' ] . '" method="POST">';
		
		/*
		if( is_array( $this->response_errors ) && count( $this->response_errors ) > 0 ):
			$html.=  '<div id="surveyval_errors" class="surveyval_errors">';
			foreach( $this->response_errors AS $error ):
				$html.= '<span>' . $error['message'] . '</span>';
			endforeach;
			$html.= '</div>';
		endif;
		*/
		
		$step_count = $this->get_step_count( $survey_id );
		
		$html.= '<div class="surveyval-description">' . sprintf( __( 'Step %d of %s', 'surveyval-locale' ), $actual_step + 1, $step_count + 1 ) . '</div>';
		
		$elements = $this->get_actual_step_elements( $survey_id, $actual_step );
		
		if( is_array( $elements ) && count( $elements ) > 0 ):
			foreach( $elements AS $element ):
				
				if( !$element->splitter ):
					if( FALSE != $this->element_error( $element ) ):
						$html.= '<div class="survey-element-error">';
					endif;
					
					// Standard element
					if( FALSE != $element->get_element() ):
						// If element has own get_element function
						$html.= $element->get_element( $element );
					else:
						// Use base get_element function
						$html.= $this->get_element( $element );
					endif;
					
					if( FALSE != $this->element_error( $element ) ):
						$html.= '<div class="surveyval_errors">' . $this->element_error( $element ) . '</div>';
						$html.= '</div>';
					endif;
				else:
					$next_step+=1;
					// If element is form splitter
					break;
				endif;
			endforeach;
		else:
			return FALSE;
		endif;
		
		if( 0 < $actual_step ):
			$html.= '<input type="submit" name="surveyval_submission_back" value="' . __( 'Back', 'surveyval-locale' ) . '"> ';
		endif;
		
		if( $actual_step == $next_step ):
			$html.= '<input type="submit" name="surveyval_submission" value="' . __( 'Finish Survey', 'surveyval-locale' ) . '">';
		else:
			$html.= '<input type="submit" name="surveyval_submission" value="' . __( 'Next Step', 'surveyval-locale' ) . '">';
		endif;
		
		$html.= '<input type="hidden" name="surveyval_next_step" value="' . $next_step . '" />';
		$html.= '<input type="hidden" name="surveyval_actual_step" value="' . $actual_step . '" />';
		$html.= '<input type="hidden" name="surveyval_id" value="' . $survey_id . '" />';
		
		$html.= '<pre>';
		$html.= print_r( $this->response_errors, TRUE );
		$html.= '</pre>';
		
		$html.= '</form>';
		
		return $html;
	}

	private function element_error( $element ){
		if( is_array( $this->response_errors ) && count( $this->response_errors ) > 0 ):
			foreach( $this->response_errors AS $error ):
				if( $error[ 'question_id' ] == $element->id )
					return $error[ 'message' ];			
			endforeach;
		endif;
		
		return FALSE;
	}

	private function get_step_count( $survey_id ){
		$survey = new SurveyVal_Survey( $survey_id );
		return $survey->splitter_count;
	}

	private function get_actual_step_elements( $survey_id, $step = 0 ){
		$survey = new SurveyVal_Survey( $survey_id );
		
		$actual_step = 0;
		
		$elements = array();
		foreach( $survey->elements AS $element ):
			$elements[ $actual_step ][] = $element;
			if( $element->splitter ):
				$actual_step++;
			endif;
		endforeach;
		
		if( $actual_step < $step )
			return FALSE;
		
		return $elements[ $step ];
	}

	public function get_element( $element ){
		global $surveyval_survey_id;
		
		$answer = '';
		
		if( !empty( $surveyval_survey_id ) ):
			if( isset( $_COOKIE[ 'surveyval_responses' ] ) ):
				$surveyval_responses = $_COOKIE[ 'surveyval_responses' ];
				$surveyval_responses = unserialize( stripslashes( $surveyval_responses ) );
				$response = $surveyval_responses[ $surveyval_survey_id ];
				$answer = $response[ $element->id ];
			endif;				
		endif;
		
		if( '' == $element->question && $element->is_question )
			return FALSE;
		
		if( 0 == count( $element->answers )  && $element->preset_of_answers == TRUE )
			return FALSE;
		
		$html = '<div class="survey-element survey-element-' . $element->id . '">';
		$html.= '<h5>' . $element->question . '</h5>';
		
		if( !$element->preset_of_answers ):
			/*
			 * On simple input
			 */
			$param_arr = array();
			$param_arr[] = $element->answer_syntax;
				
			foreach( $element->answer_params AS $param ):
				switch( $param ){
					case 'name':
						$param_value = 'surveyval_response[' . $element->id . ']';
						break;
						
					case 'value':
						$param_value = $answer;
						break;
						
					case 'answer';
						$param_value = $answer;
						break;
				}
				$param_arr[] = $param_value;			
			endforeach;
			
			$html.= '<div class="answer">';
			$html = apply_filters( 'surveyval_before_answer_' . $element->slug, $html, $element->slug, $element->id );
			$html.= call_user_func_array( 'sprintf', $param_arr );
			$html = apply_filters( 'surveyval_after_answer_' . $element->slug, $html, $element->slug, $element->id );
			$html.= '</div>';
			
		else:
			/*
			 * With preset of answers
			 */
			foreach( $element->answers AS $answer ):
				$param_arr = array();
				
				// Is answer selected choose right syntax
				if( $element->answer_is_multiple ):
					
					if( is_array( $element->response ) && in_array( $answer['text'], $element->response ) ):
						$param_arr[] = $element->answer_selected_syntax;
					else:
						$param_arr[] = $element->answer_syntax;
					endif;
					
				else:
					
					if( $element->response == $answer['text'] && !empty( $element->answer_selected_syntax ) ):
						$param_arr[] = $element->answer_selected_syntax;
					else:
						$param_arr[] = $element->answer_syntax;
					endif;
					
				endif;
				
				// Running every parameter for later calling
				foreach( $element->answer_params AS $param ):
					switch( $param ){
						
						case 'name':
							if( $element->answer_is_multiple )
								$param_value = 'surveyval_response[' . $element->id . '][]';
							else
								$param_value = 'surveyval_response[' . $element->id . ']';
								
							break;
							
						case 'value':
							$param_value = $answer;
							break;
							
						case 'answer';
							$param_value = $answer;
							break;
					}
					$param_arr[] = $param_value;			
				endforeach;
				
				$html.= '<div class="answer">';
				$html = apply_filters( 'surveyval_before_answer', $html, $element->slug, $element->id );
				$html.= call_user_func_array( 'sprintf', $param_arr );
				$html = apply_filters( 'surveyval_after_answer', $html, $element->slug, $element->id );
				$html.= '</div>';
					
				// $html.= '<pre>' . print_r( $answer, TRUE ) . '</pre>';
				// $html.= sprintf( $this->answer_syntax, $answer, $this->slug );
			endforeach;
		endif;
		
		$html.= '</div>';
		
		return $html;
	}

	public function save_response_cookies(){
		$response = array();
		
		if( !array_key_exists( 'surveyval_response', $_POST ) ):
			return;
		endif;
		
		$survey_id = $_POST[ 'surveyval_id' ];
		$posted_values = $_POST[ 'surveyval_response' ];
		$surveyval_responses = array();
		$response = array();
		
		if( isset( $_COOKIE[ 'surveyval_responses' ] ) ):
			$surveyval_responses = $_COOKIE[ 'surveyval_responses' ];
			$surveyval_responses = unserialize( stripslashes( $surveyval_responses ) );
			$response = $surveyval_responses[ $survey_id ];
		endif;
		
		if( is_array( $posted_values ) ):
			foreach( $posted_values AS $key =>$value ):
				$response[ $key ] = $value;
			endforeach;
		endif;

		$surveyval_responses[ $survey_id ] = $response; 
		$surveyval_responses = serialize( $surveyval_responses );
		
		$this->validate_response();
		
		setcookie( 'surveyval_responses', $surveyval_responses, time() + 1200 ); // Save cookie for six hours
		$_COOKIE[ 'surveyval_responses' ] = $surveyval_responses;
	}
	
	public function validate_response( $survey_id = NULL, $responses = NULL ){
		if( array_key_exists( 'surveyval_submission_back', $_POST ) )
			return FALSE;
		
		if( empty( $responses ) )
			$responses = $_POST[ 'surveyval_response' ];
		
		if( empty( $responses ) )
			return FALSE;
		
		if( empty( $survey_id ) )
			$survey_id = $_POST[ 'surveyval_id' ];
		
		if( empty( $survey_id ) )
			return FALSE;
		
		$survey = new SurveyVal_Survey( $survey_id );
		
		// Are there any elements?
		if( is_array( $responses ) && count( $responses ) > 0 ):
			
			// Running thru all answers
			foreach( $responses AS $element_id => $response ):

				$element = $this->get_survey_element_by_id( $survey, $element_id );
				
				/*
				 * Validating Answer
				 */
				if( !$element->validate( $response ) ):
					// Gettign every error of question back
					foreach( $element->validate_errors AS $error ):
						$this->response_errors[] = array(
							'message' => $error,
							'question_id' =>  $element->id
						);
					endforeach;
					$answer_error = TRUE;
				endif;
				
				
				/*
				// Checking if question have been answered
				if( !array_key_exists( $element->id, $response ) ):
					$this->response_errors[] = array(
						'message' => sprintf( __( 'You missed to answer question "%s"!', 'surveyval-locale' ), $element->question ),
						'question_id' =>  $question->id
					);
					$this->elements[ $key ]->error = TRUE;
					$answer_error = TRUE;
				endif;
				
				$answer = '';
				
				if( array_key_exists( $element->id, $response ) )
					$answer = $response[ $element->id ];
				
				// Taking response
				$this->elements[ $key ]->response = $answer;
				
				// Validating answer with custom validation
				if( !$element->validate( $answer ) ):
					
					// Gettign every error of question back
					foreach( $question->validate_errors AS $error ):
						$this->response_errors[] = array(
							'message' => $error,
							'question_id' =>  $question->id
						);
					endforeach;
					
					$this->elements[ $key ]->error = TRUE;
					$answer_error = TRUE;
					
				endif;
				*/
			endforeach;
			
		else:
			$this->response_errors[] = array(
				'message' => __( 'There are no elements to save in survey', 'surveyval-locale' ),
				'question_id' =>  0
			);
			$answer_error = TRUE;
		endif;
	}

	private function get_survey_element_by_id( $survey, $element_id ){
		foreach( $survey->elements AS $element ):
			if( $element->id == $element_id ):
				return $element;
			endif;
		endforeach;
		
		return FALSE;
	}
	
	public function save_response( $survey_id ){
		global $wpdb, $surveyval_global, $current_user;
		
		$response = $_POST['surveyval_response'];
		$this->response_errors = array();
		$answer_error = FALSE;
		
		if( $this->has_participated( $survey_id ) ):
			$this->response_errors[] = array(
				'message' => sprintf( __( 'You already have participated this poll!', 'surveyval-locale' ), $question->question )
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
	}

	

	public function has_participated( $surveyval_id, $user_id = NULL ){
		global $wpdb, $current_user, $surveyval_global;
		
		// Setting up user ID
		if( NULL == $user_id ):
			get_currentuserinfo();
			$user_id = $user_id = $current_user->ID;
		endif;
				
		// Setting up Survey ID
		if( NULL == $surveyval_id )
			return FALSE;
		
		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$surveyval_global->tables->responds} WHERE surveyval_id=%d AND user_id=%s", $surveyval_id, $user_id );
		$count = $wpdb->get_var( $sql );
		
		if( 0 == $count ):
			return FALSE;
		else:
			return TRUE;
		endif;
	}
	
	public function text_thankyou_for_participation(){
		$html = '<div id="surveyval-thank-participation">';
		$html.= __( 'Thank you for participating this survey!', 'surveyval-locale' );
		$html.= '</div>';
		return $html;
	}
	
	public function text_already_participated(){
		$html = '<div id="surveyval-already-participated">';
		$html.= __( 'You already have participated this poll!', 'surveyval-locale' );
		$html.= '</div>';
		return $html;
	}
	
}
$SurveyVal_ProcessResponse = new SurveyVal_ProcessResponse();

function sv_user_has_participated( $surveyval_id, $user_id = NULL){
	$response = new SurveyVal_ProcessResponse();
	return $response->has_participated( $surveyval_id, $user_id );
}


