<?php
/**
 * Processing submitted form
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_FormProcess {
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
	public function __construct( $form_id, $action_url = null ) {
		$this->form_id = $form_id;
		$this->form = new Torro_Form( $this->form_id );

		if ( null === $action_url ) {
			$this->action_url = $_SERVER[ 'REQUEST_URI' ];
		} else {
			$this->action_url = $action_url;
		}
	}

	/**
	 * Show Form
	 *
	 * Creating form HTML
	 *
	 * @param int $form_id
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	public function show_form() {
		$show_form = apply_filters( 'torro_form_show', true ); // Hook for adding restrictions and so on ...

		if ( false === $show_form ) {
			return;
		}

		if ( ! isset( $_SESSION ) ) {
			session_start();
		}

		$html = '';

		// Set global message on top of page
		if ( ! empty( $torro_response_errors ) ) {
			$html .= '<div class="torro-element-error">';
			$html .= '<div class="torro-element-error-message"><p>';
			$html .= esc_attr__( 'There are open answers', 'torro-forms' );
			$html .= '</p></div></div>';
		}

		// Getting actual step for form
		$actual_step = $this->get_actual_step();

		$html .= '<form class="torro-form" action="' . $this->action_url . '" method="POST" novalidate>';
		$html .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'torro-form-' . $this->form_id ) . '" />';

		$step_count = $this->form->get_step_count();

		// Switch on navigation if there is more than one page
		if ( 0 !== $step_count ) {
			$html .= '<div class="torro-pagination">' . sprintf( __( 'Step <span class="torro-highlight-number">%d</span> of <span class="torro-highlight-number">%s</span>', 'torro-forms' ), $actual_step + 1, $step_count + 1 ) . '</div>';
		}

		// Getting all elements of step and running them
		$elements = $this->form->get_step_elements( $actual_step );
		$next_step = $actual_step;

		ob_start();
		do_action( 'torro_form_start', $this->form_id, $actual_step, $step_count );
		$html .= ob_get_clean();

		if ( is_array( $elements ) && count( $elements ) > 0 ) {
			foreach( $elements as $element ) {
				if ( ! $element->splits_form ) {
					$html .= $element->draw();
				} else {
					$next_step += 1; // If there is a next step, setting up next step var
					break;
				}
			}
		} else {
			return false;
		}

		$html .= $this->get_navigation( $actual_step, $next_step );

		ob_start();
		do_action( 'torro_form_end', $this->form_id, $actual_step, $step_count );
		$html .= ob_get_clean();

		$html .= '<input type="hidden" name="torro_next_step" value="' . $next_step . '" />';
		$html .= '<input type="hidden" name="torro_actual_step" value="' . $actual_step . '" />';
		$html .= '<input type="hidden" name="torro_form_id" value="' . $this->form_id . '" />';

		$html .= '</form>';

		return $html;
	}

	/**
	 * Getting actual step by POST data and error response
	 *
	 * @return int
	 */
	public function get_actual_step() {
		global $torro_response_errors;

		// If there was posted torro_next_step and there was no error
		if ( isset( $_POST['torro_next_step'] ) && 0 == count( $torro_response_errors ) ) {
			$actual_step = absint( $_POST['torro_next_step'] );
		} elseif ( isset( $_POST['torro_actual_step'] ) ) {
			// If there was posted torro_next_step and there was an error
			$actual_step = absint( $_POST['torro_actual_step'] );
		} else {
			// If there was nothing posted, start at the beginning
			$actual_step = 0;
		}

		// If user wanted to go backwards, set one step back
		if ( array_key_exists( 'torro_submission_back', $_POST ) ) {
			$actual_step = absint( $_POST['torro_actual_step'] ) - 1;
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
	public function get_navigation( $actual_step, $next_step ) {
		$html = '';

		// If there was a step before, show previous button
		if ( 0 < $actual_step ) {
			$html .= '<input type="submit" name="torro_submission_back" value="' . esc_attr__( 'Previous Step', 'torro-forms' ) . '"> ';
		}

		if ( $actual_step === $next_step ) {
			// If actual step is next step, show finish form button
			ob_start();
			do_action( 'torro_form_send_button_before', $this->form_id );
			$html .= ob_get_clean();

			$html .= '<input type="submit" name="torro_submission" value="' . esc_attr__( 'Send', 'torro-forms' ) . '">';

			ob_start();
			do_action( 'torro_form_send_button_after', $this->form_id );
			$html .= ob_get_clean();
		} else {
			// Show next button
			$html .= '<input type="submit" name="torro_submission" value="' . esc_attr__( 'Next Step', 'torro-forms' ) . '">';
		}

		return $html;
	}

	/**
	 * Processing entered data
	 *
	 * @since 1.0.0
	 */
	public function process_response() {
		global $ar_form_id, $torro_response_errors;

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'torro-form-' . $ar_form_id ) ) {
			return;
		}

		$response = array();
		if ( isset( $_POST['torro_response'] ) ) {
			$response = $_POST['torro_response'];
		}

		$actual_step = absint( $_POST[ 'torro_actual_step' ] );

		// If there was a saved response
		if ( isset( $_SESSION['torro_response'][ $ar_form_id ] ) ) {
			// Merging data
			$merged_response = $_SESSION['torro_response'][ $ar_form_id ];
			if ( is_array( $response ) && 0 < count( $response ) ) {
				foreach ( $response as $key => $answer ) {
					$merged_response[ $key ] = torro_prepare_post_data( $answer );
				}
			}

			$_SESSION['torro_response'][ $ar_form_id ] = $merged_response;
		} else {
			$merged_response = $response;
		}

		$_SESSION['torro_response'][ $ar_form_id ] = $merged_response;

		$is_submit = false;
		if ( absint( $_POST[ 'torro_actual_step' ] ) === absint( $_POST[ 'torro_next_step' ] ) && ! isset( $_POST['torro_submission_back'] ) ) {
			$is_submit = true;
		}

		// Validate submitted data if user not has gone backwards
		$validation_status = true;
		if ( ! isset( $_POST[ 'torro_submission_back' ] ) ) {
			$validation_status = $this->validate( $ar_form_id, $_SESSION[ 'torro_response' ][ $ar_form_id ], $actual_step, $is_submit );
		} // Validating response values and setting up error variables

		// If form is finished and user don't have been gone backwards, save data
		if ( $is_submit && $validation_status && 0 === count( $torro_response_errors ) ) {
			$form = new Torro_Form( $ar_form_id );
			$result_id = $form->save_response( $_SESSION[ 'torro_response' ][ $ar_form_id ] );

			if ( $result_id ) {
				do_action( 'torro_response_save', $result_id );

				unset( $_SESSION['torro_response'][ $ar_form_id ] );
				$_SESSION['torro_response'][ $ar_form_id ]['finished'] = true;

				header( 'Location: ' . $_SERVER['REQUEST_URI'] );
				die();
			}
		}

		do_action( 'torro_process_response_end' );
	}

	/**
	 * Validating response
	 *
	 * @param int   $form_id
	 * @param array $response
	 * @param int   $step
	 * @param bool  $is_submit
	 *
	 * @return boolean $validated
	 * @since 1.0.0
	 */
	public function validate( $form_id, $response, $step, $is_submit ) {
		global $torro_response_errors;

		$elements = $this->form->get_step_elements( $step );
		if ( ! is_array( $elements ) || 0 === count( $elements ) ) {
			return;
		}

		$torro_response_errors = array();

		// Running through all elements
		foreach ( $elements as $element ) {
			if ( $element->splits_form ) {
				continue;
			}

			$answer = '';
			if ( array_key_exists( $element->id, $response ) ) {
				$answer = $response[ $element->id ];
			}

			if ( ! $element->validate( $answer ) ) {
				if ( 0 < count( $element->validate_errors ) ) {
					if ( ! isset( $torro_response_errors[ $element->id ] ) || empty( $torro_response_errors[ $element->id ] ) ) {
						$torro_response_errors[ $element->id ] = array();
					}

					// Getting every error of element back
					foreach ( $element->validate_errors AS $error ) {
						$torro_response_errors[ $element->id ][] = $error;
					}
				}

			}
		}

		$validation_status = count( $torro_response_errors ) > 0 ? false : true;

		return apply_filters( 'torro_response_validation_status', $validation_status, $form_id, $torro_response_errors, $step, $is_submit );
	}
}

/**
 * Checks if a user has participated on a Form
 *
 * @param int  $form_id
 * @param null $user_id
 *
 * @return boolean $has_participated
 */
function torro_user_has_participated( $form_id, $user_id = null ) {
	global $wpdb, $current_user;

	// Setting up user ID
	if ( null === $user_id ) {
		get_currentuserinfo();
		$user_id = $user_id = $current_user->ID;
	}

	// Setting up Form ID
	if ( null === $form_id ) {
		return false;
	}

	$sql = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->torro_results WHERE form_id=%d AND user_id=%s", $form_id, $user_id );

	$count = absint( $wpdb->get_var( $sql ) );

	if ( 0 === $count ) {
		return false;
	}

	return true;
}
