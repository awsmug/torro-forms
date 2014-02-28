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
		$actual_step = 1;
		
		$survey = new SurveyVal_Survey( $survey_id );
		
		$html = '<form name="surveyval" id="surveyval" action="' . $_SERVER[ 'REQUEST_URI' ] . '" method="POST">';
		
		if( is_array( $this->response_errors ) && count( $this->response_errors ) > 0 ):
			echo '<div id="surveyval_errors" class="surveyval_errors">';
			foreach( $this->response_errors AS $error ):
				echo '<span>' . $error['message'] . '</span>';
			endforeach;
			echo '</div>';
		endif;

		if( is_array( $survey->elements ) && count( $survey->elements ) > 0 ):
			foreach( $survey->elements AS $element ):
				if( !$element->splitter ):
					$html.= $this->get_element( $element );
				else:
					break;
				endif;
			endforeach;
		else:
			return FALSE;
		endif;
		
		$html.= '<input type="submit" name="surveyval_submission" value="' . __( 'Send your answers', 'surveyval-locale' ) . '">';
		$html.= '<input type="hidden" name="surveyval_step" value="' . $actual_step . '" />';
		$html.= '<input type="hidden" name="surveyval_id" value="' . $survey_id . '" />';
		
		$html.= '</form>';
		
		return $html;
	}

	public function get_element( $element ){
		global $surveyval_survey_id;
		
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
		
		$error_css = '';
		
		if( $this->error )
			$error_css = ' survey-element-error';
		
		$html = '<div class="survey-element survey-element-' . $element->id . $error_css . '">';
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
		
		setcookie( 'surveyval_responses', $surveyval_responses, time() + 1200 ); // Save cookie for six hours
		$_COOKIE[ 'surveyval_responses' ] = $surveyval_responses;
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
		
		// Are there any elements?
		if( is_array( $this->elements ) && count( $this->elements ) > 0 ):
			
			// Running thru all answers
			foreach( $this->elements AS $key => $question ):
				
				// Checking if question have been answered
				if( !array_key_exists( $question->id, $response ) ):
					$this->response_errors[] = array(
						'message' => sprintf( __( 'You missed to answer question "%s"!', 'surveyval-locale' ), $question->question ),
						'question_id' =>  $question->id
					);
					$this->elements[ $key ]->error = TRUE;
					$answer_error = TRUE;
				endif;
				
				$answer = '';
				
				if( array_key_exists( $question->id, $response) )
					$answer = $response[ $question->id ];
				
				// Taking response
				$this->elements[ $key ]->response = $answer;
				
				// Validating answer with custom validation
				if( !$question->validate( $answer ) ):
					
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
				
			endforeach;
			
		else:
			$this->response_errors[] = array(
				'message' => __( 'There are no elements to save in survey', 'surveyval-locale' ),
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


