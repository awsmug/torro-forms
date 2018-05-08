<?php
/**
 * Timerange access control class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Access_Controls;

use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

/**
 * Class for an access control to restrict based on a time range.
 *
 * @since 1.0.0
 */
class Timerange extends Access_Control {

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug        = 'timerange';
		$this->title       = __( 'Timerange', 'torro-forms' );
		$this->description = __( 'Allows you to specific a timerange in which this form is available.', 'torro-forms' );
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
		$start = $this->get_form_option( $form->id, 'start' );
		$end   = $this->get_form_option( $form->id, 'end' );

		if ( ! empty( $start ) && '0000-00-00 00:00:00' !== $start ) {
			return true;
		}

		if ( ! empty( $end ) && '0000-00-00 00:00:00' !== $end ) {
			return true;
		}

		return false;
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
		$start = $this->get_form_option( $form->id, 'start' );
		$end   = $this->get_form_option( $form->id, 'end' );

		$now = current_time( 'timestamp' );

		if ( ! empty( $start ) && '0000-00-00 00:00:00' !== $start && $now < strtotime( $start ) ) {
			$message = $this->get_form_option( $form->id, 'not_yet_open_message' );
			if ( empty( $message ) ) {
				$message = $this->get_default_not_yet_open_message();
			}

			return new WP_Error( 'form_not_yet_open', $message );
		}

		if ( ! empty( $end ) && '0000-00-00 00:00:00' !== $end && $now > strtotime( $end ) ) {
			$message = $this->get_form_option( $form->id, 'no_longer_open_message' );
			if ( empty( $message ) ) {
				$message = $this->get_default_no_longer_open_message();
			}

			return new WP_Error( 'form_no_longer_open', $message );
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

		unset( $meta_fields['enabled'] );

		$meta_fields['start']                  = array(
			'type'         => 'datetime',
			'label'        => __( 'Start Date', 'torro-forms' ),
			'description'  => __( 'Select the date this form should be opened.', 'torro-forms' ),
			'store'        => 'datetime',
			'wrap_classes' => array( 'has-torro-tooltip-description' ),
		);
		$meta_fields['end']                    = array(
			'type'         => 'datetime',
			'label'        => __( 'End Date', 'torro-forms' ),
			'description'  => __( 'Select the date this form should be closed.', 'torro-forms' ),
			'store'        => 'datetime',
			'wrap_classes' => array( 'has-torro-tooltip-description' ),
		);
		$meta_fields['not_yet_open_message']   = array(
			'type'          => 'text',
			'label'         => __( '&#8220;Not yet open&#8221; Message', 'torro-forms' ),
			'description'   => __( 'Enter the message to show to the user in case the form is not yet open to submissions.', 'torro-forms' ),
			'default'       => $this->get_default_not_yet_open_message(),
			'input_classes' => array( 'regular-text' ),
			'wrap_classes'  => array( 'has-torro-tooltip-description' ),
		);
		$meta_fields['no_longer_open_message'] = array(
			'type'          => 'text',
			'label'         => __( '&#8220;No longer open&#8221; Message', 'torro-forms' ),
			'description'   => __( 'Enter the message to show to the user in case the form is no longer open to submissions.', 'torro-forms' ),
			'default'       => $this->get_default_no_longer_open_message(),
			'input_classes' => array( 'regular-text' ),
			'wrap_classes'  => array( 'has-torro-tooltip-description' ),
		);

		return $meta_fields;
	}

	/**
	 * Returns the default message to display when the form is not yet open.
	 *
	 * @since 1.0.0
	 *
	 * @return string Message to display.
	 */
	protected function get_default_not_yet_open_message() {
		return __( 'This form is not yet open to submissions.', 'torro-forms' );
	}

	/**
	 * Returns the default message to display when the form has already been closed.
	 *
	 * @since 1.0.0
	 *
	 * @return string Message to display.
	 */
	protected function get_default_no_longer_open_message() {
		return __( 'This form is no longer open to submissions.', 'torro-forms' );
	}
}
