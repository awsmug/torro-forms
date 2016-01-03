<?php
/**
 * List Result Handler
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

if( !defined( 'ABSPATH' ) )
{
	exit;
}

class Torro_ResultsEntries extends Torro_ResultHandler
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->title = __( 'Entries', 'torro-forms' );
		$this->name = 'entries';

		add_action( 'wp_ajax_torro_show_entries', array( __CLASS__, 'ajax_show_entries' ) );
		add_action( 'wp_ajax_torro_show_entry', array( __CLASS__, 'ajax_show_entry' ) );
	}

	public function option_content()
	{
		global $post;

		$form_id = $post->ID;

		$start = 0;
		if( array_key_exists( 'torro-entries-start', $_POST ) )
		{
			$start = $_POST[ 'torro-entries-start' ];
		}

		$length = 10;
		if( array_key_exists( 'torro-entries-start', $_POST ) )
		{
			$length = $_POST[ 'torro-entries-length' ];
		}

		$html = '<div id="torro-entries">';
		$html .= '<div class="torro-entries-slider">';
		$html .= '<div class="torro-slider-start-content">';

		$form_results = new Torro_Form_Results( $form_id );
		$form_results->results();
		$num_results = $form_results->count();

		$html .= self::show_results( $form_id, $start, $length, $num_results );

		$html .= '</div>';
		$html .= '<div class="torro-slider-right"></div>';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	private static function show_results( $form_id, $start, $length, $num_results )
	{
		$form_results = new Torro_Form_Results( $form_id );

		$filter = array(
			'start_row' => $start,
			'num_rows' => $length
		);

		$html = '';
		$results = $form_results->results( $filter );

		if( $form_results->count() > 0 )
		{
			$date_format = get_option( 'date_format' );

			$html .= '<table id="torro-entries-table" class="widefat entries">';
			$html .= '<thead>';
			$html .= '<tr>';

			$entry_columns = apply_filters( 'torro_entry_columns', array( 'result_id', 'user_id', 'timestamp' ) );

			foreach( array_keys( $results[ 0 ] ) AS $headline )
			{
				if( in_array( $headline, $entry_columns ) )
				{
					switch ( $headline )
					{
						case 'result_id':
							$headline_title = esc_attr__( 'ID', 'torro_locale' );
							break;
						case 'user_id':
							$headline_title = esc_attr__( 'User', 'torro_locale' );
							break;
						case 'timestamp':
							$headline_title = esc_attr__( 'Date', 'torro_locale' );
							break;
						default:
							$headline_title = $headline;
					}
					$html .= '<th nowrap>' . $headline_title . '</th>';
				}
			}

			$html .= '<th class="export-links">' . sprintf( __( 'Export as <a href="%s">XLS</a> or <a href="%s">CSV</a>', 'torro-forms' ), admin_url( 'edit.php' ) . '?post_type=torro-forms&torro_export=xls&form_id=' . $form_id, admin_url( 'edit.php' ) . '?post_type=torro-forms&export=csv&form_id=' . $form_id ) . '</th>';
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

							case 'user_id':
								$user = get_user_by( 'id', $result[ $headline ] );
								$content = $user->display_name;
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
				$html .= '<a type="button" class="button torro-show-entry" rel="' . $result[ 'result_id' ] . '" >' . esc_attr__( 'Show Details', 'torro-forms' ) . '</a>';
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
			$count = $num_results <= $length ? $num_results : $length;

			$html .= '<div class="torro-nav">';
			if( $prev >= 0 )
			{
				$html .= '<div class="torro-nav-prev-link">';
				$prev_url = self::get_admin_url( $form_id, array(
					'torro-entries-start'	=> $prev,
					'torro-entries-length'	=> $length,
				) );
				$prev_link = sprintf( __( '<a href="%s" class="torro-entries-nav button">Previous</a>', 'torro-forms' ), $prev_url );
				$html .= $prev_link;
				$html .= '</div>';
			}

			if( $next < $num_results )
			{
				$html .= '<div class="torro-nav-next-link">';
				$next_url = self::get_admin_url( $form_id, array(
					'torro-entries-start'	=> $next,
					'torro-entries-length'	=> $length,
				) );
				$next_link = sprintf( __( '<a href="%s" class="torro-entries-nav button">Next</a>', 'torro-forms' ), $next_url );
				$html .= $next_link;
				$html .= '</div>';
			}

			$html .= '<p>' . sprintf( esc_attr__( '%s - %s of %s', 'torro-forms' ), $start + 1, $count, $num_results ) . '</p>';
			$html .= '</div>';
		}
		else
		{
			$html .= self::show_not_found_notice();
		}

		return $html;
	}

	/**
	 * Returns the edit URL for a form, with optional query arguments
	 */
	private static function get_admin_url( $form_id, $args = array() ) {
		$admin_url = admin_url( 'post.php?post=' . $form_id . '&action=edit' );
		if ( count( $args ) > 0 ) {
			$admin_url = add_query_arg( $args, $admin_url );
		}

		return $admin_url;
	}

	public static function show_not_found_notice() {
		return '<p class="not-found-area">' . esc_attr__( 'There are no Results to show.', 'torro-forms' ) . '</p>';
	}

	public static function ajax_show_entries()
	{
		$form_id = $_POST[ 'form_id' ];
		$start = $_POST[ 'start' ];
		$length = $_POST[ 'length' ];

		$form_results = new Torro_Form_Results( $form_id );
		$results = $form_results->results();
		$num_results = $form_results->count();

		echo  self::show_results( $form_id, $start, $length, $num_results );

		exit;
	}


	public static function ajax_show_entry()
	{
		global $wpdb, $torro_global;

		$form_id = $_POST[ 'form_id' ];
		$result_id = $_POST[ 'result_id' ];

		if( !torro_form_exists( $form_id ) )
		{
			echo esc_attr__( 'Form not found.', 'torro-forms' );
			exit;
		}

		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$torro_global->tables->results} WHERE id = %d", $result_id );
		$count_results = $wpdb->get_var( $sql );

		if( 0 === $count_results )
		{
			echo esc_attr__( 'Entry not found.', 'torro-forms' );
			exit;
		}

		$filter = array(
			'result_ids' => array( $result_id ),
		);

		$form_results = new Torro_Form_Results( $form_id );
		$results = $form_results->results( $filter );

		if( $form_results->count() > 0 )
		{
			foreach( $results AS $result )
			{
				if( !array_key_exists( 'result_id', $result ) )
				{
					echo esc_attr__( 'Error on getting Result.', 'torro-forms' );
					exit;
				}

				$html = '<table id="torro-entry-table" class="widefat">';

				$html .= '<thead>';
				$html .= '<tr>';
				$html .= '<th>' . esc_attr__( 'Label', 'torro-forms' ) . '</th>';
				$html .= '<th>' . esc_attr__( 'Value', 'torro-forms' ) . '</th>';
				$html .= '</tr>';
				$html .= '</thead>';

				$html .= '<tbody>';

				$result_id = '';
				$user_id = '';
				$timestamp = '';
				$extra_info = '';

				foreach( $result AS $column_name => $value )
				{

					switch ( $column_name )
					{
						case 'result_id':
							$result_id = $value;
							break;

						case 'user_id':
							if( !empty( $value ) )
							{
								$user_id = $value;
								$user = get_user_by( 'id', $user_id );
								$extra_info .= ' - ' . esc_attr__( 'User', 'torro-forms' ) . ' ' . $user->user_nicename;
							}
							break;

						case 'timestamp':
							$timestamp = $value;

							$date_string = date_i18n( get_option( 'date_format' ), $timestamp );
							$time_string = date_i18n( get_option( 'time_format' ), $timestamp );
							break;

						default:

							$column_arr = explode( '_', $column_name );

							// On Elements
							if( array_key_exists( 0, $column_arr ) && 'element' == $column_arr[ 0 ] )
							{
								$element_id = $column_arr[ 1 ];
								$element = torro_get_element( $element_id );

								$column_name = $element->replace_column_name( $column_name );

								if( FALSE == $column_name )
								{
									$column_name = $element->label;
								}

								if( 'yes' == $value )
								{
									$value = esc_attr__( 'Yes', 'torro-forms' );
								}
								if( 'no' == $value )
								{
									$value = esc_attr__( 'No', 'torro-forms' );
								}

								$html .= '<tr>';
								$html .= '<td>' . $column_name . '</td>';
								$html .= '<td>' . $value . '</td>';
								$html .= '</tr>';
							}

							break;
					}
				}
				$html .= '</tbody>';
				$html .= '<tfoot>';
				$html .= '<tr>';
				$html .= '<td colspan="2"><small>' . esc_attr__( 'Date', 'torro-forms' ) . ' ' . $date_string . ' - ' . esc_attr__( 'Time', 'torro-forms' ) . ' ' . $time_string . $extra_info . '</small></td>';
				$html .= '</tr>';
				$html .= '</tfoot>';
				$html .= '</table>';
			}
		}
		else
		{
			$html =  esc_attr__( 'Entry not found.', 'torro-forms' );
		}

		$html .= '<div id="torro-entry-buttons">';
		$html .= '<input type="button" class="button torro-hide-entry" value="' . esc_attr__( 'Back to Results', 'torro-forms' ) . '">';
		$html .= '</div>';

		echo $html;

		exit;
	}

	public function admin_styles()
	{
		if( !torro_is_formbuilder() )
		{
			return;
		}

		wp_enqueue_style( 'torro-results-entries', TORRO_URLPATH . 'assets/css/results-entries.css' );
	}

	public function admin_scripts()
	{
		if( !torro_is_formbuilder() )
		{
			return;
		}

		wp_enqueue_script( 'torro-results-entries', TORRO_URLPATH . 'assets/js/results-entries.js' );
	}

	public function frontend_styles() {}

	public function frontend_scripts() {}
}

torro_register_result_handler( 'Torro_ResultsEntries' );
