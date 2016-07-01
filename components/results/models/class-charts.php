<?php
/**
 * Components: Torro_Result_Charts class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.6
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Torro_Result_Charts extends Torro_Form_Result {
	/**
	 * Fegistered chart types (e.g. bars, pies, ...)
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $chart_types = array();

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();

		$this->init();

		if ( empty( $this->name ) ) {
			$this->name = 'charts';
		}
		if ( empty( $this->title ) ) {
			$this->title = __( 'Charts', 'torro-forms' );
		}
		if ( empty( $this->description ) ) {
			$this->description = __( 'This is a Torro Forms chart creator.', 'torro-forms' );
		}

		$this->register_chart_type( 'bars', esc_attr__( 'Bars', 'torro-forms' ), array( $this, 'bars' ) );
		$this->register_chart_type( 'pies', esc_attr__( 'Pies', 'torro-forms' ), array( $this, 'pies' ) );
	}

	abstract function bars( $title, $results, $params = array() );

	abstract function pies( $title, $results, $params = array() );

	/**
	 * Register Chart types
	 *
	 * @param string $name
	 * @param string $display_name
	 * @param string|array $callback
	 *
	 * @since 1.0.0
	 */
	protected function register_chart_type( $name, $display_name, $callback ) {
		$this->chart_types[] = array(
			'name'			=> $name,
			'display_name'	=> $display_name,
			'callback'		=> $callback,
		);
	}

	/**
	 * Returns option content for charts
	 *
	 * @param int $form_id
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	public function option_content( $form_id ) {
		if( ! torro()->forms()->get( $form_id )->has_analyzable_elements() ){
			$html = '<p class="not-found-area">' . esc_attr__( 'There are no analyzable elements in your form.', 'torro-forms' ) . '</p>';
			return $html;
		}

		$html = $this->show_form_charts( $form_id );

		$html .= '<div id="torro-result-charts-bottom">';
		ob_start();
		do_action( 'torro_result_charts_postbox_bottom', $form_id );
		$html .= ob_get_clean();
		$html .= '<div style="clear:both"></div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Showing all form charts
	 *
	 * @param $form_id
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function show_form_charts( $form_id ) {
		$results = $this->parse_results_for_export( $form_id, 0, -1, 'raw', false );
		$count_charts = 0;

		$html = '';
		if ( 0 < count( $results ) ) {
			$element_results = $this->format_results_by_element( $results );

			foreach ( $element_results as $headline => $element_result ) {
				$html .= $this->show_element_chart( $headline, $element_result );
				$count_charts++;
			}
		}

		if ( 0 === $count_charts || 0 === count( $results ) ) {
			$html .= '<p class="not-found-area">' . esc_attr__( 'There are no Results to show.', 'torro-forms' ) . '</p>';
		}

		return $html;
	}

	/**
	 * Showing element charts
	 *
	 * @param $headline
	 * @param $element_result
	 *
	 * @return string|void
	 * @since 1.0.0
	 */
	public function show_element_chart( $headline, $element_result ) {
		$headline_arr = explode( '_', $headline );

		$element_id = (int) $headline_arr[1];
		$element = torro()->elements()->get( $element_id );

		// Skip collecting Data if there is no analyzable Data
		$type = $element->type_obj;
		if ( ! $type->input_answers ) {
			return;
		}

		if ( 0 < count( $element->sections ) ) {
			$label = $element->label;

			$answer_id = (int) $headline_arr[2];

			$value = '';
			foreach( $element->answers as $element_answer ){
				if( $element_answer->id === $answer_id ){
					$value = $element_answer->answer;
					break;
				}
			}

			if ( ! empty( $value ) ) {
				$label .= ' - ' . $value;
			}
		} else {
			$label = $element->label;
		}

		$html  = '<div class="torro-chart">';
		$html .= '<div class="torro-chart-diagram">';

		ob_start();
		do_action( 'torro_result_charts_postbox_element_top', $element );
		$html .= ob_get_clean();

		$html .= $this->bars( $label, $element_result );

		$html .= '</div>';

		$html .= '<div class="torro-chart-actions">';
		ob_start();
		do_action( 'torro_result_charts_postbox_element', $element );
		$html .= ob_get_clean();
		$html .= '</div>';

		$html .= '<div style="clear:both"></div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Formating results for charting
	 *
	 * @param array $results
	 *
	 * @return array $results_formatted
	 * @since 1.0.0
	 */
	public function format_results_by_element( $results ) {
		if ( ! is_array( $results ) ) {
			return FALSE;
		}

		$column_names = array_keys( $results[0] );
		$results_formatted = array();

		// Running thru all available Result Values
		foreach ( $column_names as $column_name ) {
			$column_name_arr = explode( '_', $column_name );
			$element_type = $column_name_arr[0];

			// Missing columns without element data
			if ( 'element' !== $element_type ) {
				continue;
			}

			$element_id = (int) $column_name_arr[1];
			$element = torro()->elements()->get( $element_id );

			if ( 0 < count( $element->sections ) ) {
				$result_key = 'element_' . $element_id . '_' . $column_name_arr[2];
			} else {
				$result_key = 'element_' . $element_id;
			}

			// Collecting Data from all Resultsets
			foreach ( $results as $result ) {
				// Skip collecting Data if there is no analyzable Data
				if ( ! $element->is_analyzable() ) {
					continue;
				}

				// Counting different kind of Elements
				$type = $element->type_obj;
				if ( $type->answer_array ) {
					$answer_id = (int) $column_name_arr[ 2 ];

					$value = '';
					foreach( $element->answers as $element_answer ) {
						if( $element_answer->id === $answer_id ){
							$value = $element_answer->answer;
							break;
						}
					}

					// If there is no value for this element, skip counting
					if( empty( $value ) ) {
						continue;
					}

					if ( array_key_exists( $result_key, $results_formatted ) && is_array( $results_formatted[ $result_key ] ) && array_key_exists( $value, $results_formatted[ $result_key ] ) ) {
						if ( 'yes' === $result[ $column_name ] ) {
							$results_formatted[ $result_key ][ $value ]++;
						}
					} elseif( 'yes' === $result[ $column_name ] ) {
						$results_formatted[ $result_key ][ $value ] = 1;
					} else {
						$results_formatted[ $result_key ][ $value ] = 0;
					}
				} else {
					// Setting up all answers to 0 to have also Zero values
					foreach ( $element->answers as $element_answer ) {
						if ( ! isset( $results_formatted[ $result_key ][ $element_answer->answer ] ) ) {
							$results_formatted[ $result_key ][ $element_answer->answer ] = 0;
						}
					}

					$value = $result[ $column_name ];

					// If there is no value for this element, skip counting
					if( empty( $value ) ) {
						continue;
					}

					$results_formatted[ $result_key ][ $value ]++;
				}
			}
		}

		return $results_formatted;
	}
}
