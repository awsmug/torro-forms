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

global $SurveyVal_ProcessResponse;

class SurveyVal_ProcessResponse{
	var $survey_id;
	var $response_errors = array();
	var $finished = FALSE;
	var $finished_id;
	
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	public function __construct() {
		if( !is_admin() ):
			add_action( 'init', array( $this, 'save_response_cookies' ) );
			add_action( 'the_post', array( $this, 'load_post_filter' ) ); // Just hooking in at the beginning of a loop
		endif;
	} // end constructor
	
	public function load_post_filter(){
		add_filter( 'the_content', array( $this, 'the_content' ) );
	}
	
	public function the_content( $content ){
		global $post, $surveyval_global;
		
		if( 'surveyval' != $post->post_type )
			return $content;
		
		$content = $this->get_survey( $post->ID );
		
		remove_filter( 'the_content', array( $this, 'the_content' ) ); // only show once		
		
		return $content;
	}
	
	public function get_survey( $survey_id ){
		global $surveyval_survey_id, $current_user;
		
		$surveyval_survey_id = $survey_id;
		
		/*
		 * Checks on starting surveys
		 */
		 
		// If user is not logged in
		if( !is_user_logged_in() ):
			return $this->text_not_logged_in();
		endif;
		
		// If user user has finished successfull
		if( $this->finished && $this->finished_id == $survey_id ):
			
			global $post;
			get_currentuserinfo();
			
			$subject_template = sv_get_mail_template_subject( 'thankyou_participating' );
			
			$subject = str_replace( '%displayname%', $current_user->display_name, $subject_template );
			$subject = str_replace( '%username%', $current_user->user_nicename, $subject );
			$subject = str_replace( '%site_name%', get_bloginfo( 'name' ), $subject );
			$subject = str_replace( '%survey_title%', $post->post_title, $subject );
			
			$text_template = sv_get_mail_template_text( 'thankyou_participating' );
			
			$content = str_replace( '%displayname%', $current_user->display_name, $text_template );
			$content = str_replace( '%username%', $current_user->user_nicename, $content );
			$content = str_replace( '%site_name%', get_bloginfo( 'name' ), $content );
			$content = str_replace( '%survey_title%', $post->post_title, $content );
			
			sv_mail( $current_user->user_email, $subject, $content );
			
			return $this->text_thankyou_for_participation();
		endif;
		
		// If user has already participated
		if( $this->has_participated( $survey_id ) ):
			return $this->text_already_participated();
		endif;
		
		// If user can't participate the poll
		if( !$this->user_can_participate( $survey_id ) ):
			return $this->text_cant_participate();
		endif;
		
		return $this->get_survey_form( $survey_id );
	}
	
	public function user_can_participate( $survey_id, $user_id = NULL ){
		global $wpdb, $current_user;
		
		$can_participate = FALSE;
		
		// Setting up user ID
		if( NULL == $user_id ):
			get_currentuserinfo();
			$user_id = $user_id = $current_user->ID;
		endif;
		
		$can_participate = TRUE;
		
		return apply_filters( 'surveyval_user_can_participate', $can_participate, $survey_id, $user_id );
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
		
		if( empty( $next_step ) )
			$next_step = 0;
		
		$actual_step = $next_step;
		
		$html = '<form name="surveyval" id="surveyval" action="' . $_SERVER[ 'REQUEST_URI' ] . '" method="POST">';
		
		$step_count = $this->get_step_count( $survey_id );
		
		$html.= '<div class="surveyval-description">' . sprintf( __( 'Step <span class="surveyval-highlight-number">%d</span> of <span class="surveyval-highlight-number">%s</span>', 'surveyval-locale' ), $actual_step + 1, $step_count + 1 ) . '</div>';
		
		$elements = $this->get_actual_step_elements( $survey_id, $actual_step );
		
		if( is_array( $elements ) && count( $elements ) > 0 ):
			foreach( $elements AS $element ):
				
				if( !$element->splitter ):
					
					if( FALSE != $this->element_error( $element ) ):
						$html.= '<div class="surveyval-element-error">';
						$html.= '<div class="surveyval-element-error-message">' . $this->element_error( $element ) . '</div>';
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
			$html.= '<input type="submit" name="surveyval_submission_back" value="' . __( 'Previous Step', 'surveyval-locale' ) . '"> ';
		endif;
		
		if( $actual_step == $next_step ):
			$html.= '<input type="submit" name="surveyval_submission" value="' . __( 'Finish Survey', 'surveyval-locale' ) . '">';
		else:
			$html.= '<input type="submit" name="surveyval_submission" value="' . __( 'Next Step', 'surveyval-locale' ) . '">';
		endif;
		
		$html.= '<input type="hidden" name="surveyval_next_step" value="' . $next_step . '" />';
		$html.= '<input type="hidden" name="surveyval_actual_step" value="' . $actual_step . '" />';
		$html.= '<input type="hidden" name="surveyval_id" value="' . $survey_id . '" />';
		
		$html.= '</form>';
		
		return $html;
	}

