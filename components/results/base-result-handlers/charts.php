<?php
/**
 * Charts Result Handler
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Results
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Torro_Result_Charts extends Torro_ResultHandler {
	protected $chart_types = array();

	/**
	 * Initializing.
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
			$this->description = __( 'This is an Torro Forms Chart Creator.', 'torro-forms' );
		}

		$this->register_chart_type( 'bars', esc_attr__( 'Bars', 'torro-forms' ), array( $this, 'bars' ) );
		$this->register_chart_type( 'pies', esc_attr__( 'Pies', 'torro-forms' ), array( $this, 'pies' ) );

		add_action( 'torro_result_charts_postbox_bottom', array( $this, 'charts_general_settings' ), 10 );
	}

	abstract function bars( $title, $results, $params = array() );

	abstract function pies( $title, $results, $params = array() );

	/**
	 * Register Chart types
	 *
	 * @param $name
	 * @param $display_name
	 * @param $callback
	 */
	protected function register_chart_type( $name, $display_name, $callback ) {
		$this->chart_types[] = array(
			'name'			=> $name,
			'display_name'	=> $display_name,
			'callback'		=> $callback,
		);
	}

	public function option_content() {
		global $post;

		$form_id = $post->ID;

		$form_results = new Torro_Form_Results( $form_id );
		$results = $form_results->results();
		$html = '';

		$count_charts = 0;

		if ( 0 < $form_results->count() ) {
			$element_results = self::format_results_by_element( $results );

			foreach ( $element_results as $headline => $element_result ) {
				$headline_arr = explode( '_', $headline );

				$element_id = (int) $headline_arr[ 1 ];
				$element = torro()->elements()->get( $element_id );

				// Skip collecting Data if there is no analyzable Data
				if ( ! $element->has_answers ) {
					continue;
				}

				if ( 0 < count( $element->sections ) ) {
					$label = $element->label;

					$column_name = $element->replace_column_name( $headline );

					if ( ! empty( $column_name ) ) {
						$label .= ' - ' . $column_name;
					}
				} else {
					$label = $element->label;
				}

				$html .= '<div class="torro-chart">';
				$html .= '<div class="torro-chart-diagram">';

				$html .= $this->bars( $label, $element_result );

				$html .= '</div>';

				$count_charts++;

				$html .= '<div class="torro-chart-actions">';
				ob_start();
				do_action( 'torro_result_charts_postbox_element', $element );
				$html .= ob_get_clean();
				$html .= '</div>';

				$html .= '<div style="clear:both"></div>';
				$html .= '</div>';
			}
		}

		if ( 0 === $count_charts || 0 === $form_results->count() ) {
			$html .= '<p class="not-found-area">' . esc_attr__( 'There are no Results to show.', 'torro-forms' ) . '</p>';
		}

		$html .= '<div id="torro-result-charts-bottom">';
		ob_start();
		do_action( 'torro_result_charts_postbox_bottom', $form_id );
		$html .= ob_get_clean();
		$html .= '<div style="clear:both"></div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Formating Results for Charting
	 *
	 * @param array $results
	 *
	 * @return array $results_formatted
	 * @since 1.0.0
	 */
	public static function format_results_by_element( $results ) {
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
				if ( ! $element->is_analyzable ) {
					continue;
				}

				// Counting different kind of Elements
				if ( $element->answer_is_multiple ) {
					$answer_id = (int) $column_name_arr[ 2 ];

					$value = $element->replace_column_name( $column_name );

					if ( empty( $value ) ) {
						$value = $element->answers[ $answer_id ]['text'];
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
					foreach ( $element->answers AS $element_answers ) {
						if ( ! isset( $results_formatted[ $result_key ][ $element_answers['text'] ] ) ) {
							$results_formatted[ $result_key ][ $element_answers['text'] ] = 0;
						}
					}

					$value = $result[ $column_name ];
					$results_formatted[ $result_key ][ $value ]++;
				}
			}
		}

		return $results_formatted;
	}

	/**
	 * Adding option for showing Charts after submitting Form
	 */
	public function charts_general_settings() {
		global $post;

		$form_id = $post->ID;
		$show_results = get_post_meta( $form_id, 'show_results', TRUE );

		if ( ! $show_results ) {
			$show_results = 'no';
		}

		$checked_no = '';
		$checked_yes = '';

		if ( 'no' === $show_results ) {
			$checked_no = ' checked="checked"';
		} else {
			$checked_yes = ' checked="checked"';
		}

		$html = '<div class="in-postbox-one-third">';
		$html .= '<label for="show_results">' . esc_html__( 'After Submit' ) . '</label>';
		$html .= '<input type="radio" name="show_results" value="yes"' . $checked_yes . '>' . esc_html__( 'show Charts' ) . ' <br />';
		$html .= '<input type="radio" name="show_results" value="no"' . $checked_no . '>' . esc_html__( 'do not Show Charts' ) . '';
		$html .= '</div>';

		echo $html;
	}
}
