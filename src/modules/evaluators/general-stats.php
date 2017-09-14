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
		if ( ! isset( $aggregate_results['total'] ) ) {
			$aggregate_results['total'] = 1;
		} else {
			$aggregate_results['total']++;
		}

		$year  = (string) $submission->format_datetime( 'Y' );
		$month = (string) $submission->format_datetime( 'm' );

		if ( ! isset( $aggregate_results[ $year ] ) ) {
			$aggregate_results[ $year ] = array();
		}

		if ( ! isset( $aggregate_results[ $year ]['total'] ) ) {
			$aggregate_results[ $year ]['total'] = 1;
		} else {
			$aggregate_results[ $year ]['total']++;
		}

		if ( ! isset( $aggregate_results[ $year ][ $month ] ) ) {
			$aggregate_results[ $year ][ $month ] = 1;
		} else {
			$aggregate_results[ $year ][ $month ]++;
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
		$total_count = 0;
		if ( isset( $results['total'] ) ) {
			$total_count = $results['total'];
			unset( $results['total'] );
		}

		if ( ! empty( $results ) ) {
			ksort( $results );
			$keys = array_keys( $results );

			$min_year = $keys[0];
			$max_year = $keys[ count( $keys ) - 1 ];

			$years = array_map( 'strval', range( (int) $min_year, (int) $max_year, 1 ) );
		} else {
			$years = array( (string) current_time( 'Y' ) );
		}

		$tabs = array(
			'total' => array(
				'label'    => _x( 'Total', 'submission count', 'torro-forms' ),
				'callback' => function() use ( $results, $years, $total_count ) {
					$year_results = array();
					foreach ( $years as $year ) {
						if ( isset( $results[ $year ]['total'] ) ) {
							$year_results[] = (int) $results[ $year ]['total'];
						} else {
							$year_results[] = 0;
						}
					}

					?>
					<p>
						<strong>
							<?php _e( 'Number of completed submissions:', 'torro-forms' ); ?>
							<?php echo absint( $total_count ); ?>
						</strong>
					</p>
					<div id="<?php echo esc_attr( $this->slug . '-chart-total' ); ?>"></div>
					<script type="application/json" class="c3-chart-data">
						<?php echo json_encode( $this->get_chart_json( esc_attr( $this->slug . '-chart-total' ), $years, $year_results, __( 'Years', 'torro-forms' ), __( 'Submission Count', 'torro-forms' ) ) ); ?>
					</script>
					<?php
				},
			),
		);

		foreach ( $years as $year ) {
			$tabs[ $year ] = array(
				'label'    => $year,
				'callback' => function() use ( $results, $year ) {
					global $wp_locale;

					$total_count = isset( $results[ $year ]['total'] ) ? $results[ $year ]['total'] : 0;

					$months = $wp_locale->month;

					$month_results = array();
					foreach ( $months as $index => $month ) {
						if ( isset( $results[ $year ][ $index ] ) ) {
							$month_results[] = (int) $results[ $year ][ $index ];
						} else {
							$month_results[] = 0;
						}
					}

					?>
					<p>
						<strong>
							<?php
							/* translators: %s: a year */
							printf( __( 'Number of completed submissions in %s:', 'torro-forms' ), $year );
							?>
							<?php echo absint( $total_count ); ?>
						</strong>
					</p>
					<div id="<?php echo esc_attr( $this->slug . '-chart-' . $year ); ?>"></div>
					<script type="application/json" class="c3-chart-data">
						<?php echo json_encode( $this->get_chart_json( esc_attr( $this->slug . '-chart-' . $year ), array_values( $months ), $month_results, __( 'Months', 'torro-forms' ), __( 'Submission Count', 'torro-forms' ) ) ); ?>
					</script>
					<?php
				},
			);
		}

		$this->display_tabs( $tabs );
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

	protected function get_chart_json( $id, $x_values, $y_values, $x_label, $y_label ) {
		$less_than_10 = true;
		foreach ( $y_values as $y_value ) {
			if ( $y_value > 10 ) {
				$less_than_10 = false;
				break;
			}
		}

		array_unshift( $x_values, 'x' );
		array_unshift( $y_values, 'submissionCount' );

		$data = array(
			'bindto' => '#' . $id,
			'data'   => array(
				'x'       => 'x',
				'columns' => array( $x_values, $y_values ),
				'names'   => array(
					'submissionCount' => $y_label,
				),
				'type'    => 'line',
				'colors'  => array(
					'submissionCount' => '#0073aa',
				),
			),
			'axis'   => array(
				'x' => array(
					'label' => $x_label,
					'type' => 'category',
				),
				'y' => array(
					'label' => $y_label,
					'tick'  => array(
						'time' => array(
							'interval' => 1,
						),
					),
					'min'   => 1,
				),
			),
			'legend' => array(
				'show' => false,
			),
		);
		if ( $less_than_10 ) {
			$data['axis']['y']['max'] = 10;
		}

		return $data;
	}
}
