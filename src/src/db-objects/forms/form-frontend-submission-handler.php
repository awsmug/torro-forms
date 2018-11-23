<?php
/**
 * Form frontend submission handler class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Forms;

use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use awsmug\Torro_Forms\DB_Objects\Submission_Values\Submission_Value;
use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Element_Type;
use WP_Error;

/**
 * Class for handling form frontend submissions.
 *
 * @since 1.0.0
 */
class Form_Frontend_Submission_Handler {

	/**
	 * Form manager instance.
	 *
	 * @since 1.0.0
	 * @var Form_Manager
	 */
	protected $form_manager;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Form_Manager $form_manager Form manager instance.
	 */
	public function __construct( $form_manager ) {
		$this->form_manager = $form_manager;
	}

	/**
	 * Handles a form submission and redirects back if conditions are met.
	 *
	 * @since 1.0.0
	 */
	public function maybe_handle_form_submission() {
		if ( ! isset( $_POST['torro_submission'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
			return;
		}

		$data = wp_unslash( $_POST['torro_submission'] ); // phpcs:ignore WordPress.Security

		$context = $this->detect_request_form_and_submission( $data );
		if ( is_wp_error( $context ) ) {
			// Always die when one of these strange errors happens.
			wp_die( wp_kses_data( $context->get_error_message() ), esc_html__( 'Form Submission Error', 'torro-forms' ), 400 );
		}

		$form       = $context['form'];
		$submission = $context['submission'];

		$verified = $this->verify_request( $data, $form, $submission );
		if ( ! $verified || is_wp_error( $verified ) ) {
			if ( ! $verified ) {
				$verified = new WP_Error( 'cannot_verify_request', __( 'The request could not be verified.', 'torro-forms' ) );
			}

			// Die only if the form error could not be set.
			if ( ! $this->set_form_error( $form, $verified ) ) {
				wp_die( wp_kses_data( $verified->get_error_message() ), esc_html__( 'Form Submission Error', 'torro-forms' ), 403 );
			}
		} else {
			if ( ! $submission ) {
				$submission = $this->create_new_submission( $form, $data );
			}

			$this->handle_form_submission( $form, $submission, $data );
		}

		$redirect_url = ! empty( $data['original_form_id'] ) ? get_permalink( absint( $data['original_form_id'] ) ) : get_permalink( $form->id );

		// Append submission ID.
		if ( $submission && ! empty( $submission->id ) ) {
			$redirect_url = add_query_arg( 'torro_submission_id', $submission->id, $redirect_url );
		}

		/**
		 * Filters the URL to redirect the user to after a form submission request has been processed.
		 *
		 * If a submission is applicable, its query variable will be appended.
		 *
		 * @since 1.0.0
		 *
		 * @param string     $redirect_url URL to redirect to. Default is the original form URL.
		 * @param Form       $form         Form object.
		 * @param Submission $submission   Submission object, or null if no submission.
		 */
		$redirect_url = apply_filters( "{$this->form_manager->get_prefix()}handle_form_submission_redirect_url", $redirect_url, $form, $submission );

		wp_redirect( $redirect_url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		exit;
	}

	/**
	 * Handles a form submission.
	 *
	 * @since 1.0.0
	 *
	 * @param Form       $form       Form object.
	 * @param Submission $submission Submission object.
	 * @param array      $data       Submission POST data.
	 */
	protected function handle_form_submission( $form, $submission, $data = array() ) {
		if ( $submission->has_errors() ) {
			$submission->reset_errors();
		}

		$container = $submission->get_current_container();
		if ( ! $container ) {
			$submission->add_error( 0, 'internal_error_container', __( 'Internal error: No current container is available for this form.', 'torro-forms' ) );
			$submission->sync_upstream();
			return;
		}

		$old_submission_status = $submission->status;

		$going_back = ! empty( $data['action'] ) && 'prev' === $data['action'];

		/**
		 * Fires before a form submission is handled.
		 *
		 * If you add one or more errors to the submission, the rest of the handling is skipped, essentially making the submission fail.
		 *
		 * @since 1.0.0
		 *
		 * @param Submission $submission Submission object.
		 * @param Form       $form       Form object.
		 * @param Container  $container  Current container object.
		 * @param array      $data       Submission POST data.
		 */
		do_action( "{$this->form_manager->get_prefix()}pre_handle_submission", $submission, $form, $container, $data );

		$submission->sync_upstream();

		if ( $submission->has_errors() ) {
			return;
		}

		$validated = array();
		foreach ( $container->get_elements() as $element ) {
			$fields = isset( $data['values'][ $element->id ] ) ? (array) $data['values'][ $element->id ] : array();

			$validation_result = $element->validate_fields( $fields, $submission );

			// When navigating back to the previous step, allow errors resulting of required fields to be skipped.
			if ( $going_back ) {
				$skip_validation = true;

				foreach ( $validation_result as $field => $result ) {
					if ( ! is_wp_error( $result ) ) {
						$skip_validation = false;
						break;
					}

					$non_required_errors = array_diff_key( $result->errors, array( Element_Type::ERROR_CODE_REQUIRED => true ) );
					if ( ! empty( $non_required_errors ) ) {
						$skip_validation = false;
						break;
					}
				}

				if ( ! $skip_validation ) {
					$validated[ $element->id ] = $validation_result;
				}

				continue;
			}

			$validated[ $element->id ] = $validation_result;
		}

		// Update existing submission values first.
		$submission_values = $submission->get_submission_values();
		foreach ( $submission_values as $submission_value ) {
			$field = $submission_value->field;
			if ( empty( $field ) ) {
				$field = '_main';
			}

			if ( ! isset( $validated[ $submission_value->element_id ][ $field ] ) ) {
				continue;
			}

			$result = $validated[ $submission_value->element_id ][ $field ];

			if ( is_wp_error( $result ) ) {
				$submission->add_error( $submission_value->element_id, $result->get_error_code(), $result->get_error_message() );

				$error_data = $result->get_error_data();
				if ( is_array( $error_data ) && isset( $error_data['validated_value'] ) ) {
					$this->update_submission_value( $submission_value, $error_data['validated_value'] );
				} else {
					$submission_value->delete();
				}

				unset( $validated[ $submission_value->element_id ][ $field ] );
				continue;
			}

			$result = (array) $result;

			$value = array_shift( $result );

			if ( empty( $result ) ) {
				unset( $validated[ $submission_value->element_id ][ $field ] );

				if ( empty( $validated[ $submission_value->element_id ] ) ) {
					unset( $validated[ $submission_value->element_id ] );
				}
			}

			if ( is_wp_error( $value ) ) {
				$submission->add_error( $submission_value->element_id, $value->get_error_code(), $value->get_error_message() );

				$error_data = $value->get_error_data();
				if ( is_array( $error_data ) && isset( $error_data['validated_value'] ) ) {
					$this->update_submission_value( $submission_value, $error_data['validated_value'] );
				} else {
					$submission_value->delete();
				}
			} else {
				$this->update_submission_value( $submission_value, $value );
			}
		}

		// Add the remaining results as new submission values.
		foreach ( $validated as $element_id => $values ) {
			foreach ( $values as $field => $result ) {
				if ( is_wp_error( $result ) ) {
					$submission->add_error( $element_id, $result->get_error_code(), $result->get_error_message() );

					$error_data = $result->get_error_data();
					if ( is_array( $error_data ) && isset( $error_data['validated_value'] ) ) {
						$this->insert_submission_value( $submission->id, $element_id, $field, $error_data['validated_value'] );
					}

					continue;
				}

				$result = (array) $result;

				foreach ( $result as $value ) {
					if ( is_wp_error( $value ) ) {
						$submission->add_error( $element_id, $value->get_error_code(), $value->get_error_message() );

						$error_data = $value->get_error_data();
						if ( is_array( $error_data ) && isset( $error_data['validated_value'] ) ) {
							$this->insert_submission_value( $submission->id, $element_id, $field, $error_data['validated_value'] );
						}
					} else {
						$this->insert_submission_value( $submission->id, $element_id, $field, $value );
					}
				}
			}
		}

		/**
		 * Fires when a form submission is handled.
		 *
		 * If you add one or more errors to the submission, this will make the submission fail.
		 *
		 * @since 1.0.0
		 *
		 * @param Submission $submission Submission object.
		 * @param Form       $form       Form object.
		 * @param Container  $container  Current container object.
		 * @param array      $data       Submission POST data.
		 */
		do_action( "{$this->form_manager->get_prefix()}handle_submission", $submission, $form, $container, $data );

		if ( $submission->has_errors() ) {
			$submission->sync_upstream();
			return;
		}

		if ( $going_back ) {
			$previous_container = $submission->get_previous_container();
			if ( $previous_container ) {
				$submission->set_current_container( $previous_container );
			} else {
				$submission->add_error( 0, 'internal_error_previous_container', __( 'Internal error: There is no previous container available.', 'torro-forms' ) );
			}
		} else {
			$next_container = $submission->get_next_container();
			if ( $next_container ) {
				$submission->set_current_container( $next_container );
			} else {
				$submission->set_current_container( null );
				$submission->status = 'completed';
			}
		}

		$submission->sync_upstream();

		if ( 'progressing' === $old_submission_status && 'completed' === $submission->status ) {
			/**
			 * Fires when a form submission is completed.
			 *
			 * At the point of this action, all submission data is already synchronized with the database
			 * and its status is set as 'completed'.
			 *
			 * @since 1.0.0
			 *
			 * @param Submission $submission Submission object.
			 * @param Form       $form       Form object.
			 */
			do_action( "{$this->form_manager->get_prefix()}complete_submission", $submission, $form );
		}
	}

	/**
	 * Detects the form and submission from the request.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Submission POST data.
	 * @return array|WP_Error Array with 'form' and 'submission' keys, or error object on failure.
	 */
	protected function detect_request_form_and_submission( $data ) {
		$form       = null;
		$submission = null;

		if ( ! empty( $data['id'] ) ) {
			$submission = $this->form_manager->get_child_manager( 'submissions' )->get( absint( $data['id'] ) );
			if ( $submission ) {
				if ( 'completed' === $submission->status ) {
					return new WP_Error( 'submission_already_completed', __( 'Submission already completed.', 'torro-forms' ) );
				}

				if ( ! empty( $submission->form_id ) ) {
					$form = $submission->get_form();
				}
			}
		}

		if ( ! $form ) {
			if ( empty( $data['form_id'] ) ) {
				return new WP_Error( 'cannot_detect_form', __( 'Could not detect form.', 'torro-forms' ) );
			}

			$form = $this->form_manager->get( absint( $data['form_id'] ) );
			if ( ! $form ) {
				return new WP_Error( 'cannot_detect_form', __( 'Could not detect form.', 'torro-forms' ) );
			}
		}

		return array(
			'form'       => $form,
			'submission' => $submission,
		);
	}

	/**
	 * Creates a new submission object.
	 *
	 * @since 1.0.0
	 *
	 * @param Form  $form Form object.
	 * @param array $data Submission POST data.
	 * @return Submission New submission object.
	 */
	protected function create_new_submission( $form, $data ) {
		$submission = $this->form_manager->get_child_manager( 'submissions' )->create();

		/**
		 * Fires when a new submission object has just been instantiated.
		 *
		 * This hook may be used to set additional unique data on a submission.
		 *
		 * @since 1.0.0
		 *
		 * @param Submission $submission Submission object.
		 * @param Form       $form       Form object.
		 * @param array      $data       Submission POST data.
		 */
		do_action( "{$this->form_manager->get_prefix()}create_new_submission", $submission, $form, $data );

		return $submission;
	}

	/**
	 * Inserts a new submission value into the database.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $submission_id ID of the submission the value belongs to.
	 * @param int    $element_id    ID of the element the value applies to.
	 * @param string $field         Slug of the field the value applies to.
	 * @param mixed  $value         Value to set.
	 * @return bool|WP_Error True on success, or error object on failure.
	 */
	protected function insert_submission_value( $submission_id, $element_id, $field, $value ) {
		$submission_value = $this->form_manager->get_child_manager( 'submissions' )->get_child_manager( 'submission_values' )->create();

		$submission_value->submission_id = $submission_id;
		$submission_value->element_id    = $element_id;
		if ( '_main' !== $field ) {
			$submission_value->field = $field;
		}
		$submission_value->value = $value;

		return $submission_value->sync_upstream();
	}

	/**
	 * Updates an existing submission value in the database.
	 *
	 * @since 1.0.1
	 *
	 * @param Submission_Value $submission_value Existing submission value to update.
	 * @param mixed            $value            New value to set.
	 * @return bool|WP_Error True on success, or error object on failure.
	 */
	protected function update_submission_value( $submission_value, $value ) {
		if ( is_array( $value ) ) {
			$submission_value->value = array_shift( $value );
			$result                  = $submission_value->sync_upstream();

			$field = $submission_value->field;
			if ( empty( $field ) ) {
				$field = '_main';
			}

			foreach ( $value as $val ) {
				$this->insert_submission_value( $submission_value->submission_id, $submission_value->element_id, $field, $val );
			}

			return $result;
		}

		$submission_value->value = $value;
		return $submission_value->sync_upstream();
	}

	/**
	 * Verifies the request.
	 *
	 * By default only the security nonce is checked. Further checks can be applied via a filter.
	 *
	 * @since 1.0.0
	 *
	 * @param array           $data       Submission POST data.
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Submission object, or null if a new submission.
	 * @return bool|WP_Error True if request is verified, error object otherwise.
	 */
	protected function verify_request( $data, $form, $submission = null ) {
		if ( ! isset( $data['nonce'] ) ) {
			return new WP_Error( 'missing_nonce', __( 'Missing security nonce.', 'torro-forms' ) );
		}

		if ( ! wp_verify_nonce( $data['nonce'], $this->get_nonce_action( $form, $submission ) ) ) {
			return new WP_Error( 'invalid_nonce', __( 'Invalid security nonce.', 'torro-forms' ) );
		}

		/**
		 * Filters the verification of a form submission request.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error   $verified   Either a boolean or an error object must be returned. Default true.
		 * @param array           $data       Submission POST data.
		 * @param Form            $form       Form object.
		 * @param Submission|null $submission Submission object, or null if a new submission.
		 */
		return apply_filters( "{$this->form_manager->get_prefix()}verify_form_submission_request", true, $data, $form, $submission );
	}

	/**
	 * Returns the name of the nonce action to check.
	 *
	 * @since 1.0.0
	 *
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Optional. Submission object, or null if none available. Default null.
	 * @return string Nonce action name.
	 */
	protected function get_nonce_action( $form, $submission = null ) {
		if ( $submission && ! empty( $submission->id ) ) {
			return $this->form_manager->get_prefix() . 'form_' . $form->id . '_submission_' . $submission->id;
		}

		return $this->form_manager->get_prefix() . 'form_' . $form->id;
	}

	/**
	 * Sets a form error so that it can be printed to the user in the next request.
	 *
	 * @since 1.0.0
	 *
	 * @param Form     $form  Form object.
	 * @param WP_Error $error Error object.
	 * @return bool True on success, false on failure.
	 */
	protected function set_form_error( $form, $error ) {
		$key = $this->form_manager->get_prefix() . 'form_errors';

		if ( is_user_logged_in() ) {
			$errors = get_user_meta( get_current_user_id(), $key, true );

			if ( ! is_array( $errors ) ) {
				$errors = array();
			}

			$errors[ $form->id ] = $error->get_error_message();

			return (bool) update_user_meta( get_current_user_id(), $key, $errors );
		}

		if ( ! isset( $_SESSION ) ) {
			if ( headers_sent() ) {
				return false;
			}

			session_start();
		}

		if ( ! isset( $_SESSION[ $key ] ) ) {
			$_SESSION[ $key ] = array();
		}

		$_SESSION[ $key ][ $form->id ] = $error->get_error_message();

		return true;
	}
}
