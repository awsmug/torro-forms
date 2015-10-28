<?php
/**
 * List Result Handler
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

class AF_ResultsEntries extends AF_ResultHandler
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->title = __( 'Entries', 'af-locale' );
		$this->name = 'entries';

		add_action( 'admin_print_styles', array( __CLASS__, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );
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
		$results = $form_results->results( array( 'column_name' => 'label' ) );

		$html = '<div id="af-result-list">';

		if( $form_results->count() > 0 )
		{

			$html .= '<table class="widefat">';
			$html .= '<thead>';
			$html .= '<tr>';

			foreach( array_keys( $results[ 0 ] ) AS $headline )
			{
				$html .= '<th nowrap>' . $headline . '</th>';
			}

			$html .= '</tr>';
			$html .= '</thead>';

			$html .= '<tbody>';

			foreach( $results AS $result )
			{
				$html .= '<tr>';
				foreach( array_keys( $results[ 0 ] ) AS $headline )
				{
					$html .= '<td>';
					$html .= $result[ $headline ];
					$html .= '</td>';
				}
				$html .= '</tr>';
			}
			$html .= '</tbody>';

			$html .= '</table>';
		}
		else
		{
			$html .= '<p>' . esc_attr( 'There are no Results to show.', 'af-locale' ) . '</p>';
		}

		$html .= '</div>';

		return $html;
	}
}

af_register_result_handler( 'AF_ResultsEntries' );