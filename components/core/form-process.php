<?php
/*
 * Processing form
 *
 * This class initializes the component.
 *
 * @author awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2

  Copyright 2015 awesome.ug (support@awesome.ug)

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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Questions_FormProcess{

	/**
	 * ID of processed form
	 */
	var $form_id;

	/**
	 * Is form processing fineshed
	 */
	var $finished = FALSE;


	var $finished_id;


	var $response_id;

	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	public function __construct() {

		if ( is_admin() ) {
			return NULL;
		}

		add_action( 'parse_request', array( $this, 'process_response' ), 99 );
		add_action( 'the_post', array( $this, 'add_post_filter' ) ); // Just hooking in at the beginning of a loop

	} // end constructor

	/**
	 * Adding filter for the content to show Survey
	 * @since 1.0.0
	 */
	public function add_post_filter() {

		add_filter( 'the_content', array( $this, 'the_content' ) );
	}

	/**
	 * The filtered content gets a survey
	 *
	 * @param string $content
	 * @return string $content
	 * @since 1.0.0
	 */
	public function the_content( $content ) {

		global $post, $questions_response_errors;

		// Set global message on top of page
		if ( ! empty( $questions_response_errors ) ) {
			$html = '<div class="questions-element-error">';
			$html .= '<div class="questions-element-error-message"><p>';
			$html .= esc_attr__( 'There are open answers', 'questions-locale' );
			$html .= '</p></div></div>';
			$html = apply_filters( 'questions_draw_global_error', $html, $this );

			echo $html;
		}

		if ( 'questions' != $post->post_type ) {
			return $content;
		}

		$content = $this->show_survey( $post->ID );

		remove_filter( 'the_content', array( $this, 'the_content' ) ); // only show once

		return $content;
	}

	/**
	 * Showing form
	 *
	 * @param int $form_id
	 * @return string $survey_html
	 * @since 1.0.0
	 */
	public function show_survey( $form_id ) {

        $checked_restrictions = $this->check_restrictions( $form_id );
        $checked_timerange = $this->check_timerange( $form_id );

		if ( TRUE === $checked_restrictions && TRUE === $checked_timerange ):

			return $this->survey_form( $form_id );
		elseif( TRUE !== $checked_restrictions ):

            return $checked_restrictions;
        elseif( TRUE !== $checked_timerange ):

            return $checked_timerange;
		endif;
	}

    /**
     * Check Timerange
     *
     * Checking if the survey has not yet begun or is already over
     *
     * @param int $form_id
     * @return mixed $intime
     * @since 1.0.0
     */
    private function check_timerange( $form_id ){
        $actual_date = time();
        $start_date = strtotime( get_post_meta( $form_id, 'start_date', TRUE ) );
        $end_date = strtotime( get_post_meta( $form_id, 'end_date', TRUE ) );

        if( '' != $start_date  && 0 != (int)$start_date && FALSE != $start_date && $actual_date < $start_date ){
            $html = '<div id="questions-out-of-timerange">';
            $html.= '<p>' . esc_attr( 'The survey has not yet begun.', 'questions-locale' ) . '</p>';
            $html.= '</div>';
            return $html;
        }

        if( '' != $end_date  && 0 != (int)$end_date && FALSE != $end_date && '' != $end_date && $actual_date > $end_date ){
            $html = '<div id="questions-out-of-timerange">';
            $html.= '<p>' . esc_attr( 'The survey is already over.', 'questions-locale' ) . '</p>';
            $html.= '</div>';
            return $html;
        }

        return TRUE;
    }

	/**
	 * Check restrictions
	 *
	 * Checking restrictions if user can participate
	 *
	 * @param int $form_id
	 * @return mixed $participate True
	 * @since 1.0.0
	 */
	private function check_restrictions( $form_id ) {

		$participiant_restrictions = get_post_meta( $form_id, 'participiant_restrictions', TRUE );

		switch ( $participiant_restrictions ) {

			/**
			 * All Visitors can participate once
			 */
			case 'all_visitors':

				if ( $this->finished && $this->finished_id == $form_id ):
					return $this->text_thankyou_for_participation( $form_id );
				endif;

				if ( $this->ip_has_participated( $form_id ) ):
					return $this->text_already_participated( $form_id );
				endif;

				return TRUE;

				break;

			/**
			 * All WordPress members can participate once
			 */
			case 'all_members':

				// If user is not logged in
				if ( ! is_user_logged_in() ):
					return $this->text_not_logged_in();
				endif;

				// If user user has finished successfull
				if ( $this->finished && $this->finished_id == $form_id ):
					$this->email_finished();

					return $this->text_thankyou_for_participation( $form_id );
				endif;

				// If user has already participated
				if ( $this->has_participated( $form_id ) ):
					return $this->text_already_participated( $form_id );
				endif;

				return TRUE;

				break;

			/**
			 * Only selected members can participate once
			 */
			case 'selected_members':

				if ( ! is_user_logged_in() ):
					return $this->text_not_logged_in();
				endif;

				// If user user has finished successfull
				if ( $this->finished && $this->finished_id == $form_id ):
					$this->email_finished();

					return $this->text_thankyou_for_participation( $form_id );
				endif;

				// If user has already participated
				if ( $this->has_participated( $form_id ) ):
					return $this->text_already_participated( $form_id );
				endif;

				// If user can't participate the poll
				if ( ! $this->user_can_participate( $form_id ) ):
					return $this->text_cant_participate();
				endif;

				return TRUE;

				break;
			/**
			 * Only selected members can participate
			 */
			default:
				// If user user has finished successfull
				if ( $this->finished && $this->finished_id == $form_id ):
					return $this->text_thankyou_for_participation( $form_id );
				endif;

				return apply_filters( 'questions_check_restrictions', TRUE, $form_id, $participiant_restrictions );

				break;
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
	private function survey_form( $form_id ) {

		global $questions_response_errors, $questions_survey_id;
		$questions_survey_id = $form_id;

		do_action( 'before_survey_form' );

		if ( array_key_exists( 'questions_next_step', $_POST ) && 0 == count( $questions_response_errors ) ):
			$next_step = (int) $_POST[ 'questions_next_step' ];
		else:
			if ( array_key_exists( 'questions_actual_step', $_POST ) ):
				$next_step = (int) $_POST[ 'questions_actual_step' ];
			else:
				$next_step = 0;
			endif;
		endif;

		if ( array_key_exists( 'questions_submission_back', $_POST ) ):
			$next_step = (int) $_POST[ 'questions_actual_step' ] - 1;
		endif;

		$actual_step = $next_step;

		$html = '<form name="questions" id="questions" action="' . $_SERVER[ 'REQUEST_URI' ] . '" method="POST">';

        $html.= '<input type="hidden" name="_wpnonce" value="' .  wp_create_nonce( 'questions-' . $form_id ) . '" />';

		$step_count = $this->get_step_count( $form_id );
		
		if( 0 != $step_count ):
			
			$html .= '<div class="questions-pagination">' . sprintf(
					__(
						'Step <span class="questions-highlight-number">%d</span> of <span class="questions-highlight-number">%s</span>',
						'questions-locale'
					), $actual_step + 1, $step_count + 1
				) . '</div>';
		endif;

		$elements = $this->get_elements( $form_id, $actual_step );

		if ( is_array( $elements ) && count( $elements ) > 0 ):
			foreach ( $elements AS $element ):
				if ( ! $element->splits_form ):
					$html .= $element->draw();
				else:
					$next_step += 1;
					break;
				endif;
			endforeach;
		else:
			return FALSE;
		endif;

		if ( 0 < $actual_step ):
			$html .= '<input type="submit" name="questions_submission_back" value="' . __(
					'Previous Step', 'questions-locale'
				) . '"> ';
		endif;

		if ( $actual_step == $next_step ):
			$html .= '<input type="submit" name="questions_submission" value="' . __(
					'Finish Survey', 'questions-locale'
				) . '">';
		else:
			$html .= '<input type="submit" name="questions_submission" value="' . __(
					'Next Step', 'questions-locale'
				) . '">';
		endif;

		$html .= '<input type="hidden" name="questions_next_step" value="' . $next_step . '" />';
		$html .= '<input type="hidden" name="questions_actual_step" value="' . $actual_step . '" />';
		$html .= '<input type="hidden" name="questions_id" value="' . $form_id . '" />';

		$html .= '</form>';

		return $html;
	}

	/**
	 * Checks if a user can participate
	 *
	 * @param int $form_id
	 * @param int $user_id
	 * @return boolean $can_participate
	 * @since 1.0.0
	 */
	public function user_can_participate( $form_id, $user_id = NULL ) {

		global $wpdb, $current_user, $questions_global;

        $can_participate = FALSE;

		// Setting up user ID
		if ( NULL == $user_id ):
			get_currentuserinfo();
			$user_id = $user_id = $current_user->ID;
		endif;

        $sql = $wpdb->prepare( "SELECT user_id FROM {$questions_global->tables->participiants} WHERE survey_id = %d", $form_id );
        $user_ids = $wpdb->get_col( $sql );

        if( in_array( $user_id, $user_ids  ) )
		    $can_participate = TRUE;

		return apply_filters( 'questions_user_can_participate', $can_participate, $form_id, $user_id );
	}

	/**
	 * Get numer of spits in survey
	 *
	 * @param int $form_id
	 * @return int $splitter_count
	 * @since 1.0.0
	 */
	private function get_step_count( $form_id ) {

		$form = new Questions_Form( $form_id );

		return (int) $form->splitter_count;
	}

	/**
	 * Getting elements of a survey
	 *
	 * @param int $form_id
	 * @param int $step
	 * @return array $elements
	 * @since 1.0.0
	 */
	public function get_elements( $form_id, $step = 0 ) {

		$survey = new Questions_Form( $form_id );

		$actual_step = 0;

		$elements = array();
		foreach ( $survey->elements AS $element ):
			$elements[ $actual_step ][ ] = $element;
			if ( $element->splits_form ):
				$actual_step ++;
			endif;
		endforeach;

		if ( $actual_step < $step ) {
			return FALSE;
		}

		return $elements[ $step ];
	}

	/**
	 * Processing entered data
	 * @since 1.0.0
	 */
	public function process_response() {

		global $questions_survey_id;

		// Survey ID was posted or die
		if ( ! array_key_exists( 'questions_id', $_POST ) )
			return;

        $questions_survey_id = $_POST[ 'questions_id' ];

        // WP Nonce Check
        if( ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'questions-' . $questions_survey_id ) )
            return;

		// Survey exists or die
		if ( ! qu_form_exists( $questions_survey_id ) )
			return;

		// Checking restrictions
		if ( TRUE !== $this->check_restrictions( $questions_survey_id ) ) {
			return;
		}

		// Getting Session Data
		if ( ! isset( $_SESSION ) ) {
			session_start();
		}

		// If session has data, get it!
		if ( isset( $_SESSION[ 'questions_response' ] ) ) {
			$saved_response = $_SESSION[ 'questions_response' ][ $questions_survey_id ];
		}

		do_action( 'questions_before_process_response', $_POST );

		$response       = array();
		$this->finished = FALSE;

		// Getting data of posted step
		$survey_response = array();
		if ( array_key_exists( 'questions_response', $_POST ) ) {
			$survey_response = $_POST[ 'questions_response' ];
		}

		$survey_actual_step = (int) $_POST[ 'questions_actual_step' ];

		// Validating response values and setting up error variables
		$this->validate_response( $questions_survey_id, $survey_response, $survey_actual_step );

		// Adding / merging Values to response var
		if ( isset( $saved_response ) ):

			// Replacing old values by key
			if ( is_array( $survey_response ) && count( $survey_response ) > 0 ):
				foreach ( $survey_response AS $key => $answer ):
					$saved_response[ $key ] = qu_prepare_post_data( $answer );
				endforeach;
			endif;

			$response = $saved_response;
		else:
			$response = $survey_response;
		endif;

		$response = apply_filters( 'questions_process_response', $response );

		// Storing values in Session
		$_SESSION[ 'questions_response' ][ $questions_survey_id ] = $response;

		$this->save_response();

		do_action( 'questions_after_process_response', $_POST );
	}

	/**
	 * Saving response data
	 * @since 1.0.0
	 */
	private function save_response() {

		global $questions_response_errors, $questions_survey_id;

		do_action( 'questions_before_save_response' );

		if ( ! isset( $_SESSION[ 'questions_response' ][ $questions_survey_id ] ) ) {
			return;
		}

		if ( (int) $_POST[ 'questions_actual_step' ] == (int) $_POST[ 'questions_next_step' ]
			&& 0 == count(
				$questions_response_errors
			)
			&& ! array_key_exists( 'questions_submission_back', $_POST )
		):
			$response = $_SESSION[ 'questions_response' ][ $questions_survey_id ];

			if ( $this->save_data( $questions_survey_id, apply_filters( 'questions_save_response', $response ) ) ):
				do_action( 'questions_after_save_response' );

				// Unsetting Session, because not needed anymore
				session_destroy();
				unset( $_SESSION[ 'questions_response' ] );

				$this->finished    = TRUE;
				$this->finished_id = $questions_survey_id;
			endif;
		endif;
	}

	/**
	 * Validating response
	 *
	 * @param int $form_id
	 * @param array $response
	 * @param int $step
	 * @return boolean $validated
	 * @since 1.0.0
	 */
	public function validate_response( $form_id, $response, $step ) {

		global $questions_response_errors;

		if ( array_key_exists( 'questions_submission_back', $_POST ) ) {
			return FALSE;
		}

		if ( empty( $form_id ) ) {
			return NULL;
		}

		if ( empty( $step ) && (int) $step != 0 ) {
			return NULL;
		}

		$elements = $this->get_elements( $form_id, $step );

		if ( ! is_array( $elements ) && count( $elements ) == 0 ) {
			return NULL;
		}

		if ( empty( $questions_response_errors ) ) {
			$questions_response_errors = array();
		}

		// Running true all elements
		foreach ( $elements AS $element ):
			if ( $element->splits_form ) {
				continue;
			}

			$skip_validating = apply_filters( 'questions_skip_validating', FALSE, $element );

			if ( $skip_validating ) {
				continue;
			}

			$answer = '';
			if ( array_key_exists( $element->id, $response ) ) {
				$answer = $response[ $element->id ];
			}

			if ( ! $element->validate( $answer ) ):

				if ( empty( $questions_response_errors[ $element->id ] ) ) {
					$questions_response_errors[ $element->id ] = array();
				}

				// Getting every error of question back
				foreach ( $element->validate_errors AS $error ):
					$questions_response_errors[ $element->id ][ ] = $error;
				endforeach;

			endif;
		endforeach;

		if ( is_array( $questions_response_errors ) && array_key_exists( $element->id, $questions_response_errors ) ):
			// ??? One Element at the end ???
			if ( is_array( $questions_response_errors[ $element->id ] )
				&& count(
					$questions_response_errors[ $element->id ]
				) == 0
			):
				return TRUE;
			else:
				return FALSE;
			endif;
		else:
			return TRUE;
		endif;

	}

	/**
	 * Sub function for save_response
	 *
	 * @param int   $form_id
	 * @param array $response
	 * @return boolean $saved
	 * @since 1.0.0
	 */
	private function save_data( $form_id, $response ) {

		global $wpdb, $questions_global, $current_user;

		get_currentuserinfo();
		$user_id = $user_id = $current_user->ID;

		if ( '' == $user_id ) {
			$user_id = - 1;
		}

		// Adding new question
		$wpdb->insert(
			$questions_global->tables->responds,
			array(
				'questions_id' => $form_id,
				'user_id'      => $user_id,
				'timestamp'    => time(),
				'remote_addr'  => $_SERVER[ 'REMOTE_ADDR' ]
			)
		);

		do_action( 'questions_save_data', $form_id, $response );

        $response_id       = $wpdb->insert_id;
		$this->response_id = $response_id;

		foreach ( $response AS $element_id => $answers ):

			if ( is_array( $answers ) ):

				foreach ( $answers AS $answer ):
					$wpdb->insert(
						$questions_global->tables->respond_answers,
						array(
							'respond_id'  => $response_id,
							'question_id' => $element_id,
							'value'       => $answer
						)
					);
				endforeach;

			else:
				$answer = $answers;

				$wpdb->insert(
					$questions_global->tables->respond_answers,
					array(
						'respond_id'  => $response_id,
						'question_id' => $element_id,
						'value'       => $answer
					)
				);

			endif;
		endforeach;

		return TRUE;
	}

	/**
	 * Has the user participated survey
	 *
	 * @param $questions_id
	 * @param int $user_id
	 * @return boolean $has_participated
	 * @since 1.0.0
	 */
	public function has_participated( $form_id, $user_id = NULL ) {

		global $wpdb, $current_user, $questions_global;

		// Setting up user ID
		if ( NULL == $user_id ):
			get_currentuserinfo();
			$user_id = $user_id = $current_user->ID;
		endif;

		// Setting up Form ID
		if ( NULL == $form_id ) {
			return FALSE;
		}

		$sql   = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$questions_global->tables->responds} WHERE questions_id=%d AND user_id=%s",
            $form_id, $user_id
		);
		$count = $wpdb->get_var( $sql );

		if ( 0 == $count ):
			return FALSE;
		else:
			return TRUE;
		endif;
	}

	/**
	 * Has IP already participated
	 *
	 * @param $questions_id
	 * @return bool $has_participated
	 * @since 1.0.0
	 *
	 */
	public function ip_has_participated( $form_id ) {

		global $wpdb, $questions_global;

		$remote_ip = $_SERVER[ 'REMOTE_ADDR' ];

		$sql   = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$questions_global->tables->responds} WHERE questions_id=%d AND remote_addr=%s",
            $form_id, $remote_ip
		);
		$count = $wpdb->get_var( $sql );

		if ( 0 == $count ):
			return FALSE;
		else:
			return TRUE;
		endif;
	}

	/**
	 * Sending out finish email to participator
	 * @since 1.0.0
	 */
	public function email_finished() {

		global $post, $current_user;
		get_currentuserinfo();

		$subject_template = qu_get_mail_template_subject( 'thankyou_participating' );

		$subject = str_replace( '%displayname%', $current_user->display_name, $subject_template );
		$subject = str_replace( '%username%', $current_user->user_nicename, $subject );
		$subject = str_replace( '%site_name%', get_bloginfo( 'name' ), $subject );
		$subject = str_replace( '%survey_title%', $post->post_title, $subject );

		$subject = apply_filters( 'questions_email_finished_subject', $subject );

		$text_template = qu_get_mail_template_text( 'thankyou_participating' );

		$content = str_replace( '%displayname%', $current_user->display_name, $text_template );
		$content = str_replace( '%username%', $current_user->user_nicename, $content );
		$content = str_replace( '%site_name%', get_bloginfo( 'name' ), $content );
		$content = str_replace( '%survey_title%', $post->post_title, $content );

		$content = apply_filters( 'questions_email_finished_content', $content );

		qu_mail( $current_user->user_email, $subject, $content );
	}

	/**
	 * Text which will be shown after a user has participated successful
	 *
	 * @param int $form_id
	 * @return string $html
	 * @since 1.0.0
	 */
	public function text_thankyou_for_participation( $form_id ) {

		$show_results = get_post_meta( $form_id, 'show_results', TRUE );
		if ( '' == $show_results ) {
			$show_results = 'no';
		}

		$html = '<div id="questions-thank-participation">';
		$html .= '<p>' . __( 'Thank you for participating this survey!', 'questions-locale' ) . '</p>';
		if ( 'yes' == $show_results ) {
			$html .= $this->show_results( $form_id );
		}

		$html .= '<input name="response_id" id="response_id" type="hidden" value="' . $this->response_id . '" />';
		$html .= '</div>';

		return apply_filters( 'questions_text_thankyou_for_participation', $html, $form_id );
	}

	/**
	 * Text which will be shown if a user has participated already
	 *
	 * @param int $form_id
	 * @return string $html
	 * @since 1.0.0
	 */
	public function text_already_participated( $form_id ) {

		$show_results = get_post_meta( $form_id, 'show_results', TRUE );
		if ( '' == $show_results ) {
			$show_results = 'no';
		}

		$html = '<div id="questions-already-participated">';
		$html .= '<p>' . __( 'You already have participated this poll.', 'questions-locale' ) . '</p>';
		if ( 'yes' == $show_results ) {
			$html .= $this->show_results( $form_id );
		}

		$html .= '</div>';

		return apply_filters( 'questions_text_already_participated', $html, $form_id );
	}

	/**
	 * Text which will be shown if a user has to login to participate
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	public function text_not_logged_in() {

		$html = '<div id="questions-not-logged-in">';
		$html .= __( 'You have to be logged in to participate this survey.', 'questions-locale' );
		$html .= '</div>';

		return apply_filters( 'questions_text_not_logged_in', $html );
	}

	/**
	 * Text which will be shown if a user cant participate
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	public function text_cant_participate() {

		$html = '<div id="questions-cant-participate">';
		$html .= __( 'You can\'t participate this survey.', 'questions-locale' );
		$html .= '</div>';

		return apply_filters( 'questions_text_cant_participate', $html );
	}

	/**
	 * Showing results
	 *
	 * @param int $survey_id
	 * @return string $html
	 * @since 1.0.0
	 */
	public function show_results( $form_id ) {

		$html = '<p>' . __( 'This are the actual results:', 'questions-locale' ) . '</p>';
		$html .= do_shortcode( '[survey_results id="' . $form_id . '"]' );

		return apply_filters( 'questions_show_results', $html, $form_id );
	}
}

global $Questions_FormProcess;
$Questions_FormProcess = new Questions_FormProcess();

/**
 * Checks if a user has participated on a survey
 *
 * @param int $form_id
 * @param null $user_id
 * @return boolean $has_participated
 */
function qu_user_has_participated( $form_id, $user_id = NULL ) {

	global $Questions_FormProcess;
	return $Questions_FormProcess->has_participated( $form_id, $user_id );
}