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
	 */
	protected function bootstrap() {
		$this->slug  = 'submission_count';
		$this->title = __( 'Submission Count', 'torro-forms' );
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
		$limit = (int) $this->get_form_option( $form->id, 'total_submissions_limit', 100 );

		$submissions = $form->get_submissions(
			array(
				'number' => $limit,
				'fields' => 'ids',
				'status' => 'completed',
			)
		);
		if ( count( $submissions ) >= $limit ) {
			$message = $this->get_form_option( $form->id, 'total_submissions_reached_message' );
			if ( empty( $message ) ) {
				$message = $this->get_default_total_submissions_reached_message();
			}

			return new WP_Error( 'total_submissions_reached', $message );
		}

		return true;
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

		$meta_fields['total_submissions_limit']           = array(
			'type'         => 'number',
			'label'        => __( 'Total Submissions Limit', 'torro-forms' ),
			'description'  => __( 'Enter the total amount of submissions that are allowed to be submitted for this form.', 'torro-forms' ),
			'min'          => 0,
			'step'         => 1,
			'default'      => 100,
			'wrap_classes' => array( 'has-torro-tooltip-description' ),
		);
		$meta_fields['total_submissions_reached_message'] = array(
			'type'          => 'text',
			'label'         => __( '&#8220;Total submissions reached&#8221; Message', 'torro-forms' ),
			'description'   => __( 'Enter the message to show to the user when a sufficient amount of submissions have already been completed.', 'torro-forms' ),
			'default'       => $this->get_default_total_submissions_reached_message(),
			'input_classes' => array( 'regular-text' ),
			'wrap_classes'  => array( 'has-torro-tooltip-description' ),
		);

		return $meta_fields;
	}

	/**
	 * Returns the default message to display when the total amount of submissions has been reached.
	 *
	 * @since 1.0.0
	 *
	 * @return string Message to display.
	 */
	protected function get_default_total_submissions_reached_message() {
		return __( 'This form is no longer open to submissions since the maximum number of submissions has been reached.', 'torro-forms' );
	}
}
