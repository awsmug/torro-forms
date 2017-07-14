<?php
/**
 * Access control base class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Access_Controls;

use awsmug\Torro_Forms\Modules\Submodule;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Trait;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Trait;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use awsmug\Torro_Forms\Error;

/**
 * Base class for an access control.
 *
 * @since 1.0.0
 */
class Timerange extends Access_Control {

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function bootstrap() {
		$this->slug        = 'timerange';
		$this->title       = __( 'Timerange', 'torro-forms' );
		$this->description = __( 'Allows you to specific a timerange in which this form is available.', 'torro-forms' );
	}

	/**
	 * Determines whether the current user can access a specific form or submission.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Submission object, or null if no submission is set.
	 * @return bool|Error True if the form or submission can be accessed, false or error object otherwise.
	 */
	public function can_access( $form, $submission = null ) {
		$start = $this->get_form_option( $form->id, 'start' );
		$end   = $this->get_form_option( $form->id, 'end' );

		$now = current_time( 'timestamp' );

		if ( $start && $now < strtotime( $start ) ) {
			return new Error( 'form_not_yet_open', __( 'This form is currently not accessible.', 'torro-forms' ) );
		}

		if ( $end && $now > strtotime( $end ) ) {
			return new Error( 'form_not_yet_open', __( 'This form is currently not accessible.', 'torro-forms' ) );
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

		$meta_fields['start'] = array(
			'type'        => 'datetime',
			'label'       => __( 'Start Date', 'torro-forms' ),
			'description' => __( 'Select the date this form should be opened.', 'torro-forms' ),
			'store'       => 'datetime',
		);
		$meta_fields['end'] = array(
			'type'        => 'datetime',
			'label'       => __( 'End Date', 'torro-forms' ),
			'description' => __( 'Select the date this form should be closed.', 'torro-forms' ),
			'store'       => 'datetime',
		);

		//TODO: back-compat with 'start_date' and 'end_date' (both timestamps); if set, set 'enabled' too

		return $meta_fields;
	}
}
