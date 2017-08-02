<?php
/**
 * Evaluator base class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Evaluators;

use awsmug\Torro_Forms\Modules\Submodule;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Trait;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Trait;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

/**
 * Base class for an evaluator.
 *
 * @since 1.0.0
 */
abstract class Evaluator extends Submodule implements Meta_Submodule_Interface, Settings_Submodule_Interface {
	use Meta_Submodule_Trait, Settings_Submodule_Trait {
		Meta_Submodule_Trait::get_meta_fields as protected _get_meta_fields;
	}

	/**
	 * Checks whether the evaluator is enabled for a specific form.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Form $form Form object to check.
	 * @return bool True if the evaluator is enabled, false otherwise.
	 */
	public function enabled( $form ) {
		return $this->get_form_option( $form->id, 'enabled', false );
	}

	/**
	 * Evaluates a specific form submission.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Submission $submission Submission to evaluate.
	 * @param Form       $form       Form the submission applies to.
	 * @return bool|WP_Error True on success, error object on failure.
	 */
	public abstract function evaluate( $submission, $form );

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

	/**
	 * Returns the available meta fields for the submodule.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$meta_fields = $this->_get_meta_fields();

		$meta_fields['enabled'] = array(
			'type'       => 'checkbox',
			'label'      => _x( 'Enable?', 'evaluator', 'torro-forms' ),
		);

		return $meta_fields;
	}
}
