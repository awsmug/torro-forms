<?php
/**
 * Element responses evaluator class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Evaluators;

use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use awsmug\Torro_Forms\Modules\Assets_Submodule_Interface;
use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Choice_Element_Type_Interface;
use WP_Error;

/**
 * Class for an evaluator for individual element responses.
 *
 * @since 1.0.0
 */
class Element_Responses extends Evaluator implements Assets_Submodule_Interface {

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function bootstrap() {
		$this->slug        = 'element_responses';
		$this->title       = __( 'Element Responses', 'torro-forms' );
		$this->description = __( 'Evaluates individual element responses.', 'torro-forms' );
	}

	/**
	 * Evaluates a specific form submission.
	 *
	 * This method is run whenever a submission is completed to update the aggregate calculations.
	 * Aggregate calculations are stored so that forms with a very high number of submissions do
	 * not need to be calculated live.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array      $aggregate_results Aggregate results to update.
	 * @param Submission $submission        Submission to evaluate.
	 * @param Form       $form              Form the submission applies to.
	 * @return array Updated aggregate evaluation results.
	 */
	public function evaluate_single( $aggregate_results, $submission, $form ) {
		foreach ( $submission->get_submission_values() as $submission_value ) {
			if ( ! $this->is_element_evaluatable( $submission_value->element_id ) ) {
				continue;
			}

			if ( ! isset( $aggregate_results[ $submission_value->element_id ] ) ) {
				$aggregate_results[ $submission_value->element_id ] = array();
			}

			$field = ! empty( $submission_value->field ) ? $submission_value->field : '_main';
			if ( ! isset( $aggregate_results[ $submission_value->element_id ][ $field ] ) ) {
				$aggregate_results[ $submission_value->element_id ][ $field ] = array();
			}

			if ( ! isset( $aggregate_results[ $submission_value->element_id ][ $field ][ $submission_value->value ] ) ) {
				$aggregate_results[ $submission_value->element_id ][ $field ][ $submission_value->value ] = 1;
			} else {
				$aggregate_results[ $submission_value->element_id ][ $field ][ $submission_value->value ]++;
			}
		}

		return $aggregate_results;
	}

	/**
	 * Renders evaluation results for a specific form.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $results Results to show.
	 * @param Form  $form    Form the results belong to.
	 */
	public function show_results( $results, $form ) {
		// TODO.
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
		// Empty method body.
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
	 */
	public function enqueue_submission_results_assets( $assets, $form ) {
		$assets->enqueue_script( 'c3' );
		$assets->enqueue_style( 'c3' );
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
