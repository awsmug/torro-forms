<?php
/**
 * Author identification access control class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Access_Controls;

use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

/**
 * Class for an access control to restrict based on author identification.
 *
 * @since 1.0.0
 */
class Author_Identification extends Access_Control implements Submission_Modifier_Access_Control_Interface {

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function bootstrap() {
		$this->slug        = 'author_identification';
		$this->title       = __( 'Author Identification', 'torro-forms' );
		$this->description = __( 'Allows you to restrict this form based on whether it belongs to a specific author.', 'torro-forms' );
	}

	/**
	 * Determines whether the current user can access a specific form or submission.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Submission object, or null if no submission is set.
	 * @return bool|WP_Error True if the form or submission can be accessed, false or error object otherwise.
	 */
	public function can_access( $form, $submission = null ) {
		if ( $this->get_form_option( $form->id, 'prevent_multiple_submissions' ) ) {
			// Always allow access to already completed submissions.
			if ( $submission && 'completed' === $submission->status ) {
				return true;
			}

			// Back-compat: Check for whether an old cookie is still set.
			if ( $this->get_form_option( $form->id, 'use_cookie_check' ) && isset( $_COOKIE[ 'torro_has_participated_form_' . $form->id ] ) && 'yes' === $_COOKIE[ 'torro_has_participated_form_' . $form->id ] ) {
				$message = $this->get_form_option( $form->id, 'already_submitted_message' );
				if ( empty( $message ) ) {
					$message = $this->get_default_already_submitted_message();
				}

				return new WP_Error( 'already_submitted', $message );
			}

			$query_args = array(
				'number' => 1,
				'fields' => 'ids',
				'status' => 'completed',
			);
			$valid_args = false;
			if ( is_user_logged_in() ) {
				$query_args['user_id'] = get_current_user_id();
			} else {
				$identification_args = array();
				if ( $this->get_form_option( $form->id, 'use_ip_check' ) && ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
					$validated_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );
					if ( ! empty( $validated_ip ) ) {
						$identification_args['remote_addr'] = $validated_ip;
					}
				}
				if ( $this->get_form_option( $form->id, 'use_cookie_check' ) && ! empty( $_COOKIE['torro_identity'] ) ) {
					$identification_args['user_key'] = esc_attr( wp_unslash( $_COOKIE['torro_identity'] ) );
				} elseif( isset( $_SESSION ) && ! empty( $_SESSION['torro_identity'] ) ) {
					$identification_args['user_key'] = esc_attr( wp_unslash( $_SESSION['torro_identity'] ) );
				}
				if ( ! empty( $identification_args ) ) {
					$query_args['author_identification'] = $identification_args;
				}
			}

			if ( count( $query_args ) === 4 ) {
				$submissions = $form->get_submissions( $query_args );
				if ( count( $submissions ) > 0 ) {
					$message = $this->get_form_option( $form->id, 'already_submitted_message' );
					if ( empty( $message ) ) {
						$message = $this->get_default_already_submitted_message();
					}

					return new WP_Error( 'already_submitted', $message );
				}
			}
		}

		return true;
	}

	/**
	 * Sets additional data for a submission when it is created.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Submission $submission New submission object.
	 * @param Form       $form       Form object the submission belongs to.
	 * @param array      $data       Submission POST data.
	 */
	public function set_submission_data( $submission, $form, $data ) {
		if ( $this->get_form_option( $form->id, 'use_ip_check' ) ) {
			if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
				$validated_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );
				if ( ! empty( $validated_ip ) ) {
					$submission->remote_addr = $validated_ip;
				}
			}
		}

		if ( $this->get_form_option( $form->id, 'use_cookie_check' ) ) {
			if ( ! isset( $_COOKIE['torro_identity'] ) ) {
				setcookie( 'torro_identity', $submission->user_key, current_time( 'timestamp' ) + 3 * YEAR_IN_SECONDS );
			}
		}
	}

	/**
	 * Returns the available meta fields for the submodule.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$meta_fields = parent::get_meta_fields();

		$meta_fields['use_ip_check'] = array(
			'type'         => 'checkbox',
			'label'        => __( 'Enable IP address detection to identify a non logged-in user?', 'torro-forms' ),
			'description'  => __( 'If you activate this checkbox, the IP address will be detected to identify a non logged-in user submitting a form.', 'torro-forms' ),
		);
		$meta_fields['use_cookie_check'] = array(
			'type'         => 'checkbox',
			'label'        => __( 'Enable usage of a cookie to identify a non logged-in user?', 'torro-forms' ),
			'description'  => __( 'If you activate this checkbox, a cookie will be set to identify a non logged-in user submitting a form.', 'torro-forms' ),
		);
		$meta_fields['prevent_multiple_submissions'] = array(
			'type'        => 'checkbox',
			'label'       => __( 'Prevent multiple submissions by a single user?', 'torro-forms' ),
			'description' => __( 'Click the checkbox to ensure that participants may only submit this form once.', 'torro-forms' ),
		);
		$meta_fields['already_submitted_message'] = array(
			'type'          => 'text',
			'label'         => __( '&#8220;Already submitted&#8221; Message', 'torro-forms' ),
			'description'   => __( 'Enter the message to show to the user when they have already submitted this form.', 'torro-forms' ),
			'default'       => $this->get_default_already_submitted_message(),
			'input_classes' => array( 'regular-text' ),
			'dependencies'  => array(
				array(
					'prop'     => 'display',
					'callback' => 'get_data_by_condition_true',
					'fields'   => array( 'prevent_multiple_submissions' ),
					'args'     => array(),
				),
			),
		);

		/* TODO: back-compat with 'form_access_controls_allmembers_same_users', 'form_access_controls_check_ip', 'form_access_controls_check_cookie' (all set to 'yes' or empty string) and 'already_entered_text' (string); if set, set 'enabled' too. */

		return $meta_fields;
	}

	/**
	 * Returns the default message to display when the user is not logged in.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string Message to display.
	 */
	protected function get_default_already_submitted_message() {
		return __( 'You already submitted this form.', 'torro-forms' );
	}
}
