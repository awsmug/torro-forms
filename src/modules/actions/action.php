<?php
/**
 * Action base class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Actions;

use awsmug\Torro_Forms\Modules\Submodule;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Trait;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;


/**
 * Base class for an action.
 *
 * @since 1.0.0
 */
abstract class Action extends Submodule implements Settings_Submodule_Interface {
	use Settings_Submodule_Trait;

	/**
	 * Handles the action for a specific form submission.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Form       $form       Form the submission applies to.
	 * @param Submission $submission Submission to handle by the action.
	 */
	public abstract function handle( $form, $submission );
}
