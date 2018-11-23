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
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission_Collection;
use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Choice_Element_Type_Interface;
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
	 * Temporary storage to cache whether specific elements are evaluatable for the current request.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $evaluatable_elements = array();

	/**
	 * Checks whether the evaluator is enabled for a specific form.
	 *
	 * @since 1.0.0
	 *
	 * @param Form $form Form object to check.
	 * @return bool True if the evaluator is enabled, false otherwise.
	 */
	public function enabled( $form ) {
		return $this->get_form_option( $form->id, 'enabled', false );
	}

	/**
	 * Gets aggregate form statistics for the evaluator.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id Form ID.
	 * @return array Array of statistics, or empty array if nothing set yet.
	 */
	public function get_stats( $form_id ) {
		$stats = $this->module->manager()->meta()->get( 'post', $form_id, $this->module->manager()->get_prefix() . 'stats', true );
		if ( ! is_array( $stats ) ) {
			return array();
		}

		$stats_slug = $this->get_meta_identifier();
		if ( ! isset( $stats[ $stats_slug ] ) ) {
			return array();
		}

		return $stats[ $stats_slug ];
	}

	/**
	 * Updates aggregate form statistics for the evaluator.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $form_id Form ID.
	 * @param array $data    Array of statistics.
	 * @return bool True on success, false on failure.
	 */
	public function update_stats( $form_id, $data ) {
		$stats = $this->module->manager()->meta()->get( 'post', $form_id, $this->module->manager()->get_prefix() . 'stats', true );
		if ( ! is_array( $stats ) ) {
			$stats = array();
		}

		$stats_slug = $this->get_meta_identifier();

		$stats[ $stats_slug ] = $data;

		return (bool) $this->module->manager()->meta()->update( 'post', $form_id, $this->module->manager()->get_prefix() . 'stats', $stats );
	}

	/**
	 * Evaluates a specific form submission.
	 *
	 * This method is run whenever a submission is completed to update the aggregate calculations.
	 * Aggregate calculations are stored so that forms with a very high number of submissions do
	 * not need to be calculated live.
	 *
	 * @since 1.0.0
	 *
	 * @param array      $aggregate_results Aggregate results to update.
	 * @param Submission $submission        Submission to evaluate.
	 * @param Form       $form              Form the submission applies to.
	 * @return array Updated aggregate evaluation results.
	 */
	abstract public function evaluate_single( $aggregate_results, $submission, $form );

	/**
	 * Evaluates multiple specific form submissions.
	 *
	 * Unlike the evaluate() method, this method is used to evaluate the passed form submissions
	 * live. They aggregate results array should however be updated with the evaluation results
	 * in the same way as in evaluate().
	 *
	 * @since 1.0.0
	 *
	 * @param array                 $aggregate_results Aggregate results to update.
	 * @param Submission_Collection $submissions       Submission collection to evaluate.
	 * @param Form                  $form              Form the submission applies to.
	 * @return array Updated aggregate evaluation results.
	 */
	public function evaluate_multiple( $aggregate_results, $submissions, $form ) {
		foreach ( $submissions as $submission ) {
			$aggregate_results = $this->evaluate_single( $aggregate_results, $submission, $form );
		}

		return $aggregate_results;
	}

	/**
	 * Evaluates all (completed) form submissions for a form.
	 *
	 * If the number of submissions for the form is very high, stored aggregate results will be used.
	 * Otherwise the evaluation results will be calculated live.
	 *
	 * @since 1.0.0
	 *
	 * @param Form $form Form for which to evaluate all submissions.
	 * @return array Evaluation results for all completed form submissions.
	 */
	public function evaluate_all( $form ) {
		if ( $this->should_use_aggregate_calculations( $form ) ) {
			return $this->get_stats( $form->id );
		}

		$submissions = $form->get_submissions(
			array(
				'status' => 'completed',
			)
		);

		return $this->evaluate_multiple( array(), $submissions, $form );
	}

	/**
	 * Renders evaluation results for a specific form.
	 *
	 * @since 1.0.0
	 *
	 * @param array $results Results to show.
	 * @param Form  $form    Form the results belong to.
	 * @param array $args    Optional. Additional arguments for displaying the results, which depend on the respective evaluator.
	 *                       Default empty array.
	 */
	abstract public function show_results( $results, $form, $args = array() );

	/**
	 * Returns the available meta fields for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$meta_fields = $this->_get_meta_fields();

		$meta_fields['enabled'] = array(
			'type'         => 'checkbox',
			'label'        => _x( 'Enable?', 'evaluator', 'torro-forms' ),
			'visual_label' => _x( 'Status', 'evaluator', 'torro-forms' ),
		);

		return $meta_fields;
	}

	/**
	 * Renders sub-tabs in the evaluation area.
	 *
	 * @since 1.0.0
	 *
	 * @param array $tabs Associative array where each key is a sub-tab slug and each value is an array with a
	 *                    'label' key and 'callback' key.
	 */
	protected function display_tabs( $tabs ) {
		$prefix = 'evaluations-tab-' . $this->slug . '-subtab';

		?>
		<div class="torro-evaluations-subtabs" role="tablist">
			<?php $first = true; ?>
			<?php foreach ( $tabs as $tab_slug => $tab_data ) : ?>
				<a id="<?php echo esc_attr( $prefix . '-label-' . $tab_slug ); ?>" class="torro-evaluations-subtab" href="<?php echo esc_attr( '#' . $prefix . '-' . $tab_slug ); ?>" aria-controls="<?php echo esc_attr( $prefix . '-' . $tab_slug ); ?>" aria-selected="<?php echo $first ? 'true' : 'false'; ?>" role="tab">
					<?php echo esc_html( $tab_data['label'] ); ?>
				</a>
				<?php $first = false; ?>
			<?php endforeach; ?>
		</div>
		<div class="torro-evaluations-subcontent">
			<?php $first = true; ?>
			<?php foreach ( $tabs as $tab_slug => $tab_data ) : ?>
				<div id="<?php echo esc_attr( $prefix . '-' . $tab_slug ); ?>" class="torro-evaluations-subtab-panel" aria-labelledby="<?php echo esc_attr( $prefix . '-label-' . $tab_slug ); ?>" aria-hidden="<?php echo $first ? 'false' : 'true'; ?>" role="tabpanel">
					<?php call_user_func( $tab_data['callback'] ); ?>
				</div>
				<?php $first = false; ?>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Checks whether aggregate calculations should be used for evaluating all submissions of a specific form.
	 *
	 * @since 1.0.0
	 *
	 * @param Form $form Form for which to check this.
	 * @return bool True if aggregate calculations should be used, false otherwise.
	 */
	protected function should_use_aggregate_calculations( $form ) {
		$submission_count = $this->module->manager()->forms()->get_child_manager( 'submissions' )->count( 0, $form->id );

		/**
		 * Filters the breakpoint for when to use aggregate results for evaluating submissions.
		 *
		 * If the number of available completed submissions for a form is higher than the breakpoint, aggregate results will
		 * be used. Otherwise the calculations will be performed live.
		 *
		 * Powerful setups may increase the breakpoint as needed to get more accurate results for higher submission counts,
		 * as aggregate results may possibly be less precise than live calculations.
		 *
		 * @since 1.0.0
		 *
		 * @param int    $breakpoint Breakpoint count to use. Default is 100 submissions.
		 * @param string $slug       Slug of the evaluator for which the breakpoint is checked.
		 * @param Form   $form       Form for which the breakpoint is checked.
		 */
		$breakpoint = apply_filters( "{$this->module->manager()->get_prefix()}use_aggregate_calculations_breakpoint", 100, $this->slug, $form );

		return $submission_count['completed'] > $breakpoint;
	}

	/**
	 * Checks whether a specific element is evaluatable.
	 *
	 * An evaluatable element must implement the Choice_Element_Type_Interface interface.
	 *
	 * @since 1.0.0
	 *
	 * @param int $element_id Element ID to check.
	 * @return bool True if the element is evaluatable, false otherwise.
	 */
	protected function is_element_evaluatable( $element_id ) {
		if ( ! isset( $this->evaluatable_elements[ $element_id ] ) ) {
			$elements = $this->module->manager()->forms()->get_child_manager( 'containers' )->get_child_manager( 'elements' );

			$element = $elements->get( $element_id );
			if ( ! $element ) {
				$this->evaluatable_elements[ $element_id ] = false;
			} else {
				$element_type = $element->get_element_type();
				if ( ! $element_type || ! is_a( $element_type, Choice_Element_Type_Interface::class ) ) {
					$this->evaluatable_elements[ $element_id ] = false;
				} else {
					$this->evaluatable_elements[ $element_id ] = true;
				}
			}
		}

		return $this->evaluatable_elements[ $element_id ];
	}
}
