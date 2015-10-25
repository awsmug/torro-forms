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
		add_action( 'form_results_content_bottom', array( $this, 'show_results_after_submitting_fields' ), 10 );

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

			foreach( $element_results AS $headline => $element_result )
			{
				$headline_arr = explode( '_', $headline );

				$element_id = (int) $headline_arr[ 1 ];
				$element = af_get_element( $element_id );

				$chart_creator = 'AF_ChartCreator_Dimple';

				$chart_creator = new $chart_creator();
				$chart_type = 'bars';

				if( !$element->answer_is_multiple ){
					$label = $element->label;
				}
				else
				{
					$label = $element->label . ' - ' . $element->answers[ $headline_arr[ 2 ] ][ 'text' ];
				}

				$html .= $chart_creator->$chart_type( $label, $element_result );
			}
		}
		else
		{
			$html .= '<p>' . esc_attr( 'There are no Results to show.', 'af-locale' ) . '</p>';
		}

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

		$headlines = array_keys( $results[ 0 ] );
		$results_formatted = array();

		foreach( $headlines AS $headline )
		{
			$headline_arr = explode( '_', $headline );
			$headline_type = $headline_arr[ 0 ];

			if( 'element' == $headline_type )
			{
				$element_id = (int) $headline_arr[ 1 ];
				$element = af_get_element( $element_id );

				foreach( $results AS $result )
				{
					if( !$element->has_answers )
					{
						continue;
					}

					if( !array_key_exists( $headline, $results_formatted ) )
					{
						$results_formatted[ $headline ] = array();
					}

					if( !$element->answer_is_multiple )
					{
						if( array_key_exists( $result[ $headline ], $results_formatted[ $headline ] ) )
						{
							$results_formatted[ $headline ][ $result[ $headline ] ]++;
						}
						else
						{

							$results_formatted[ $headline ][ $result[ $headline ] ] = 1;
						}
					}
					else
					{
						if( array_key_exists( $result[ $headline ], $results_formatted[ $headline ] ) )
						{
							$results_formatted[ $headline ][ $result[ $headline ] ]++;
						}
						else
						{

							$results_formatted[ $headline ][ $result[ $headline ] ] = 1;
						}
					}
				}
			}
		}

		return $results_formatted;
	}

	public function show_results_after_submitting_fields()
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
		$html .= '<td><label for="show_results">' . esc_attr__( 'After submitting Form:', 'af-locale' ) . '</label></td>';
		$html .= '<td>';
		$html .= '<input type="radio" name="show_results" value="yes"' . $checked_yes . '>' . esc_attr__( 'Show Charts' ) . ' ';
		$html .= '<input type="radio" name="show_results" value="no"' . $checked_no . '>' . esc_attr__( 'Do not Show Charts' ) . '';
		$html .= '</td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '</div>';

		echo $html;
	}
}

af_register_result_handler( 'AF_ResultCharts' );