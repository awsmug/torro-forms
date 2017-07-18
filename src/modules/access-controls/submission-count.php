<?php
/**
 * Submission count access control class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Access_Controls;

use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

/**
 * Class for an access control to restrict based on submission count.
 *
 * @since 1.0.0
 */
class Submission_Count extends Access_Control {

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function bootstrap() {
		$this->slug        = 'submission_count';
		$this->title       = __( 'Submission Count', 'torro-forms' );
		$this->description = __( 'Allows you restrict this form based on how many submissions have already been completed.', 'torro-forms' );
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
		$limit = (int) $this->get_form_option( $form->id, 'total_submissions_limit', 0 );
		if ( $limit > 0 ) {
			$submissions = $form->get_submissions( array(
				'number' => $limit,
				'fields' => 'ids',
				'status' => 'completed',
			) );
			if ( count( $submissions ) >= $limit ) {
				$message = $this->get_form_option( $form->id, 'total_submissions_reached_message' );
				if ( empty( $message ) ) {
					$message = $this->get_default_total_submissions_reached_message();
				}

				return new WP_Error( 'total_submissions_reached', $message );
			}
		}

		if ( $this->get_form_option( $form->id, 'prevent_multiple_submissions' ) ) {
			$query_args = array(
				'number' => 1,
				'fields' => 'ids',
				'status' => 'completed',
			);
			$valid_args = false;
			if ( is_user_logged_in() ) {
				$query_args['user_id'] = get_current_user_id();
			} elseif ( $this->get_form_option( $form->id, 'use_ip_check' ) && ! empty( $_SERVER['REMOTE_ADDR'] ) && preg_match( '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $_SERVER['REMOTE_ADDR'] ) ) {
				$query_args['remote_addr'] = $_SERVER['REMOTE_ADDR'];
			} elseif ( $this->get_form_option( $form->id, 'use_cookie_check' ) && isset( $_COOKIE['torro_identity'] ) ) {
				$query_args['cookie_key'] = $_COOKIE['torro_identity'];
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
	 * Returns the available meta fields for the submodule.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$meta_fields = parent::get_meta_fields();

		$meta_fields['total_submissions_limit'] = array(
			'type'        => 'number',
			'label'       => __( 'Total Submissions Limit', 'torro-forms' ),
			'description' => __( 'Enter the total amount of submissions that are allowed to be submitted for this form. An empty value or 0 means no limit.', 'torro-forms' ),
			'min'         => 0,
			'step'        => 1,
		);
		$meta_fields['total_submissions_reached_message'] = array(
			'type'          => 'text',
			'label'         => __( '&#8220;Total submissions reached&#8221; Message', 'torro-forms' ),
			'description'   => __( 'Enter the message to show to the user when a sufficient amount of submissions have already been completed.', 'torro-forms' ),
			'default'       => $this->get_default_total_submissions_reached_message(),
			'input_classes' => array( 'regular-text' ),
		);
		$meta_fields['prevent_multiple_submissions'] = array(
			'type'        => 'checkbox',
			'label'       => __( 'Prevent multiple submissions by a single user?', 'torro-forms' ),
			'description' => __( 'Click the checkbox to ensure that participants may only submit this form once.', 'torro-forms' ),
		);
		$meta_fields['use_ip_check'] = array(
			'type'         => 'checkbox',
			'label'        => __( 'Enable IP address detection to identify a non logged-in user?', 'torro-forms' ),
			'description'  => __( 'If you activate this checkbox, the IP address will be detected to identify a non logged-in user submitting a form.', 'torro-forms' ),
			'dependencies' => array(
				array(
					'prop'     => 'display',
					'callback' => 'get_data_by_condition_true',
					'fields'   => array( 'prevent_multiple_submissions' ),
					'args'     => array(),
				),
			),
		);
		$meta_fields['use_cookie_check'] = array(
			'type'         => 'checkbox',
			'label'        => __( 'Enable usage of a cookie to identify a non logged-in user?', 'torro-forms' ),
			'description'  => __( 'If you activate this checkbox, a cookie will be set to identify a non logged-in user submitting a form.', 'torro-forms' ),
			'dependencies' => array(
				array(
					'prop'     => 'display',
					'callback' => 'get_data_by_condition_true',
					'fields'   => array( 'prevent_multiple_submissions' ),
					'args'     => array(),
				),
			),
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
	 * Returns the default message to display when the total amount of submissions has been reached.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string Message to display.
	 */
	protected function get_default_total_submissions_reached_message() {
		return __( 'This form is no longer open to submissions since the maximum number of submissions has been reached.', 'torro-forms' );
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
