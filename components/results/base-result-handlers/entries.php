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

		$params = array(
			'num_rows' => 20
		);

		$results = $form_results->results( $params );

		$html = '<div id="af-entries">';

		if( $form_results->count() > 0 )
		{
			$date_format = get_option( 'date_format' );

			$html .= '<table class="widefat entries">';
			$html .= '<thead>';
			$html .= '<tr>';

			$entry_columns = apply_filters( 'af_entry_columns', array( 'result_id', 'user_id', 'timestamp' ) );

			foreach( array_keys( $results[ 0 ] ) AS $headline )
			{
				if( in_array( $headline, $entry_columns ) )
				{
					switch( $headline )
					{
						case 'result_id':
							$headline_title = esc_attr( 'ID', 'af_locale' );
							break;
						case 'user_id':
							$headline_title = esc_attr( 'User ID', 'af_locale' );
							break;
						case 'timestamp':
							$headline_title = esc_attr( 'Date', 'af_locale' );
							break;
						default:
							$headline_title = $headline;
					}
					$html .= '<th nowrap>' . $headline_title . '</th>';
				}
			}

			$html .= '<th class="export-links">' . sprintf( __( 'Export as <a href="%s">XLS</a> or <a href="%s">CSV</a>', 'af-locale' ), admin_url( 'edit.php' ) . '?post_type=af-forms&export=xls&form_id=' . $form_id, admin_url( 'edit.php' ) . '?post_type=af-forms&export=csv&form_id=' . $form_id ). '</th>';
			$html .= '</tr>';
			$html .= '</thead>';

			$html .= '<tbody>';

			foreach( $results AS $result )
			{
				$html .= '<tr>';
				foreach( array_keys( $results[ 0 ] ) AS $headline )
				{
					if( in_array( $headline, $entry_columns ) )
					{
						switch( $headline )
						{
							case 'timestamp':
								$content = date_i18n( $date_format, $result[ $headline ] );
								break;
							default:
								$content = $result[ $headline ];
						}

						$html .= '<td>';
						$html .= $content;
						$html .= '</td>';
					}
				}

				$html .= '<td class="entry-actions">';
				$html .= '<input type="button" value="' . esc_attr( 'Show Details', 'af-locale' ) . '" class="button entry-show-details" />';
				$html .= '</td>';

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