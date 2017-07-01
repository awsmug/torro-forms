<?php
/**
 * Access control base class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Access_Controls;

use awsmug\Torro_Forms\Modules\Submodule;
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
abstract class Access_Control extends Submodule implements Settings_Submodule_Interface {
	use Settings_Submodule_Trait;

	/**
	 * Checks whether the access control is enabled for a specific form.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool True if the access control is enabled, false otherwise.
	 */
	public function enabled( $form ) {
		//TODO: Manage this through meta.
		return true;
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
	public abstract function can_access( $form, $submission = null );
}
