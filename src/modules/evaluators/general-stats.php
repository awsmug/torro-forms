<?php
/**
 * General stats evaluator class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Evaluators;

use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use awsmug\Torro_Forms\Modules\Assets_Submodule_Interface;
use WP_Error;

/**
 * Class for an evaluator that measures some general form stats.
 *
 * @since 1.0.0
 */
class General_Stats extends Evaluator implements Assets_Submodule_Interface {

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function bootstrap() {
		$this->slug        = 'generalstats';
		$this->title       = __( 'General Stats', 'torro-forms' );
		$this->description = __( 'Creates general stats on submissions for a form.', 'torro-forms' );
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
	public function evaluate( $submission, $form ) {
		// TODO.

		return true;
	}

	/**
	 * Renders evaluation results for a specific form.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Form  $form Form to show results for.
	 * @param array $args Arguments to tweak the displayed results.
	 */
	public function show_results( $form, $args = array() ) {

	}

	/**
	 * Registers all assets the submodule provides.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Assets $assets The plugin assets instance.
	 */
	public function register_assets( $assets ) {

	}

	/**
	 * Enqueues scripts and stylesheets on the form editing screen.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Assets $assets The plugin assets instance.
	 */
	public function enqueue_form_builder_assets( $assets ) {
		// Empty method body.
	}

	/**
	 * Enqueues scripts and stylesheets on the submissions list table view.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Assets $assets The plugin assets instance.
	 * @param Form   $form   Form to show results for.
	 * @param array  $args   Arguments to tweak the displayed results.
	 */
	public function enqueue_submission_results_assets( $assets, $form, $args = array() ) {

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
		return $this->get_form_option( $form->id, 'enabled', true );
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
		$meta_fields = $this->_get_meta_fields();

		$meta_fields['enabled'] = array(
			'type'    => 'checkbox',
			'label'   => _x( 'Enable?', 'evaluator', 'torro-forms' ),
			'default' => true,
		);

		return $meta_fields;
	}
}
