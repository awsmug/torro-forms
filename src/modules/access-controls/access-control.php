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

/**
 * Base class for an access control.
 *
 * @since 1.0.0
 */
abstract class Access_Control extends Submodule implements Settings_Submodule_Interface {
	use Settings_Submodule_Trait;

	/**
	 * Determines whether the current user can access a specific form or submission.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Submission object, or null if no submission is set.
	 */
	public abstract function can_access( $form, $submission = null );
}
