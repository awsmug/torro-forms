<?php
/**
 * Evaluator base class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Evaluators;

use awsmug\Torro_Forms\Modules\Submodule;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Trait;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;

/**
 * Base class for an evaluator.
 *
 * @since 1.0.0
 */
abstract class Evaluator extends Submodule implements Settings_Submodule_Interface {
	use Settings_Submodule_Trait;

	/**
	 * Evaluates a specific form submission.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Form       $form       Form the submission applies to.
	 * @param Submission $submission Submission to evaluate.
	 */
	public abstract function evaluate( $form, $submission );

	/**
	 * Renders evaluation results for a specific form.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Form  $form Form to show results for.
	 * @param array $args Arguments to tweak the displayed results.
	 */
	public abstract function show_results( $form, $args = array() );
}
