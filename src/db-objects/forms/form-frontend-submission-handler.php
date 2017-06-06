<?php
/**
 * Form frontend submission handler class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Forms;

use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;

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
	 * @access protected
	 * @var Form_Manager
	 */
	protected $form_manager;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
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
	 * @access public
	 */
	public function maybe_handle_form_submission() {
		if ( ! isset( $_POST['torro_submission'] ) ) {
			return;
		}

		$data = wp_unslash( $_POST['torro_submission'] );

		$form = $submission = null;
		if ( ! empty( $data['id'] ) ) {
			$submission = $this->form_manager->get_child_manager( 'submissions' )->get( absint( $data['id'] ) );
			if ( $submission && ! empty( $submission->form_id ) ) {
				$form = $submission->get_form();
			}
		}

		if ( ! $form ) {
			if ( empty( $data['form_id'] ) ) {
				return;
			}

			$form = $this->form_manager->get( absint( $data['form_id'] ) );
		}

		if ( ! $submission ) {
			$submission = $this->form_manager->get_child_manager( 'submissions' )->create();

			$submission->form_id = $form->id;
			$submission->status  = 'processing';
		}

		$this->handle_form_submission( $form, $submission, $data );

		$redirect_url = ! empty( $data['original_id'] ) ? get_permalink( absint( $data['original_id'] ) ) : get_permalink( $form->id );
		if ( ! empty( $submission->id ) ) {
			$redirect_url = add_query_arg( 'torro_submission_id', $submission->id, $redirect_url );
		}

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Handles a form submission.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Form       $form       Form object.
	 * @param Submission $submission Submission object.
	 * @param array      $data       Submission POST data.
	 */
	protected function handle_form_submission( $form, $submission, $data = array() ) {
		if ( ! isset( $data['nonce'] ) ) {
			$submission->add_error( 0, 'missing_nonce', __( 'Missing security nonce.', 'torro-forms' ) );
			$submission->sync_upstream();
			return;
		}

		if ( ! wp_verify_nonce( $data['nonce'], $this->get_nonce_action( $form, $submission ) ) ) {
			$submission->add_error( 0, 'invalid_nonce', __( 'Invalid security nonce.', 'torro-forms' ) );
			$submission->sync_upstream();
			return;
		}

		$container = $submission->get_current_container();
		if ( ! $container ) {
			$submission->add_error( 0, 'internal_error_container', __( 'Internal error: No current container is available for this form.', 'torro-forms' ) );
			$submission->sync_upstream();
			return;
		}

		//TODO: Do action for further checks here.

		$submission->sync_upstream();

		$validated = array();
		foreach ( $container->get_elements() as $element ) {
			$fields = isset( $data['values'][ $element->id ] ) ? (array) $data['values'][ $element_id ] : array();

			$validated[ $element->id ] = $element->validate_fields( $fields );
		}

		$submission_values = $submission->get_submission_values();
		foreach ( $submission_values as $submission_value ) {
			if ( ! isset( $validated[ $submission_value->element_id ] ) ) {
				continue;
			}

			$field = $submission_value->field;
			if ( empty( $field ) ) {
				$field = '_main';
			}

			if ( ! isset( $validated[ $submission_value->element_id ][ $field ] ) ) {
				continue;
			}

			$value = $validated[ $submission_value->element_id ][ $field ];

			if ( is_wp_error( $value ) ) {
				$submission->add_error( $submission_value->element_id, $value->get_error_code(), $value->get_error_message() );
				$error_data = $value->get_error_data();
				if ( is_array( $error_data ) && isset( $error_data['validated_value'] ) ) {
					$submission_value->value = $error_data['validated_value'];
					$submission_value->sync_upstream();
				}
			} else {
				$submission_value->value = $value;
				$submission_value->sync_upstream();
			}

			unset( $validated[ $submission_value->element_id ][ $field ] );
		}

		foreach ( $validated as $element_id => $values ) {
			foreach ( $values as $name => $value ) {
				if ( is_wp_error( $value ) ) {
					$submission->add_error( $element_id, $value->get_error_code(), $value->get_error_message() );
					$error_data = $value->get_error_data();
					if ( is_array( $error_data ) && isset( $error_data['validated_value'] ) ) {
						$submission_value = $this->form_manager->get_child_manager( 'submissions' )->get_child_manager( 'submission_values' )->create();
						$submission_value->submission_id = $submission->id;
						$submission_value->element_id    = $element_id;
						if ( '_main' !== $name ) {
							$submission_value->field = $name;
						}
						$submission_value->value = $error_data['validated_value'];
						$submission_value->sync_upstream();
					}
				} else {
					$submission_value = $this->form_manager->get_child_manager( 'submissions' )->get_child_manager( 'submission_values' )->create();
					$submission_value->submission_id = $submission->id;
					$submission_value->element_id    = $element_id;
					if ( '_main' !== $name ) {
						$submission_value->field = $name;
					}
					$submission_value->value = $value;
					$submission_value->sync_upstream();
				}
			}
		}

		//TODO: Check if there is a next container. If there are no errors, either set the next container or set the status to completed and unset the current container. Plus trigger hooks.

		$submission->sync_upstream();
	}

	/**
	 * Returns the name of the nonce action to check.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Form       $form       Form object.
	 * @param Submission $submission Submission object.
	 * @return string Nonce action name.
	 */
	protected function get_nonce_action( $form, $submission ) {
		if ( ! empty( $submission->id ) ) {
			return $this->form_manager->get_prefix() . 'form_' . $form->id . '_submission_' . $submission->id;
		}

		return $this->form_manager->get_prefix() . 'form_' . $form->id;
	}
}
