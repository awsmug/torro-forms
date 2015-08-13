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
     * Form object
     */
    var $form;

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
	public function __construct( $form_id ) {

		if ( is_admin() ) {
			return NULL;
		}

        $this->form_id = $form_id;
        $this->form = new Questions_Form( $this->form_id );
	} // end constructor

    /**
     * Showing form
     *
     * @param int $form_id
     * @return string $survey_html
     * @since 1.0.0
     */
    public function init_form() {

        $checked_restrictions = $this->check_restrictions( $this->form_id );
        $checked_timerange = $this->check_timerange( $this->form_id );

        // @todo Adding restrictions API

        if ( TRUE === $checked_restrictions && TRUE === $checked_timerange ):

            echo $this->show_form();

        elseif( TRUE !== $checked_restrictions ):

            echo $checked_restrictions;

        elseif( TRUE !== $checked_timerange ):

            echo $checked_timerange;

        endif;
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
    private function show_form() {

        // Getting actual step for form
        $actual_step = $this->get_actual_step();

        do_action( 'questions_form_start' );

        $html = '<form name="questions" id="questions" action="' . $_SERVER[ 'REQUEST_URI' ] . '" method="POST">';
        $html.= '<input type="hidden" name="_wpnonce" value="' .  wp_create_nonce( 'questions-' . $this->form_id ) . '" />';

        $step_count = $this->form->get_step_count();

        // Switch on navigation if there is more than one page
        if( 0 != $step_count ) {
            $html .= '<div class="questions-pagination">' . sprintf(__('Step <span class="questions-highlight-number">%d</span> of <span class="questions-highlight-number">%s</span>', 'questions-locale'), $actual_step + 1, $step_count + 1) . '</div>';
        }

        // Getting all elements of step and running them
        $elements = $this->form->get_step_elements( $actual_step );
        $next_step = $actual_step;

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

        $html .= $this->get_navigation( $actual_step, $next_step );

        $html .= '<input type="hidden" name="questions_next_step" value="' . $next_step . '" />';
        $html .= '<input type="hidden" name="questions_actual_step" value="' . $actual_step . '" />';
        $html .= '<input type="hidden" name="questions_id" value="' . $this->form_id . '" />';

        $html .= '</form>';

        do_action( 'questions_form_end' );

        return $html;
    }

    /**
     * Processing entered data
     * @since 1.0.0
     */
    public function process_response() {
        global $questions_form_id;

        // Form ID was posted or die
        if ( ! array_key_exists( 'questions_id', $_POST ) )
            return;

        // Setting up Questions form ID after submitting
        $questions_form_id = $_POST[ 'questions_id' ];

        // WP Nonce Check
        if( ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'questions-' . $questions_form_id ) )
            return;

        // Form exists or die
        if ( ! qu_form_exists( $questions_form_id ) )
            return;

        // Checking restrictions
        // @todo Should check restrictions at this place? Should be done before!
        if ( TRUE !== $this->check_restrictions( $questions_form_id ) )
            return;

        // Setting up session if not exists
        if ( ! isset( $_SESSION ) )
            session_start();

        // If session has data, get it!
        if ( isset( $_SESSION[ 'questions_response' ] ) )
            $saved_response = $_SESSION[ 'questions_response' ][ $questions_form_id ];

        do_action( 'questions_process_response_start' );

        $response       = array();
        $this->finished = FALSE;

        // Getting data of posted step if existing
        $response = array();
        if ( array_key_exists( 'questions_response', $_POST ) )
            $response = $_POST[ 'questions_response' ];


        $actual_step = (int) $_POST[ 'questions_actual_step' ];

        // Validate submitted data if user not has gone backwards
        if ( !array_key_exists( 'questions_submission_back', $_POST ) )
            $this->validate( $questions_form_id, $response, $actual_step ); // Validating response values and setting up error variables

        // If there was a saved response > Merge data
        if ( isset( $saved_response ) ) {

            // Merging data
            if (is_array($response) && count($response) > 0)
                foreach ($response AS $key => $answer)
                    $saved_response[$key] = qu_prepare_post_data($answer);

            $response = $saved_response;
        }

        // Storing values in Session
        $_SESSION[ 'questions_response' ][ $questions_form_id ] = $response;

        // If form is finished and user don't have been gone backwards, save data
        if ( (int) $_POST[ 'questions_actual_step' ] == (int) $_POST[ 'questions_next_step' ] && 0 == count( $questions_response_errors ) && ! array_key_exists( 'questions_submission_back', $_POST ) ):

            $questions_form = new Questions_Form( $questions_form_id );

            if ( $questions_form->save_response( $response ) ):
                do_action( 'questions_after_save_response' );

                // Unsetting Session, because not needed anymore
                session_destroy();
                unset( $_SESSION[ 'questions_response' ] );

                $this->finished    = TRUE;
                $this->finished_id = $questions_form_id;
            endif;
        endif;

        do_action( 'questions_process_response_end' );
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
    public function validate( $form_id, $response, $step ) {

        global $questions_response_errors;

        $elements = $this->form->get_step_elements( $step );
        if ( ! is_array( $elements ) && count( $elements ) == 0 )
            return;

        $questions_response_errors = array();

        // Running true all elements
        foreach ( $elements AS $element ):
            if ( $element->splits_form ) {
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
     * Getting actual step by POST data and error response
     * @return int
     */
    public function get_actual_step(){
        global $questions_response_errors;

        // If there was posted questions_next_step and there was no error
        if ( array_key_exists( 'questions_next_step', $_POST ) && 0 == count( $questions_response_errors ) ):
            $actual_step = (int) $_POST[ 'questions_next_step' ];
        else:
            if ( array_key_exists( 'questions_actual_step', $_POST ) ):
                // If there was posted questions_next_step and there was an error
                $actual_step = (int) $_POST[ 'questions_actual_step' ];
            else:
                // If there was nothing posted, start at the beginning
                $actual_step = 0;
            endif;
        endif;

        // If useer wanted to go backwards, set one step back
        if ( array_key_exists( 'questions_submission_back', $_POST ) ):
            $actual_step = (int) $_POST[ 'questions_actual_step' ] - 1;
        endif;

        return $actual_step;
    }

    /**
     * Getting navigation for form
     * @param $actual_step
     * @param $next_step
     * @return string
     */
    public function get_navigation( $actual_step, $next_step ){

        $html = '';

        // If there was a step before, show previous button
        if ( $actual_step > 0  ) {
            $html .= '<input type="submit" name="questions_submission_back" value="' . __('Previous Step', 'questions-locale') . '"> ';
        }

        if ( $actual_step == $next_step ){
            // If actual step is next step, show finish form button
            $html .= '<input type="submit" name="questions_submission" value="' . __( 'Finish Survey', 'questions-locale' ) . '">';
        }else{
            // Show next button
            $html .= '<input type="submit" name="questions_submission" value="' . __('Next Step', 'questions-locale') . '">';
        }

        return $html;
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
		$html .= '<p>' . __( 'You already have participated in this poll.', 'questions-locale' ) . '</p>';
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

    /**
     * Check restrictions
     *
     * Checking restrictions if user can participate
     *
     * @param int $form_id
     * @return mixed $participate True
     * @since 1.0.0
     */
    public function check_restrictions( $form_id ) {

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
     * Check Timerange
     *
     * Checking if the survey has not yet begun or is already over
     *
     * @param int $form_id
     * @return mixed $intime
     * @since 1.0.0
     */
    public function check_timerange( $form_id ){
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
}

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