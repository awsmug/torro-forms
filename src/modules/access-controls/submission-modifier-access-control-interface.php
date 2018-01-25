<?php
/**
 * Interface for submission modifier access controls
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Access_Controls;

/**
 * Interface for an access control that modifies submission data.
 *
 * @since 1.0.0
 */
interface Submission_Modifier_Access_Control_Interface {

	/**
	 * Sets additional data for a submission when it is created.
	 *
	 * @since 1.0.0
	 *
	 * @param Submission $submission New submission object.
	 * @param Form       $form       Form object the submission belongs to.
	 * @param array      $data       Submission POST data.
	 */
	public function set_submission_data( $submission, $form, $data );
}