	private function element_error( $element ){
		$messages = array();
		
		$found_error = FALSE;
		
		if( is_array( $this->response_errors ) && count( $this->response_errors ) > 0 ):
			foreach( $this->response_errors AS $error ):
				if( $error[ 'question_id' ] == $element->id ):
					$messages[] = $error[ 'message' ];
					$found_error = TRUE;
				endif;
			endforeach;
			
			if( FALSE == $found_error )
				return FALSE;
			
			$text = '<ul class="surveyval-error-messages">';
		
			foreach( $messages AS $message ):
				$text.= '<li>' . $message . '</li>';
			endforeach;
			
			$text.= '</ul>';
			
			return $text;
			
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
		$html.= $element->before_question();
		$html.= '<h5>' . $element->question . '</h5>';
		$html.= $element->after_question();
		
		if( FALSE != $element->show() ):
			// Using own method to show 
			$html.= $element->show( $answer );
		else:
			// Using standard functions to show element
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
				$html.= $element->before_answers();
				$html.= $element->before_answer();
				$html.= call_user_func_array( 'sprintf', $param_arr );
				$html.= $element->after_answer();
				$html.= $element->after_answers();
				$html.= '</div>';
				
			else:
				/*
				 * With preset of answers
				 */
				 
				 $html.= $element->before_answers();
				 
				foreach( $element->answers AS $answer ):
					$param_arr = array();
					
					// Is answer selected choose right syntax
					if( $element->answer_is_multiple ):
						
						if( is_array( $response[ $element->id ] ) && in_array( $answer['text'], $response[ $element->id ] ) ):
							$param_arr[] = $element->answer_selected_syntax;
						else:
							$param_arr[] = $element->answer_syntax;
						endif;
						
					else:
						
						if( $response[ $element->id ] == $answer['text'] && !empty( $element->answer_selected_syntax ) ):
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
								$param_value = $answer['text'];
								break;
								
							case 'answer';
								$param_value = $answer['text'];
								break;
						}
						$param_arr[] = $param_value;			
					endforeach;
					//$html.= '<div class="answer">';
					$html.= $element->before_answer();
					$html.= call_user_func_array( 'sprintf', $param_arr );
					$html.= $element->after_answer();
					//$html.= '<pre>' . print_r( $param_arr, TRUE ) . '</pre>';
					//$html.= '</div>';
				endforeach;
				
				$html.= $element->after_answers();
				
			endif;
		endif;
		
		$html.= '</div>';
		
