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
		else:
			add_action( 'save_post', array( $this, 'save_survey' ), 50 );
			add_action( 'delete_post', array( $this, 'delete_survey' ) );
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
			foreach( $survey->elements AS $question ):
				if( !$question->splitter ):
					$html.= $question->get_html();
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

	public function save_survey( $post_id ){
		if ( wp_is_post_revision( $post_id ) )
			return;
		
		if( !array_key_exists( 'post_type', $_POST ) )
			return;
		
		if ( 'surveyval' != $_POST['post_type'] )
			return;
		
		$this->save_survey_postdata( $post_id );
		
		// Preventing dublicate saving
		remove_action( 'save_post', array( $this, 'save_survey' ), 50 );
	}

	public function save_survey_postdata( $post_id ){
		global $surveyval_global, $wpdb;
		
		$survey_elements = $_POST['surveyval'];
		$survey_deleted_surveyelements = $_POST['surveyval_deleted_surveyelements'];
		$survey_deleted_answers = $_POST['surveyval_deleted_answers'];
		
		// mail( 'sven@deinhilden.de', 'Test', print_r( $_POST, TRUE ) . print_r( $surveyval_global, TRUE ) );
		
		$survey_deleted_surveyelements = explode( ',', $survey_deleted_surveyelements );
		
		/*
		 * Deleting deleted answers
		 */
		if( is_array( $survey_deleted_surveyelements ) && count( $survey_deleted_surveyelements ) > 0 ):
			foreach( $survey_deleted_surveyelements AS $deleted_question ):
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
		 * Saving elements
		 */
		foreach( $survey_elements AS $key => $survey_question ):
			if( 'widget_surveyelement_##nr##' == $key )
				continue;
			
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

				// Adding new question
				$wpdb->insert(
					$surveyval_global->tables->questions,
					array(
						'surveyval_id' => $post_id,
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

	public function delete_survey( $post_id ){
		global $wpdb, $surveyval_global;
		
		$sql = $wpdb->prepare( "SELECT id FROM {$surveyval_global->tables->questions} WHERE surveyval_id=%d", $post_id );
		
		$elements = $wpdb->get_col( $sql );
		
		$wpdb->delete( 
			$surveyval_global->tables->questions, 
			array( 'surveyval_id' => $post_id ) 
		);
		
		if( is_array( $elements ) && count( $elements ) > 0 ):
			foreach( $elements AS $question ):
				$wpdb->delete( 
					$surveyval_global->tables->answers,
					array( 'question_id' => $question ) 
				);
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


