<?php
/**
 * Form Controller
 *
 * This class will controll the form
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

class Torro_Form_Controller {

	private static $instance = null;

	/**
	 * ID of processed form
	 *
	 * @var int
	 */
	private $form_id = null;

	/**
	 * @var int
	 * @since 1.0.0
	 */
	private $form_container_id = null;

	/**
	 * Form object
	 *
	 * @var object
	 *
	 * @since 1.0.0
	 */
	private $form = null;

	/**
	 * Actual step
	 * @var int
	 * @since 1.0.0
	 */
	private $actual_step = 0;

	/**
	 * Actual step
	 * @var int
	 * @since 1.0.0
	 */
	private $previous_step = 0;

	/**
	 * Determines if we are going forward
	 * @var bool
	 */
	private $going_forward = false;

	/**
	 * Determines if a response will be saved or not
	 * @var bool
	 */
	private $save_response = false;

	/**
	 * Is this a torro submit?
	 * @var bool
	 */
	private $is_torro_submit = false;

	/**
	 * Action URL
	 *
	 * @var string
	 * @since 1.0.0
	 */
	private $form_action_url = null;

	/**
	 * Response Errors
	 *
	 * @var array
	 * @since 1.0.0
	 */
	private $response_errors = array();

	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	private function __construct( $filter_the_content = false ) {
		add_action( 'parse_request', array( $this, 'parse_request' ), 100, 1 );

		if ( true === $filter_the_content ) {
			add_action( 'the_post', array( $this, 'add_filter_the_content' ) ); // Only on a loop
		}

		if ( ! isset( $_SESSION ) ) {
			session_start();
		}

		$this->set_form_action_url( $_SERVER[ 'REQUEST_URI' ] );
	}

	/**
	 * Singleton
	 *
	 * @param bool $filter_the_content
	 *
	 * @return null|Torro_Form_Controller
	 *
	 * @since 1.0.0
	 */
	public static function instance( $filter_the_content = false ) {
		if ( null === self::$instance ) {
			self::$instance = new self( $filter_the_content );
		}

		return self::$instance;
	}

	/**
	 * Text which will be shown after a user has participated successful
	 *
	 * @param int $form_id
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	public static function show_results( $form_id ) {
		$show_results = get_post_meta( $form_id, 'show_results', true );

		$html = '<div class="torro-results">';
		if ( 'yes' === $show_results ) {
			$html = '<p>' . __( 'This are the current results:', 'torro-forms' ) . '</p>';
			$html .= do_shortcode( '[form_charts id="' . $form_id . '"]' );
		}
		$html .= '</div>';

		return apply_filters( 'torro_show_results', $html, $form_id );
	}

	/**
	 * Porcessing Request and setting up class variables
	 *
	 * @param array $response
	 *
	 * @since 1.0.0
	 */
	public function parse_request( $request ) {
		$this->parse_posted_vars();

		if ( ! $this->is_torro_submit ){
			return;
		}

		$response_new   = isset( $_POST[ 'torro_response' ] ) ? $_POST[ 'torro_response' ] : array();
		$response_saved = isset( $_SESSION[ 'torro_response' ][ $this->form_id ] ) ? $_SESSION[ 'torro_response' ][ $this->form_id ] : array();
		$merged_response = $response_new;

		// If there was a saved response merge it with new
		if ( ! empty( $response_saved ) ) {
			$merged_response = $response_saved;

			if ( is_array( $response_new ) && 0 < count( $response_new ) ) {
				foreach ( $response_new as $key => $answer ) {
					$merged_response[ $key ] = torro_prepare_post_data( $answer );
				}
			}
		}

		$_SESSION[ 'torro_response' ][ $this->form_id ] = $merged_response;  // Saving data to Session for further submits

		// Only parse request if not going backwards
		if( ! $this->going_forward ){
			return;
		}

		$validated = $this->validate( $response_new, $this->previous_step );

		if( ! $validated )
		{
			$this->actual_step = absint( $_POST[ 'torro_actual_step' ] );
			return;
		}

		// Saving
		if ( $this->save_response ) {
			$result_id = torro()->forms( $this->form_id )->save_response( $merged_response );

			// After successfull saving
			if ( $result_id ) {
				do_action( 'torro_response_save', $this->form_id, $result_id, $merged_response );

				unset( $_SESSION[ 'torro_response' ][ $this->form_id ] );

				$_SESSION[ 'torro_response' ][ $this->form_id ][ 'result_id' ] = $result_id;
				$_SESSION[ 'torro_response' ][ $this->form_id ][ 'finished' ]  = true;

				header( 'Location: ' . $_SERVER[ 'REQUEST_URI' ] );
				die();
			}
		}

		do_action( 'torro_form_parse_request', $this->form_id, $this->actual_step, $response_new, $merged_response );
	}

	/**
	 * Parsing posted vars and setting up class variables
	 *
	 * @since 1.0.0
	 */
	private function parse_posted_vars() {
		$this->is_torro_submit = false;

		if ( isset( $_POST[ 'torro_form_id' ] ) ) {
			$this->is_torro_submit = true;
		}

		if ( isset( $_POST[ '_wpnonce' ] ) && wp_verify_nonce( $_POST[ '_wpnonce' ], 'torro-form-' . $this->form_id ) ) {
			$this->is_torro_submit = true;
		}

		if( ! $this->is_torro_submit ){
			return;
		}

		if ( ! $this->set_form_id( $_POST[ 'torro_form_id' ] ) ) {
			return;
		}

		if ( isset( $_POST[ 'torro_next_step' ] ) ) {
			$this->actual_step = absint( $_POST[ 'torro_next_step' ] );
			$this->previous_step = absint( $_POST[ 'torro_actual_step' ] );
		} elseif ( isset( $_POST[ 'torro_actual_step' ] ) ) {
			$this->actual_step = absint( $_POST[ 'torro_actual_step' ] );
			$this->previous_step = absint( $_POST[ 'torro_actual_step' ] );
		} else {
			$this->actual_step = 0;
			$this->previous_step = 0;
		}

		if ( array_key_exists( 'torro_submission_back', $_POST ) ) {
			$this->actual_step = absint( $_POST[ 'torro_actual_step' ] ) - 1;
		}

		$this->going_forward = false;
		if ( ! isset( $_POST[ 'torro_submission_back' ] ) ) {
			$this->going_forward = true;
		}

		$this->save_response = false;
		if( absint( $_POST[ 'torro_actual_step' ] ) === absint( $_POST[ 'torro_next_step' ] ) ){
			$this->save_response = true;
		}
	}

	/**
	 * Set Form ID
	 *
	 * @param $form_id
	 *
	 * @return boolean
	 */
	public function set_form_id( $form_id ) {
		if ( torro()->forms( $form_id )->exists() ) {
			$this->form_id = $form_id;
			$this->form    = torro()->forms( $this->form_id );

			return true;
		}

		return false;
	}

	/**
	 * Return the current form id
	 *
	 * @return int
	 */
	public function get_form_id() {
		return $this->form_id;
	}

	/**
	 * Set Form ID
	 *
	 * @param $form_id
	 *
	 * @return boolean
	 */
	public function set_form_action_url( $url ) {
		$this->form_action_url = $url;
	}

	/**
	 * Get response errors
	 *
	 * @return array
	 */
	public function get_response_errors() {
		return $this->response_errors;
	}

	/**
	 * Adding filter for the content to show Form
	 *
	 * @since 1.0.0
	 */
	public function add_filter_the_content() {
		add_filter( 'the_content', array( $this, 'filter_the_content' ) );
	}

	/**
	 * The filtered content gets a Form
	 *
	 * @param string $content
	 *
	 * @return string $content
	 * @since 1.0.0
	 */
	public function filter_the_content( $content ) {
		global $post;

		if ( null === $this->form_id ) {
			$this->set_form_id( $post->ID );
		}

		if ( 'torro-forms' !== $post->post_type ) {
			return $content;
		}

		$html = $this->html();

		remove_filter( 'the_content', array( $this, 'filter_the_content' ) ); // only show once

		return $html;
	}

	/**
	 * Show Form
	 *
	 * Creating form HTML
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	public function html() {
		$html = '';

		if ( isset( $_SESSION[ 'torro_response' ][ $this->form_id ][ 'finished' ] ) ) {
			ob_start();
			do_action( 'torro_form_finished', $this->form_id, $_SESSION[ 'torro_response' ][ $this->form_id ][ 'result_id' ] );
			$html .= ob_get_clean();

			$html .= $this->show_results( $this->form_id );

			session_destroy();
		} else {
			$show_form = apply_filters( 'torro_form_show', true ); // Hook for adding restrictions and so on ...

			if ( false === $show_form ) {
				return;
			}

			// Set global message on top of page
			if ( ! empty( $this->response_errors ) ) {
				$html .= '<div class="torro-element-error">';
				$html .= '<div class="torro-element-error-message"><p>';
				$html .= esc_attr__( 'There are open answers', 'torro-forms' );
				$html .= '</p></div></div>';
			}

			$html .= '<form class="torro-form" action="' . $this->form_action_url . '" method="POST" novalidate>';
			$html .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'torro-form-' . $this->form_id ) . '" />';

			$step_count = torro()->forms( $this->form_id )->get_step_count();

			// Switch on navigation if there is more than one page
			if ( 0 !== $step_count ) {
				$html .= '<div class="torro-pagination">' . sprintf( __( 'Step <span class="torro-highlight-number">%d</span> of <span class="torro-highlight-number">%s</span>', 'torro-forms' ), $this->actual_step + 1, $step_count + 1 ) . '</div>';
			}

			// Getting all elements of step and running them
			$elements  = torro()->forms( $this->form_id )->get_step_elements( $this->actual_step );
			$next_step = $this->actual_step;

			ob_start();
			do_action( 'torro_form_start', $this->form_id, $this->actual_step, $step_count );
			$html .= ob_get_clean();

			if ( is_array( $elements ) && count( $elements ) > 0 ) {
				foreach ( $elements as $element ) {
					if ( ! $element->splits_form ) {
						$html .= $element->get_html();
					} else {
						$next_step += 1; // If there is a next step, setting up next step var
						break;
					}
				}
			} else {
				return false;
			}

			$html .= $this->get_navigation( $this->actual_step, $next_step );

			ob_start();
			do_action( 'torro_form_end', $this->form_id, $this->actual_step, $step_count );
			$html .= ob_get_clean();

			$html .= '<input type="hidden" name="torro_next_step" value="' . $next_step . '" />';
			$html .= '<input type="hidden" name="torro_actual_step" value="' . $this->actual_step . '" />';
			$html .= '<input type="hidden" name="torro_form_id" value="' . $this->form_id . '" />';

			$html .= '</form>';
		}

		return $html;
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
	private function validate( $response, $step ) {
		$elements = torro()->forms( $this->form_id )->get_step_elements( $step );

		if ( ! is_array( $elements ) || 0 === count( $elements ) ) {
			return;
		}

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

					// Getting every error of element back
					foreach ( $element->validate_errors AS $message ) {
						$this->add_response_error( $element->id, $message );
					}
				}
			}
		}

		$validation_status = count( $this->response_errors ) > 0 ? false : true;

		return apply_filters( 'torro_response_validation_status', $validation_status, $this->form_id, $this->response_errors, $step );
	}

	/**
	 * Adding response errors
	 *
	 * @param $element_id
	 * @param $message
	 */
	private function add_response_error( $element_id, $message ) {
		if ( ! isset( $this->response_errors[ $element_id ] ) || empty( $this->response_errors[ $element_id ] ) ) {
			$this->response_errors[ $element_id ] = array();
		}

		$this->response_errors[ $element_id ][] = $message;
	}

	/**
	 * Getting navigation for form
	 *
	 * @param $actual_step
	 * @param $next_step
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function get_navigation( $actual_step, $next_step ) {
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
}

Torro_Form_Controller::instance( true );