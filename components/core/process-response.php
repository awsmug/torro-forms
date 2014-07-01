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
	var $respond_id;
	
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	public function __construct() {
		if( !is_admin() ):
			add_action( 'parse_request', array( $this, 'process_response' ), 99  );
			add_action( 'the_post', array( $this, 'add_post_filter' ) ); // Just hooking in at the beginning of a loop
		endif;
	} // end constructor
	
	public function add_post_filter(){
		add_filter( 'the_content', array( $this, 'the_content' ) );
	}
	
	public function the_content( $content ){
		global $post, $surveyval_global;
		
		if( 'surveyval' != $post->post_type )
			return $content;
		
		$content = $this->survey( $post->ID );
		
		remove_filter( 'the_content', array( $this, 'the_content' ) ); // only show once		
		
		return $content;
	}
	
	public function survey( $survey_id ){
		global $surveyval_survey_id;
		
		// If user is not logged in
		if( !is_user_logged_in() ):
			return $this->text_not_logged_in();
		endif;
		
		// If user user has finished successfull
		if( $this->finished && $this->finished_id == $survey_id ):
			$this->email_finished();
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
		
		return $this->survey_form( $survey_id );
	}
	
	private function survey_form( $survey_id ){
		global $surveyval_response_errors, $surveyval_survey_id;
		$surveyval_survey_id = $survey_id;
		
		do_action( 'before_survey_form' );
		
		if( array_key_exists( 'surveyval_next_step', $_POST ) && 0 == count( $surveyval_response_errors ) ):
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
		
		$elements = $this->get_elements( $survey_id, $actual_step );
		
		if( is_array( $elements ) && count( $elements ) > 0 ):
			foreach( $elements AS $element ):
				if( !$element->splitter ):
					$html.= $element->draw();
				else:
					$next_step+=1;
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

	public function user_can_participate( $survey_id, $user_id = NULL ){
		global $wpdb, $current_user;
		
		// Setting up user ID
		if( NULL == $user_id ):
			get_currentuserinfo();
			$user_id = $user_id = $current_user->ID;
		endif;
		
		$can_participate = TRUE;
		
		return apply_filters( 'surveyval_user_can_participate', $can_participate, $survey_id, $user_id );
	}

	private function get_step_count( $survey_id ){
		$survey = new SurveyVal_Survey( $survey_id );
		return $survey->splitter_count;
	}

	public function get_elements( $survey_id, $step = 0 ){
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

	public function process_response( $wp_object ){
		global $wpdb, $post, $surveyval_global, $surveyval_survey_id;
		
		// Survey ID was posted or die
		if( !array_key_exists( 'surveyval_id', $_POST ) )
		 	return;
		
		// Post Type is surveyval or die
		if( 'surveyval' != $wp_object->query_vars[ 'post_type' ] )
			return;
		
		// Getting Survey id from post or die
		if( array_key_exists( 'name', $wp_object->query_vars) ):
			$sql = $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE post_name = %s AND post_type='surveyval'", $wp_object->query_vars[ 'name' ] );
			$surveyval_survey_id = $wpdb->get_var( $sql );
		elseif( array_key_exists( 'p', $wp_object->query_vars) ):
			$surveyval_survey_id = $wp_object->query_vars[ 'p' ];
		else:
			return;
		endif;
		
		// User has not participated or die
		if( $this->has_participated( $surveyval_survey_id ) )
			return;
		
		// Getting Session Data
		if( !isset( $_SESSION ) )
			session_start();
		
		// If session has data, get it!
		if( isset( $_SESSION[ 'surveyval_response' ] ) )
			$saved_response = $_SESSION[ 'surveyval_response' ][ $surveyval_survey_id ];
		
		do_action( 'surveyval_before_process_response', $_POST );
		
		$response = array();
		$this->finished = FALSE;
		
		// Getting data of posted step
		$survey_response = $_POST[ 'surveyval_response' ];
		$survey_actual_step = $_POST[ 'surveyval_actual_step' ];
		
		// Validating response values and setting up error variables
		$this->validate_response( $surveyval_survey_id, $survey_response, $survey_actual_step );
		
		// Adding / merging Values to response var
		if( isset( $saved_response ) ):
			
			// Replacing old values by key
			if( is_array( $survey_response ) && count( $survey_response ) > 0 ):
				foreach( $survey_response AS $key => $answer ):
					$saved_response[ $key ] = $answer;
				endforeach;
			endif;
			
			$response = $saved_response;
		else:
			$response = $survey_response;
		endif;
		
		$response = apply_filters( 'surveyval_process_response', $response );
		
		// Storing values in Session
		$_SESSION[ 'surveyval_response' ][ $surveyval_survey_id ] = $response;
		
		$this->save_response();
		
		do_action( 'surveyval_after_process_response', $_POST );
	}

	private function save_response(){
		global $surveyval_response_errors, $surveyval_survey_id;
		
		do_action( 'surveyval_before_save_response' );
		
		if( !isset( $_SESSION[ 'surveyval_response' ][ $surveyval_survey_id ] ) )
			return;
		
		if( $_POST[ 'surveyval_actual_step' ] == $_POST[ 'surveyval_next_step' ]  && 0 == count( $surveyval_response_errors ) && !array_key_exists( 'surveyval_submission_back', $_POST ) ):
			$response = $_SESSION[ 'surveyval_response' ][ $surveyval_survey_id ];
			
			if( $this->save_data( $surveyval_survey_id, apply_filters( 'surveyval_save_response', $response ) ) ):
				do_action( 'surveyval_after_save_response' );
				
				if( $_SERVER['REMOTE_ADDR'] == '178.7.111.101' || $_SERVER[ 'REMOTE_ADDR' ] == '217.85.114.181' ):
					echo 'Session Data destroyed and data saved';
				endif;
				
				// Unsetting Session, because not needed anymore
				session_destroy();	
				unset( $_SESSION['surveyval_response'] );
				
				$this->finished = TRUE;
				$this->finished_id = $surveyval_survey_id;
			endif;
		endif;
	}
	
	public function validate_response( $survey_id, $response, $step ){
		global $surveyval_response_errors;
		
		if( array_key_exists( 'surveyval_submission_back', $_POST ) )
			return FALSE;
		
		if( empty( $survey_id ) )
			return;
		
		if( empty( $step ) && (int) $step != 0 )
			return;
		
		$elements = $this->get_elements( $survey_id, $step );
		
		if( !is_array( $elements ) && count( $elements ) == 0 )
			return;
		
		if( empty( $surveyval_response_errors ) )
			$surveyval_response_errors = array();
		
		// Running thru all elements
		foreach( $elements AS $element ):
			if( $element->splitter )
				continue;
			
			$skip_validating = apply_filters( 'surveyval_skip_validating', FALSE, $element );
			
			if( $skip_validating )
				continue;

			$answer = $response[ $element->id ];
			
			if( !$element->validate( $answer ) ):
				
				if( empty( $surveyval_response_errors[ $element->id ] ) )
					$surveyval_response_errors[ $element->id ] = array();
				
				// Gettign every error of question back
				foreach( $element->validate_errors AS $error ):
					$surveyval_response_errors[ $element->id ][] = $error;
				endforeach;
				
			endif;
		endforeach;
		
		// ??? One Element at the end ???
		if( is_array( $surveyval_response_errors[ $element->id ] ) && count( $surveyval_response_errors[ $element->id ] ) == 0 ):
			return TRUE;
		else:
			return FALSE;
		endif;
	}

	private function save_data( $survey_id, $response ){
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
		
		do_action( 'surveyval_save_data', $survey_id, $response );
		
		$respond_id = $wpdb->insert_id;
		$this->respond_id = $respond_id;
		
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
	
	public function email_finished(){
		global $post, $current_user;
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
	}
	
	public function text_thankyou_for_participation(){
		$html = '<div id="surveyval-thank-participation">';
		$html.= __( 'Thank you for participating this survey!', 'surveyval-locale' );
		$html.= '<input name="response_id" id="response_id" type="hidden" value="' . $this->respond_id . '" />';
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