		return $html;
	}

	public function save_response_cookies(){
		$response = array();
		$this->finished = FALSE;
		
		if( !array_key_exists( 'surveyval_response', $_POST ) ):
			return;
		endif;
		
		$survey_id = $_POST[ 'surveyval_id' ];
		$posted_values = $_POST[ 'surveyval_response' ];
		$step = $_POST[ 'surveyval_actual_step' ];
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
		
		$this->validate_response( $survey_id, $response, $step );
		
		setcookie( 'surveyval_responses', $surveyval_responses, time() + 1200 ); // Save cookie for six hours
		$_COOKIE[ 'surveyval_responses' ] = $surveyval_responses;
		
		if( $_POST[ 'surveyval_actual_step' ] == $_POST[ 'surveyval_next_step' ]  && 0 == count( $this->response_errors ) && !array_key_exists( 'surveyval_submission_back', $_POST ) ):
			
			$response = unserialize( stripslashes( $_COOKIE['surveyval_responses'] ) );
			if( $this->save_response( $survey_id, $response[ $survey_id ] ) ):
				// Unsetting cookie, because not needed anymore
				unset( $_COOKIE['surveyval_responses'] );
				setcookie( 'surveyval_responses', null, -1, '/' );
				$this->finished = TRUE;
				$this->finished_id = $survey_id;
			endif;
		endif;
	}
	
	public function validate_response( $survey_id = NULL, $responses = NULL, $step = 0 ){
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
		
		$elements = $this->get_actual_step_elements( $survey_id, $step );
		
		if( is_array( $elements ) && count( $elements ) > 0 ):
			// Running thru all elements
			foreach( $elements AS $element ):
				if( array_key_exists( $element->id, $responses ) && !$element->splitter ):
					$response = $responses[ $element->id ];
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
				elseif( !$element->splitter && $element->is_question ):
					$this->response_errors[] = array(
						'message' => __( 'You did´t answered this question.', 'surveyval-locale' ),
						'question_id' =>  $element->id
					);
				endif;
			endforeach;
		else:
			$this->response_errors[] = array(
				'message' => __( 'There are no elements to save in survey', 'surveyval-locale' ),
				'question_id' =>  0
			);
			$answer_error = TRUE;
		endif;
		
		if( is_array( $this->response_errors ) && count( $this->response_errors ) == 0 ):
			return TRUE;
		else:
			return FALSE;
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
	
	public function save_response( $survey_id, $response ){
		global $wpdb, $surveyval_global, $current_user;
		
		get_currentuserinfo();
		$user_id = $user_id = $current_user->ID;
		
		// Adding new question
		$wpdb->insert(
			$surveyval_global->tables->responds,
			array(
				'surveyval_id' => $survey_id,
				'user_id' => $user_id,
				'timestamp' => time()  )
		);
		
		$respond_id = $wpdb->insert_id;
		
		foreach( $response AS $element_id => $answers ):
			
			if( is_array( $answers ) ):
				
				foreach( $answers AS $answer ):
					$wpdb->insert(
						$surveyval_global->tables->respond_answers,
						array(
							'respond_id' => $respond_id,
							'question_id' => $element_id,
							'value' => $answer
						)
					);
				endforeach;
				
			else:
				$answer = $answers;
				
				$wpdb->insert(
					$surveyval_global->tables->respond_answers,
					array(
						'respond_id' => $respond_id,
						'question_id' => $element_id,
						'value' => $answer
					)
				);
				
			endif;
		endforeach;
		
		return TRUE;
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
		$html.= __( 'You already have participated this poll.', 'surveyval-locale' );
		$html.= '</div>';
		return $html;
	}
	
	public function text_not_logged_in(){
		$html = '<div id="surveyval-not-logged-in">';
		$html.= __( 'You have to be logged in to participate this survey.', 'surveyval-locale' );
		$html.= '</div>';
		return $html;
	}
	
	public function text_cant_participate(){
		$html = '<div id="surveyval-cant-participate">';
		$html.= __( 'You can\'t participate this survey.', 'surveyval-locale' );
		$html.= '</div>';
		return $html;
	}
	
}
$SurveyVal_ProcessResponse = new SurveyVal_ProcessResponse();

function sv_user_has_participated( $surveyval_id, $user_id = NULL){
	global $SurveyVal_ProcessResponse;
	return $SurveyVal_ProcessResponse->has_participated( $surveyval_id, $user_id );
}


