<?php
/**
 * Action base class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Actions;

use awsmug\Torro_Forms\Modules\Submodule;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Trait;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Trait;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use awsmug\Torro_Forms\Error;

/**
 * Base class for an action.
 *
 * @since 1.0.0
 */
abstract class Action extends Submodule implements Meta_Submodule_Interface, Settings_Submodule_Interface {
	use Meta_Submodule_Trait, Settings_Submodule_Trait;

	/**
	 * Checks whether the action is enabled for a specific form.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Form $form Form object to check.
	 * @return bool True if the action is enabled, false otherwise.
	 */
	public function enabled( $form ) {
		//TODO: Manage this through meta.
		return true;
	}

	/**
	 * Handles the action for a specific form submission.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Submission $submission Submission to handle by the action.
	 * @param Form       $form       Form the submission applies to.
	 * @return bool|Error True on success, error object on failure.
	 */
	public abstract function handle( $submission, $form );
}
