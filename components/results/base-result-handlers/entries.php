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
	/**
	 * Instance
	 *
	 * @var null|Torro_ResultsEntries
	 * @since 1.0.0
	 */
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

		torro()->ajax()->register_action( 'show_entries', array(
			'callback'		=> array( $this, 'ajax_show_entries' ),
		) );
		torro()->ajax()->register_action( 'show_entry', array(
			'callback'		=> array( $this, 'ajax_show_entry' ),
		) );
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

			$html .= '<th class="export-links">' . sprintf( __( 'Export as <a href="%s">XLS</a> or <a href="%s">CSV</a>', 'torro-forms' ), admin_url( 'edit.php' ) . '?post_type=torro_form&torro_export=xls&form_id=' . $form_id, admin_url( 'edit.php' ) . '?post_type=torro_form&export=csv&form_id=' . $form_id ) . '</th>';
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

	/**
	 * Showing entry list
	 *
	 * @param $data
	 *
	 * @return array|Torro_Error
	 * @since 1.0.0
	 */
	public function ajax_show_entries( $data ) {
		if ( ! isset( $data['form_id'] ) ) {
			return new Torro_Error( 'ajax_show_entries_form_id_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'form_id' ) );
		}

		if ( ! isset( $data['start'] ) ) {
			return new Torro_Error( 'ajax_show_entries_start_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'start' ) );
		}

		if ( ! isset( $data['length'] ) ) {
			return new Torro_Error( 'ajax_show_entries_length_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'length' ) );
		}

		$form_id = $data['form_id'];
		$start = $data['start'];
		$length = $data['length'];

		$form_results = new Torro_Form_Results( $form_id );
		$form_results->results();
		$num_results = $form_results->count();

		$response = array(
			'html'	=> torro()->resulthandlers()->get_registered( 'entries' )->show_results( $form_id, $start, $length, $num_results ),
		);

		return $response;
	}

	/**
	 * Showing single entry
	 *
	 * @param $data
	 *
	 * @return array|Torro_Error
	 * @since 1.0.0
	 * @todo Moving table HTML to entry class
	 */
	public function ajax_show_entry( $data ) {
		global $wpdb;

		if ( ! isset( $data['form_id'] ) ) {
			return new Torro_Error( 'ajax_show_entry_form_id_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'form_id' ) );
		}

		if ( ! isset( $data['result_id'] ) ) {
			return new Torro_Error( 'ajax_show_entry_result_id_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'result_id' ) );
		}

		$form_id = $data['form_id'];
		$result_id = $data['result_id'];

		if ( ! torro()->forms()->get( $form_id)->exists() ) {
			return array(
				'html'	=> __( 'Form not found.', 'torro-forms' ),
			);
		}

		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->torro_results WHERE id = %d", $result_id );
		$count_results = $wpdb->get_var( $sql );

		if ( 0 === $count_results ) {
			return array(
				'html'	=> __( 'Entry not found.', 'torro-forms' ),
			);
		}

		$filter = array(
			'result_ids' => array( $result_id ),
		);

		$form_results = new Torro_Form_Results( $form_id );
		$results = $form_results->results( $filter );

		if ( 0 < $form_results->count() ) {
			foreach ( $results as $result ) {
				if ( ! array_key_exists( 'result_id', $result ) ) {
					return array(
						'html'	=> __( 'Error on getting Result.', 'torro-forms' ),
					);
				}

				$html = '<table id="torro-entry-table" class="widefat">';

				$html .= '<thead>';
				$html .= '<tr>';
				$html .= '<th>' . esc_html__( 'Label', 'torro-forms' ) . '</th>';
				$html .= '<th>' . esc_html__( 'Value', 'torro-forms' ) . '</th>';
				$html .= '</tr>';
				$html .= '</thead>';

				$html .= '<tbody>';

				$extra_info = '';

				foreach ( $result as $column_name => $value ) {
					switch ( $column_name ) {
						case 'result_id':
							$result_id = $value;
							break;
						case 'user_id':
							if ( -1 !== (int) $value ) {
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
							if ( array_key_exists( 0, $column_arr ) && 'element' === $column_arr[0] ) {
								$element_id = $column_arr[ 1 ];
								$element = torro()->elements()->get( $element_id );

								$column_name = $element->replace_column_name( $column_name );

								if ( empty( $column_name ) ) {
									$column_name = $element->label;
								}

								if ( 'yes' === $value ) {
									$value = esc_attr__( 'Yes', 'torro-forms' );
								} elseif( 'no' == $value ) {
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
				$html .= '<td colspan="2"><small>' . esc_html__( 'Date', 'torro-forms' ) . ' ' . $date_string . ' - ' . esc_html__( 'Time', 'torro-forms' ) . ' ' . $time_string . $extra_info . '</small></td>';
				$html .= '</tr>';
				$html .= '</tfoot>';
				$html .= '</table>';
			}
		} else {
			$html = __( 'Entry not found.', 'torro-forms' );
		}

		$html .= '<div id="torro-entry-buttons">';
		$html .= '<input type="button" class="button torro-hide-entry" value="' . esc_attr__( 'Back to Results', 'torro-forms' ) . '">';
		$html .= '</div>';

		$response = array(
			'html'	=> $html,
		);

		return $response;
	}
}

torro()->resulthandlers()->register( 'Torro_ResultsEntries' );
