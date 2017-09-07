<?php
/**
 * Bar charts evaluator class
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
 * Class for an evaluator using bar charts for individual elements.
 *
 * @since 1.0.0
 */
class Bar_Charts extends Evaluator implements Assets_Submodule_Interface {

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function bootstrap() {
		$this->slug        = 'barcharts';
		$this->title       = __( 'Bar Charts', 'torro-forms' );
		$this->description = __( 'Evaluates individual element responses using bar charts.', 'torro-forms' );
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
		$stats = $this->get_stats( $form->id );

		$elements = $form->get_child_manager( 'containers' )->get_child_manager( 'elements' );

		foreach ( $submission->get_submission_values() as $submission_value ) {
			$element = $elements->get( $submission_value->element_id );
			if ( ! $element ) {
				continue;
			}

			$element_type = $element->get_element_type();
			if ( ! $element_type ) {
				continue;
			}

			if ( ! is_a( $element_type, Choice_Element_Type_Interface::class ) ) {
				continue;
			}

			if ( ! isset( $stats[ $submission_value->element_id ] ) ) {
				$stats[ $submission_value->element_id ] = array();
			}

			$field = ! empty( $submission_value->field ) ? $submission_value->field : '_main';
			if ( ! isset( $stats[ $submission_value->element_id ][ $field ] ) ) {
				$stats[ $submission_value->element_id ][ $field ] = array();
			}

			if ( ! isset( $stats[ $submission_value->element_id ][ $field ][ $submission_value->value ] ) ) {
				$stats[ $submission_value->element_id ][ $field ][ $submission_value->value ] = 1;
			} else {
				$stats[ $submission_value->element_id ][ $field ][ $submission_value->value ]++;
			}
		}

		if ( ! empty( $stats ) ) {
			$this->update_stats( $form->id, $stats );
		}

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
