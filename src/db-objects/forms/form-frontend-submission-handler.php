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
			$submission->status  = 'progressing';
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

	}
}
