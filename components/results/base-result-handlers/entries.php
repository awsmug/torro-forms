<?php
/**
 * List Result Handler
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Results
 * @version 1.0.0alpha1
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

final class Torro_ResultsEntries extends Torro_Result_Handler {
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	protected function init() {
		$this->title = __( 'Entries', 'torro-forms' );
		$this->name = 'entries';
	}

	public function option_content() {
		global $post;

		$form_id = $post->ID;

		$start = 0;
		if ( array_key_exists( 'torro-entries-start', $_POST ) ) {
			$start = $_POST['torro-entries-start'];
		}

		$length = 10;
		if ( array_key_exists( 'torro-entries-start', $_POST ) ) {
			$length = $_POST['torro-entries-length'];
		}

		$html = '<div id="torro-entries" class="torro-table-nav">';
		$html .= '<div class="torro-slider">';
		$html .= '<div class="torro-slider-middle">';

		$form_results = new Torro_Form_Results( $form_id );
		$form_results->results();
		$num_results = $form_results->count();

		$html .= $this->show_results( $form_id, $start, $length, $num_results );

		$html .= '</div>';
		$html .= '<div class="torro-slider-right"></div>';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	public function show_results( $form_id, $start, $length, $num_results ) {
		$form_results = new Torro_Form_Results( $form_id );

		$filter = array(
			'start_row'	=> $start,
			'num_rows'	=> $length
		);

		$html = '';
		$results = $form_results->results( $filter );

		if ( 0 < $form_results->count() ) {
			$date_format = get_option( 'date_format' );

			$html .= '<table id="torro-entries-table" class="widefat entries">';
			$html .= '<thead>';
			$html .= '<tr>';

			$entry_columns = apply_filters( 'torro_entry_columns', array( 'result_id', 'user_id', 'timestamp' ) );

			foreach( array_keys( $results[0] ) as $headline ) {
				if ( in_array( $headline, $entry_columns, true ) ) {
					switch ( $headline ) {
						case 'result_id':
							$headline_title = esc_attr__( 'ID', 'torro_locale' );
							$class = 'column-one';
							break;
						case 'user_id':
							$headline_title = esc_attr__( 'User', 'torro_locale' );
							$class = 'column-ten';
							break;
						case 'timestamp':
							$headline_title = esc_attr__( 'Date', 'torro_locale' );
							$class = 'column-ten';
							break;
						default:
							$headline_title = $headline;
							$class = 'column-ten';
					}
					$html .= '<th class="' . $class . '">' . $headline_title . '</th>';
				}
			}

			$html .= '<th class="export-links">' . sprintf( __( 'Export as <a href="%s">XLS</a> or <a href="%s">CSV</a>', 'torro-forms' ), admin_url( 'edit.php' ) . '?post_type=torro-forms&torro_export=xls&form_id=' . $form_id, admin_url( 'edit.php' ) . '?post_type=torro-forms&export=csv&form_id=' . $form_id ) . '</th>';
			$html .= '</tr>';
			$html .= '</thead>';

			$html .= '<tbody>';

			foreach ( $results as $result ) {
				$html .= '<tr>';
				foreach ( array_keys( $results[0] ) as $headline ) {
					if ( in_array( $headline, $entry_columns, true ) ) {
						switch ( $headline ) {
							case 'timestamp':
								$content = date_i18n( $date_format, $result[ $headline ] );
								break;
							case 'user_id':
								if( -1 !== (int) $result[ $headline ] ) {
									$user    = get_user_by( 'id', $result[ $headline ] );
									$content = $user->display_name;
								}else{
									$content = esc_html( 'not available', 'torro-forms' );
								}
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
				$html .= '<a type="button" class="button torro-show-entry" rel="' . $result['result_id'] . '" >' . esc_html__( 'Show Details', 'torro-forms' ) . '</a>';
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

			$html .= '<div class="torro-nav-prev-link">';
			if ( 0 <= $prev ) {
				$prev_url = $this->get_admin_url( $form_id, array(
					'torro-entries-start'	=> $prev,
					'torro-entries-length'	=> $length,
				) );
				$prev_link = sprintf( __( '<a href="%s" class="torro-nav-button button">Previous</a>', 'torro-forms' ), $prev_url );
				$html .= $prev_link;
			}
			$html .= '</div>';

			if( $num_results > 0 ) {
				$html .= '<div class="torro-nav-info">' . sprintf( esc_attr__( '%s - %s of %s', 'torro-forms' ), $start + 1, $count, $num_results ) . '</div>';
			}

			$html .= '<div class="torro-nav-next-link">';
			if ( $num_results > $next ) {
				$next_url = $this->get_admin_url( $form_id, array(
					'torro-entries-start'	=> $next,
					'torro-entries-length'	=> $length,
				) );
				$next_link = sprintf( __( '<a href="%s" class="torro-nav-button button">Next</a>', 'torro-forms' ), $next_url );
				$html .= $next_link;
			}
			$html .= '</div>';

			$html .= '</div>';
		} else {
			$html .= $this->show_not_found_notice();
		}

		return $html;
	}

	/**
	 * Returns the edit URL for a form, with optional query arguments
	 */
	private function get_admin_url( $form_id, $args = array() ) {
		$admin_url = admin_url( 'post.php?post=' . $form_id . '&action=edit' );
		if ( 0 < count( $args ) ) {
			$admin_url = add_query_arg( $args, $admin_url );
		}

		return $admin_url;
	}

	public function show_not_found_notice() {
		return '<p class="not-found-area">' . esc_html__( 'There are no Results to show.', 'torro-forms' ) . '</p>';
	}

	public function admin_styles() {
		if ( ! torro_is_formbuilder() ) {
			return;
		}

		wp_enqueue_style( 'torro-results-entries', torro()->get_asset_url( 'results-entries', 'css' ), array( 'torro-form-edit' ) );
	}

	public function admin_scripts() {
		if ( ! torro_is_formbuilder() ) {
			return;
		}

		$translation = array(
			'nonce_show_entries'	=> torro()->ajax()->get_nonce( 'show_entries' ),
			'nonce_show_entry'		=> torro()->ajax()->get_nonce( 'show_entry' ),
		);

		wp_enqueue_script( 'torro-results-entries', torro()->get_asset_url( 'results-entries', 'js' ), array( 'torro-form-edit' ) );
		wp_localize_script( 'torro-results-entries', 'translation_entries', $translation );
	}
}

torro()->resulthandlers()->register( 'Torro_ResultsEntries' );
