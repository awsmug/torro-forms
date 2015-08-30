<?php
/**
 * Processing form
 *
 * This class processes the submitted form.
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Core
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if( !defined( 'ABSPATH' ) ){
	exit;
}

class AF_FormProcess
{

	/**
	 * ID of processed form
	 */
	var $form_id;

	/**
	 * Form object
	 */
	var $form;

	/**
	 * Action URL
	 */
	var $action_url;

	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $form_id, $action_url = NULL )
	{
		$this->form_id = $form_id;
		$this->form = new AF_Form( $this->form_id );

		if( NULL == $action_url ){
			$this->action_url = $_SERVER[ 'REQUEST_URI' ];
		}else{
			$this->action_url = $action_url;
		}
	}

	/**
	 * Survey form
	 *
	 * Creating form HTML
	 *
	 * @param int $form_id
	 * @return string $html
	 * @since 1.0.0
	 */
	public function show_form()
	{
		global $ar_form_id;

		$show_form = apply_filters( 'questions_show_form', TRUE ); // Hook for adding restrictions and so on ...

		if( FALSE == $show_form ){
			return;
		}

		if( !isset( $_SESSION ) ){
			session_start();
		}

		$html = '';

		// Set global message on top of page
		if( !empty( $af_response_errors ) ){
			$html .= '<div class="af-element-error">';
			$html .= '<div class="af-element-error-message"><p>';
			$html .= esc_attr__( 'There are open answers', 'af-locale' );
			$html .= '</p></div></div>';
		}

		// Getting actual step for form
		$actual_step = $this->get_actual_step();

		$html .= '<form name="questions" id="questions" action="' . $this->action_url . '" method="POST">';
		$html .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'questions-' . $this->form_id ) . '" />';

		ob_start();
		do_action( 'questions_form_start' );
		$html .= ob_get_clean();

		$step_count = $this->form->get_step_count();

		// Switch on navigation if there is more than one page
		if( 0 != $step_count ){
			$html .= '<div class="af-pagination">' . sprintf( __( 'Step <span class="af-highlight-number">%d</span> of <span class="af-highlight-number">%s</span>', 'af-locale' ), $actual_step + 1, $step_count + 1 ) . '</div>';
		}

		// Getting all elements of step and running them
		$elements = $this->form->get_step_elements( $actual_step );
		$next_step = $actual_step;

		if( is_array( $elements ) && count( $elements ) > 0 ):
			foreach( $elements AS $element ):
				if( !$element->splits_form ):
					$html .= $element->draw();
				else:
					$next_step += 1; // If there is a next step, setting up next step var
					break;
				endif;
			endforeach;
		else:
			return FALSE;
		endif;

		$html .= $this->get_navigation( $actual_step, $next_step );

		ob_start();
		do_action( 'questions_form_end' );
		$html .= ob_get_clean();

		$html .= '<input type="hidden" name="af_next_step" value="' . $next_step . '" />';
		$html .= '<input type="hidden" name="af_actual_step" value="' . $actual_step . '" />';
		$html .= '<input type="hidden" name="questions_form_id" value="' . $this->form_id . '" />';

		$html .= '</form>';

		return $html;
	}

	/**
	 * Processing entered data
	 *
	 * @since 1.0.0
	 */
	public function process_response()
	{
		global $ar_form_id, $af_response_errors;

		if( !wp_verify_nonce( $_POST[ '_wpnonce' ], 'questions-' . $ar_form_id ) ){
			return;
		}

		$response = array();
		if( isset( $_POST[ 'af_response' ] ) ){
			$response = $_POST[ 'af_response' ];
		}

		$actual_step = (int) $_POST[ 'af_actual_step' ];

		// If there was a saved response
		if( isset( $_SESSION[ 'af_response' ][ $ar_form_id ] ) ){

			// Merging data
			$merged_response = $_SESSION[ 'af_response' ][ $ar_form_id ];
			if( is_array( $response ) && count( $response ) > 0 ){
				foreach( $response AS $key => $answer )
					$merged_response[ $key ] = af_prepare_post_data( $answer );
			}

			$_SESSION[ 'af_response' ][ $ar_form_id ] = $merged_response;
		}else{
			$merged_response = $response;
		}

		$_SESSION[ 'af_response' ][ $ar_form_id ] = $merged_response;

		// Validate submitted data if user not has gone backwards
		if( !array_key_exists( 'af_submission_back', $_POST ) ){
			$this->validate( $ar_form_id, $_SESSION[ 'af_response' ][ $ar_form_id ], $actual_step );
		} // Validating response values and setting up error variables

		// If form is finished and user don't have been gone backwards, save data
		if( (int) $_POST[ 'af_actual_step' ] == (int) $_POST[ 'af_next_step' ] && 0 == count( $af_response_errors ) && !isset( $_POST[ 'af_submission_back' ] ) ){

			$form = new AF_Form( $ar_form_id );
			$response_id = $form->save_response( $_SESSION[ 'af_response' ][ $ar_form_id ] );

			if( FALSE != $response_id ){
				do_action( 'af_save_response', $response_id );

				unset( $_SESSION[ 'af_response' ][ $ar_form_id ] );
				$_SESSION[ 'af_response' ][ $ar_form_id ][ 'finished' ] = TRUE;

				header( 'Location: ' . $_SERVER[ 'REQUEST_URI' ] );
				die();
			}
		}

		do_action( 'questions_process_response_end' );
	}

	/**
	 * Validating response
	 *
	 * @param int   $form_id
	 * @param array $response
	 * @param int   $step
	 *
	 * @return boolean $validated
	 * @since 1.0.0
	 */
	public function validate( $form_id, $response, $step )
	{
		global $af_response_errors;

		$elements = $this->form->get_step_elements( $step );
		if( !is_array( $elements ) && count( $elements ) == 0 ){
			return;
		}

		$af_response_errors = array();

		// Running true all elements
		foreach( $elements AS $element ):
			if( $element->splits_form ){
				continue;
			}

			$answer = '';
			if( array_key_exists( $element->id, $response ) ){
				$answer = $response[ $element->id ];
			}

			if( !$element->validate( $answer ) ):

				if( empty( $af_response_errors[ $element->id ] ) ){
					$af_response_errors[ $element->id ] = array();
				}

				// Getting every error of question back
				foreach( $element->validate_errors AS $error ):
					$af_response_errors[ $element->id ][] = $error;
				endforeach;

			endif;
		endforeach;

		if( is_array( $af_response_errors ) && array_key_exists( $element->id, $af_response_errors ) ):
			// @todo: One Element at the end ???
			if( is_array( $af_response_errors[ $element->id ] ) && count( $af_response_errors[ $element->id ] ) == 0 ):
				return TRUE;
			else:
				return FALSE;
			endif;
		else:
			return TRUE;
		endif;
	}

	/**
	 * Getting actual step by POST data and error response
	 *
	 * @return int
	 */
	public function get_actual_step()
	{
		global $af_response_errors;

		// If there was posted af_next_step and there was no error
		if( isset( $_POST[ 'af_next_step' ] ) && 0 == count( $af_response_errors ) ){
			$actual_step = (int) $_POST[ 'af_next_step' ];
		}else{

			// If there was posted af_next_step and there was an error
			if( isset( $_POST[ 'af_actual_step' ] ) ){
				$actual_step = (int) $_POST[ 'af_actual_step' ];
				// If there was nothing posted, start at the beginning
			}else{
				$actual_step = 0;
			}
		}

		// If user wanted to go backwards, set one step back
		if( array_key_exists( 'af_submission_back', $_POST ) ){
			$actual_step = (int) $_POST[ 'af_actual_step' ] - 1;
		}

		return $actual_step;
	}

	/**
	 * Getting navigation for form
	 *
	 * @param $actual_step
	 * @param $next_step
	 *
	 * @return string
	 */
	public function get_navigation( $actual_step, $next_step )
	{
		$html = '';

		// If there was a step before, show previous button
		if( $actual_step > 0 ){
			$html .= '<input type="submit" name="af_submission_back" value="' . __( 'Previous Step', 'af-locale' ) . '"> ';
		}

		if( $actual_step == $next_step ){
			// If actual step is next step, show finish form button
			$html .= '<input type="submit" name="af_submission" value="' . __( 'Send', 'af-locale' ) . '">';
		}else{
			// Show next button
			$html .= '<input type="submit" name="af_submission" value="' . __( 'Next Step', 'af-locale' ) . '">';
		}

		return $html;
	}

	/**
	 * Sending out finish email to participator
	 *
	 * @since 1.0.0
	 */
	public function email_finished()
	{
		global $post, $current_user;
		get_currentuserinfo();

		$subject_template = af_get_mail_template_subject( 'thankyou_participating' );

		$subject = str_replace( '%displayname%', $current_user->display_name, $subject_template );
		$subject = str_replace( '%username%', $current_user->user_nicename, $subject );
		$subject = str_replace( '%site_name%', get_bloginfo( 'name' ), $subject );
		$subject = str_replace( '%survey_title%', $post->post_title, $subject );

		$subject = apply_filters( 'questions_email_finished_subject', $subject );

		$text_template = af_get_mail_template_text( 'thankyou_participating' );

		$content = str_replace( '%displayname%', $current_user->display_name, $text_template );
		$content = str_replace( '%username%', $current_user->user_nicename, $content );
		$content = str_replace( '%site_name%', get_bloginfo( 'name' ), $content );
		$content = str_replace( '%survey_title%', $post->post_title, $content );

		$content = apply_filters( 'questions_email_finished_content', $content );

		af_mail( $current_user->user_email, $subject, $content );
	}
}

/**
 * Checks if a user has participated on a survey
 *
 * @param int  $form_id
 * @param null $user_id
 *
 * @return boolean $has_participated
 */
function af_user_has_participated( $form_id, $user_id = NULL )
{
	global $wpdb, $current_user, $af_global;

	// Setting up user ID
	if ( NULL == $user_id ){
		get_currentuserinfo();
		$user_id = $user_id = $current_user->ID;
	}

	// Setting up Form ID
	if ( NULL == $form_id ) {
		return FALSE;
	}

	$sql   = $wpdb->prepare(
		"SELECT COUNT(*) FROM {$af_global->tables->responds} WHERE questions_id=%d AND user_id=%s",
		$form_id, $user_id
	);
	$count = $wpdb->get_var( $sql );

	if ( 0 == $count )
		return FALSE;

	return TRUE;
}