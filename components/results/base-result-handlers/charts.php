<?php
/**
 * Charts Result Handler
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Results
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

if( !defined( 'ABSPATH' ) )
{
	exit;
}

class AF_ResultCharts extends AF_ResultHandler
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->title = __( 'Charts', 'af-locale' );
		$this->name = 'charts';

		add_action( 'admin_print_styles', array( __CLASS__, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );
		add_action( 'form_results_content_bottom', array( $this, 'charts_general_settings' ), 10 );

		$this->settings_fields = array(
			'invitations' => array(
				'title'       => esc_attr( 'Test', 'af-locale' ),
				'description' => esc_attr( 'Test XXX', 'af-locale' ),
				'type'        => 'text'
			),
		);
	}

	/**
	 * Enqueue admin scripts
	 */
	public static function enqueue_admin_scripts()
	{
		if( !af_is_formbuilder() )
		{
			return;
		}
	}

	/**
	 * Enqueue admin styles
	 */
	public static function enqueue_admin_styles()
	{
	}

	public function option_content()
	{
		global $post;

		$form_id = $post->ID;

		$form_results = new AF_Form_Results( $form_id );
		$results = $form_results->results();
		$html = '';

		if( $form_results->count() > 0 )
		{
			$element_results = $this->format_results_by_element( $results );
			$count_charts = 0;

			foreach( $element_results AS $headline => $element_result )
			{
				$headline_arr = explode( '_', $headline );

				$element_id = (int) $headline_arr[ 1 ];
				$element = af_get_element( $element_id );

				// Skip collecting Data if there is no analyzable Data
				if( !$element->has_answers )
				{
					continue;
				}

				$chart_creator = 'AF_ChartCreator_Dimple';

				$chart_creator = new $chart_creator();
				$chart_type = 'bars';

				$html .= $chart_creator->$chart_type( $element->label, $element_result );

				$count_charts++;

				ob_start();
				do_action( 'af_result_charts_postbox_element', $element );
				$html .= ob_get_clean();
			}
		}

		if( 0 == $count_charts || 0 == $form_results->count() )
		{
			$html .= '<p>' . esc_attr( 'There are no Results to show.', 'af-locale' ) . '</p>';
		}

		ob_start();
		do_action( 'af_result_charts_postbox_bottom', $form_id );
		$html .= ob_get_clean();

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
	public function format_results_by_element( $results )
	{
		if( !is_array( $results ) )
		{
			return FALSE;
		}

		$column_names = array_keys( $results[ 0 ] );
		$results_formatted = array();

		// Running thru all available Result Values
		foreach( $column_names AS $column_name )
		{
			$column_name_arr = explode( '_', $column_name );
			$element_type = $column_name_arr[ 0 ];

			// Running all Elements
			if( 'element' == $element_type )
			{
				$element_id = (int) $column_name_arr[ 1 ];
				$element = af_get_element( $element_id );

				$result_key = 'element_' . $element_id;

				// Collecting Data from all Resultsets
				foreach( $results AS $result )
				{
					// Skip collecting Data if there is no analyzable Data
					if( !$element->has_answers )
					{
						continue;
					}

					// Counting different kind of Elements
					if( $element->answer_is_multiple )
					{
						$answer_id = (int) $column_name_arr[ 2 ];
						$value = $element->answers[ $answer_id ][ 'text' ];

						if( is_array( $results_formatted[ $result_key ] ) && array_key_exists( $value, $results_formatted[ $result_key ] ) && 'yes' == $result[ $column_name ] )
						{
							$results_formatted[ $result_key ][ $value ]++;
						}
						elseif( 'yes' == $result[ $column_name ] )
						{
							$results_formatted[ $result_key ][ $value ] = 1;
						}
						else
						{
							$results_formatted[ $result_key ][ $value ] = 0;
						}
					}
					else
					{
						// Setting up all answers to 0 to have also Zero values
						foreach( $element->answers AS $element_answers )
						{
							if( !isset( $results_formatted[ $result_key ][ $element_answers[ 'text' ] ] ) )
							{
								$results_formatted[ $result_key ][ $element_answers[ 'text' ] ] = 0;
							}
						}

						$value = $result[ $column_name ];
						$results_formatted[ $result_key ][ $value ]++;
					}
				}
			}
		}

		return $results_formatted;
	}

	/**
	 * Adding option for showing Charts after submitting Form
	 */
	public function charts_general_settings()
	{
		global $post;

		$form_id = $post->ID;
		$show_results = get_post_meta( $form_id, 'show_results', TRUE );

		if( '' == $show_results )
		{
			$show_results = 'no';
		}

		$checked_no = '';
		$checked_yes = '';

		if( 'no' == $show_results )
		{
			$checked_no = ' checked="checked"';
		}
		else
		{
			$checked_yes = ' checked="checked"';
		}

		$html = '<div class="form-options section general-settings">';
		$html .= '<table>';
		$html .= '<tr>';
		$html .= '<td><label for="show_results">' . esc_attr__( 'After submitting:', 'af-locale' ) . '</label></td>';
		$html .= '<td>';
		$html .= '<input type="radio" name="show_results" value="yes"' . $checked_yes . '>' . esc_attr__( 'Show Charts' ) . ' ';
		$html .= '<input type="radio" name="show_results" value="no"' . $checked_no . '>' . esc_attr__( 'Do not Show Charts' ) . '';
		$html .= '</td>';
		$html .= '</tr>';

		ob_start();
		do_action( 'af_charts_general_settings_table' );
		$html .= ob_get_clean();

		$html .= '</table>';


		$html .= '</div>';

		echo $html;
	}
}

af_register_result_handler( 'AF_ResultCharts' );