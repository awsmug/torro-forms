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

		add_action( 'wp_ajax_af_show_entries', array( __CLASS__, 'ajax_show_entries' ) );
		add_action( 'wp_ajax_af_show_entry', array( __CLASS__, 'ajax_show_entry' ) );
	}

	public function option_content()
	{
		global $post;

		$form_id = $post->ID;

		$start = 0;
		if( array_key_exists( 'af-entries-start', $_POST ) )
		{
			$start = $_POST[ 'af-entries-start' ];
		}

		$length = 10;
		if( array_key_exists( 'af-entries-start', $_POST ) )
		{
			$length = $_POST[ 'af-entries-length' ];
		}

		$html = '<div id="af-entries">';
		$html .= '<div class="af-entries-slider">';
		$html .= '<div class="af-slider-start-content">';

		$form_results = new AF_Form_Results( $form_id );
		$form_results->results();
		$num_results = $form_results->count();

		$html .= self::show_results( $form_id, $start, $length, $num_results );

		$html .= '</div>';
		$html .= '<div class="af-slider-right"></div>';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	private static function show_results( $form_id, $start, $length, $num_results )
	{
		$form_results = new AF_Form_Results( $form_id );

		$filter = array(
			'start_row' => $start,
			'num_rows' => $length
		);

		$html = '';
		$results = $form_results->results( $filter );

		if( $form_results->count() > 0 )
		{
			$date_format = get_option( 'date_format' );

			$html .= '<table id="af-entries-table" class="widefat entries">';
			$html .= '<thead>';
			$html .= '<tr>';

			$entry_columns = apply_filters( 'af_entry_columns', array( 'result_id', 'user_id', 'timestamp' ) );

			foreach( array_keys( $results[ 0 ] ) AS $headline )
			{
				if( in_array( $headline, $entry_columns ) )
				{
					switch ( $headline )
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

			$html .= '<th class="export-links">' . sprintf( __( 'Export as <a href="%s">XLS</a> or <a href="%s">CSV</a>', 'af-locale' ), admin_url( 'edit.php' ) . '?post_type=af-forms&export=xls&form_id=' . $form_id, admin_url( 'edit.php' ) . '?post_type=af-forms&export=csv&form_id=' . $form_id ) . '</th>';
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
						switch ( $headline )
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
				$html .= '<input type="button" value="' . esc_attr( 'Show Details', 'af-locale' ) . '" class="button af-show-entry" rel="' . $result[ 'result_id' ] . '" />';
				$html .= '</td>';

				$html .= '</tr>';
			}
			$html .= '</tbody>';
			$html .= '<tfoot>';
			$html .= '<tr>';
			$html .= '<td>';
			$html .= '</td>';
			$html .= '<td>';
			$html .= '</td>';
			$html .= '<td>';
			$html .= '</td>';
			$html .= '<td>&nbsp;</td>';
			$html .= '</tr>';
			$html .= '</tfoot>';
			$html .= '</table>';

			$prev = $start - $length;
			$next = $start + $length;

			$html .= '<div class="af-nav">';
			if( $prev >= 0 )
			{
				$html .= '<div class="af-nav-prev-link">';
				$prev_url = $_SERVER[ 'REQUEST_URI' ] . '&af-entries-start=' . $prev . '&af-entries-length=' . $length;
				$prev_link = sprintf( __( '<a href="%s" class="af-entries-nav button">Previous</a>', 'af-locale' ), $prev_url );
				$html .= $prev_link;
				$html .= '</div>';
			}

			if( $next < $num_results )
			{
				$html .= '<div class="af-nav-next-link">';
				$next_url = $_SERVER[ 'REQUEST_URI' ] . '&af-entries-start=' . $next . '&af-entries-length=' . $length;
				$next_link = sprintf( __( '<a href="%s" class="af-entries-nav button">Next</a>', 'af-locale' ), $next_url );
				$html .= $next_link;
				$html .= '</div>';
			}

			$html .= '<p>' . sprintf( esc_attr__( 'Entries %d - %d of %d', 'af-locale' ), $start + 1, $start + $length, $num_results ) . '</p>';
			$html .= '</div>';
		}
		else
		{
			$html .= '<p class="not-found-area">' . esc_attr( 'There are no Results to show.', 'af-locale' ) . '</p>';
		}

		return $html;
	}

	public static function ajax_show_entries()
	{
		$form_id = $_POST[ 'form_id' ];
		$start = $_POST[ 'start' ];
		$length = $_POST[ 'length' ];

		$form_results = new AF_Form_Results( $form_id );
		$results = $form_results->results();
		$num_results = $form_results->count();

		echo  self::show_results( $form_id, $start, $length, $num_results );

		exit;
	}


	public static function ajax_show_entry()
	{
		global $wpdb, $af_global;

		$form_id = $_POST[ 'form_id' ];
		$result_id = $_POST[ 'result_id' ];

		if( !af_form_exists( $form_id ) )
		{
			echo esc_attr__( 'Form not found.', 'af-locale' );
			exit;
		}

		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$af_global->tables->results} WHERE id = %d", $result_id );
		$count_results = $wpdb->get_var( $sql );

		if( 0 === $count_results )
		{
			echo esc_attr__( 'Entry not found.', 'af-locale' );
			exit;
		}

		$filter = array(
			'result_ids' => array( $result_id ),
			'column_name' => 'label'
		);

		$form_results = new AF_Form_Results( $form_id );
		$results = $form_results->results( $filter );

		foreach( $results AS $result )
		{
			if( !array_key_exists( 'result_id', $result ) )
			{
				echo esc_attr__( 'Error on getting Result.', 'af-locale' );
				exit;
			}

			$html = '<table id="af-entry-table" class="widefat">';

			$html .= '<thead>';
			$html .= '<tr>';
			$html .= '<th>' . esc_attr__( 'Label', 'af-locale' ) . '</th>';
			$html .= '<th>' . esc_attr__( 'Value', 'af-locale' ) . '</th>';
			$html .= '</tr>';
			$html .= '</thead>';

			$html .= '<tbody>';

			$result_id = '';
			$user_id = '';
			$timestamp = '';
			$extra_info = '';

			foreach( $result AS $column_name => $value )
			{

				switch( $column_name )
				{
					case 'result_id':
						$result_id = $value;
						break;

					case 'user_id':
						if( !empty( $value ))
						{
							$user_id = $value;
							$user = get_user_by( 'id', $user_id );
							$extra_info .= ' - ' . esc_attr__( 'User', 'af-locale' ) . ' ' . $user->user_nicename;
						}
						break;

					case 'timestamp':
						$timestamp = $value;

						$date_string = date_i18n( get_option( 'date_format' ), $timestamp );
						$time_string = date_i18n( get_option( 'time_format' ), $timestamp );
						break;

					default:

						if( 'yes' == $value )
						{
							$value = esc_attr__( 'Yes', 'af-locale' );
						}
						if( 'no' == $value )
						{
							$value = esc_attr__( 'No', 'af-locale' );
						}

						$html .= '<tr>';
						$html .= '<td>' . $column_name . '</td>';
						$html .= '<td>' . $value. '</td>';
						$html .= '</tr>';

						break;
				}
			}
			$html .= '</tbody>';
			$html .= '<tfoot>';
			$html .= '<tr>';
			$html .= '<td colspan="2"><small>' . esc_attr__( 'Date', 'af-locale' ) . ' ' . $date_string . ' - ' . esc_attr__( 'Time', 'af-locale' ) . ' ' . $time_string . $extra_info . '</small></td>';
			$html .= '</tr>';
			$html .= '</tfoot>';
			$html .= '</table>';

			$html .= '<div id="af-entry-buttons">';
			$html .= '<input type="button" class="button af-hide-entry" value="'. esc_attr__( 'Back to Results', 'af-locale' ) . '">';
			$html .= '</div>';

			echo $html;
		}

		exit;
	}

	public function admin_styles()
	{
		if( !af_is_formbuilder() )
		{
			return;
		}

		wp_enqueue_style( 'af-results-entries-css', AF_URLPATH . 'components/results/base-result-handlers/includes/css/entries.css' );
	}

	public function admin_scripts()
	{
		if( !af_is_formbuilder() )
		{
			return;
		}

		wp_enqueue_script( 'af-results-entries', AF_URLPATH . 'components/results/base-result-handlers/includes/js/entries.js' );
	}
}

af_register_result_handler( 'AF_ResultsEntries' );