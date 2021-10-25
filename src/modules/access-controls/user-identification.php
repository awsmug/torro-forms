<?php
/**
 * User identification access control class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Access_Controls;

use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use Leaves_And_Love\Plugin_Lib\Fixes;
use WP_Error;

/**
 * Class for an access control to restrict based on author identification.
 *
 * @since 1.0.0
 */
class User_Identification extends Access_Control implements Submission_Modifier_Access_Control_Interface {

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug  = 'user_identification';
		$this->title = __( 'User Identification', 'torro-forms' );
	}

	/**
	 * Checks whether the access control is enabled for a specific form.
	 *
	 * @since 1.0.0
	 *
	 * @param Form $form Form object to check.
	 * @return bool True if the access control is enabled, false otherwise.
	 */
	public function enabled( $form ) {
		return true;
	}

	/**
	 * Determines whether the current user can access a specific form or submission.
	 *
	 * @since 1.0.0
	 *
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Submission object, or null if no submission is set.
	 * @return bool|WP_Error True if the form or submission can be accessed, false or error object otherwise.
	 */
	public function can_access( $form, $submission = null ) {
		if ( $this->get_form_option( $form->id, 'prevent_edit_others_submission', true ) && $submission ) {
			$others_submission_error = new WP_Error( 'others_submission', __( 'You do not have access to this form submission.', 'torro-forms' ) );
			
			$skip_further_checks = false;
			if ( is_user_logged_in() && ! empty( $submission->user_id )) {
				if(get_current_user_id() !== $submission->user_id ) {
					return $others_submission_error;
				} else {
					$skip_further_checks = true;	
				}
			}

			if ( ! $skip_further_checks && ! empty( $submission->user_key ) ) {
				if ( filter_has_var( INPUT_COOKIE, 'torro_identity' ) ) {
					if ( esc_attr( filter_input( INPUT_COOKIE, 'torro_identity' ) ) !== $submission->user_key ) {
						return $others_submission_error;
					} else {
						$skip_further_checks = true;
					}
				}
			}

			if ( ! $skip_further_checks && ! empty( $submission->remote_addr ) ) {
				$remote_addr = Fixes::php_filter_input( INPUT_SERVER, 'REMOTE_ADDR' );
				if ( ! empty( $remote_addr ) ) {
					if ( $remote_addr !== $submission->remote_addr ) {
						return $others_submission_error;
					} else {
						$skip_further_checks = true;
					}
				}
			}

			if ( ! $skip_further_checks && ( empty( $submission->user_key ) || ! isset( $_SESSION ) || empty( $_SESSION['torro_identity'] ) || $_SESSION['torro_identity'] !== $submission->user_key ) ) {
				return $others_submission_error;
			}
		}

		if ( $this->get_form_option( $form->id, 'prevent_multiple_submissions' ) ) {
			// Always allow access to already completed submissions.
			if ( $submission && 'completed' === $submission->status ) {
				return true;
			}

			$identification_modes = $this->get_form_option( $form->id, 'identification_modes', array() );

			// Back-compat: Check for whether an old cookie is still set.
			if ( in_array( 'cookie', $identification_modes, true ) && isset( $_COOKIE[ 'torro_has_participated_form_' . $form->id ] ) && 'yes' === $_COOKIE[ 'torro_has_participated_form_' . $form->id ] ) {
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
				if ( in_array( 'ip_address', $identification_modes, true ) && ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
					$validated_ip = Fixes::php_filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP );
					if ( ! empty( $validated_ip ) ) {
						$identification_args['remote_addr'] = $validated_ip;
					}
				}
				if ( in_array( 'cookie', $identification_modes, true ) && filter_has_var( INPUT_COOKIE, 'torro_identity' ) ) {
					$identification_args['user_key'] = esc_attr( Fixes::php_filter_input( INPUT_COOKIE, 'torro_identity' ) );
				} elseif ( isset( $_SESSION ) && ! empty( $_SESSION['torro_identity'] ) ) {
					$identification_args['user_key'] = esc_attr( wp_unslash( $_SESSION['torro_identity'] ) );
				}
				if ( ! empty( $identification_args ) ) {
					$query_args['user_identification'] = $identification_args;
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
	 *
	 * @param Submission $submission New submission object.
	 * @param Form       $form       Form object the submission belongs to.
	 * @param array      $data       Submission POST data.
	 */
	public function set_submission_data( $submission, $form, $data ) {
		$identification_modes = $this->get_form_option( $form->id, 'identification_modes', array() );

		if ( in_array( 'ip_address', $identification_modes, true ) ) {
			$validated_ip = Fixes::php_filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP );
			if ( ! empty( $validated_ip ) ) {
				$submission->remote_addr = $validated_ip;
			}
		}

		if ( in_array( 'cookie', $identification_modes, true ) ) {
			if ( ! isset( $_COOKIE['torro_identity'] ) ) {
				setcookie( 'torro_identity', $submission->user_key, current_time( 'timestamp' ) + 3 * YEAR_IN_SECONDS );
			}
		}
	}

	/**
	 * Returns the available meta fields for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$meta_fields = parent::get_meta_fields();

		unset( $meta_fields['enabled'] );

		$meta_fields['prevent_edit_others_submission'] = array(
			'type'         => 'checkbox',
			'label'        => __( 'Prevent users from accessing someone else&apos;s submission?', 'torro-forms' ),
			'description'  => __( 'Click the checkbox to ensure that participants cannot access other participants&apos; submissions.', 'torro-forms' ),
			'default'      => true,
			'wrap_classes' => array( 'has-torro-tooltip-description' ),
			'visual_label' => __( 'User per submission', 'torro-forms' ),
		);
		$meta_fields['prevent_multiple_submissions']   = array(
			'type'         => 'checkbox',
			'label'        => __( 'Prevent multiple submissions by a single user?', 'torro-forms' ),
			'description'  => __( 'Click the checkbox to ensure that participants may only submit this form once.', 'torro-forms' ),
			'wrap_classes' => array( 'has-torro-tooltip-description' ),
			'visual_label' => __( 'Single submission', 'torro-forms' ),
		);
		$meta_fields['identification_modes']           = array(
			'type'        => 'multibox',
			'label'       => __( 'Identification Modes', 'torro-forms' ),
			'description' => __( 'For non logged-in users, by default PHP sessions are used to identity them. You can enable further modes here to improve accuracy.', 'torro-forms' ),
			'choices'     => array(
				'ip_address' => __( 'IP address', 'torro-forms' ),
				'cookie'     => __( 'Cookie', 'torro-forms' ),
			),
		);
		$meta_fields['already_submitted_message']      = array(
			'type'          => 'text',
			'label'         => __( '&#8220;Already submitted&#8221; Message', 'torro-forms' ),
			'description'   => __( 'Enter the message to show to the user when they have already submitted this form.', 'torro-forms' ),
			'default'       => $this->get_default_already_submitted_message(),
			'input_classes' => array( 'regular-text' ),
			'wrap_classes'  => array( 'has-torro-tooltip-description' ),
			'dependencies'  => array(
				array(
					'prop'     => 'display',
					'callback' => 'get_data_by_condition_true',
					'fields'   => array( 'prevent_multiple_submissions' ),
					'args'     => array(),
				),
			),
		);

		return $meta_fields;
	}

	/**
	 * Returns the default message to display when the user is not logged in.
	 *
	 * @since 1.0.0
	 *
	 * @return string Message to display.
	 */
	protected function get_default_already_submitted_message() {
		return __( 'You already submitted this form.', 'torro-forms' );
	}
}
