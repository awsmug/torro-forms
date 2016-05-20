<?php
/**
 * Components: Torro_Result_Entries class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.2
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Torro_Result_Entries extends Torro_Form_Result {
	/**
	 * Instance
	 *
	 * @var null|Torro_Result_Entries
	 * @since 1.0.0
	 */
	private static $instance = null;

	/**
	 * Singleton
	 *
	 * @return null|Torro_Result_Entries
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initializing
	 *
	 * @since 1.0.0
	 */
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

	/**
	 * Option content
	 *
	 * @param int $form_id
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function option_content( $form_id ) {
		$start = 0;
		if ( isset( $_POST['torro-entries-start'] ) ) {
			$start = absint( $_POST['torro-entries-start'] );
		}

		$length = 10;
		if ( isset( $_POST['torro-entries-length'] ) ) {
			$length = absint( $_POST['torro-entries-length'] );
		}

		$html = '<div id="torro-entries" class="torro-table-nav">';
		$html .= '<div class="torro-slider">';
		$html .= '<div class="torro-slider-middle">';

		$html .= $this->show_results( $form_id, $start, $length );

		$html .= '</div>';
		$html .= '<div class="torro-slider-right"></div>';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Showing results
	 *
	 * @param int $form_id
	 * @param int $start
	 * @param int $length
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	public function show_results( $form_id, $start, $length ) {
		$results = torro()->results()->query( array(
			'number'	=> $length,
			'offset'	=> $start,
			'form_id'	=> $form_id,
		) );

		$total_count = torro()->results()->query( array(
			'number'	=> -1,
			'count'		=> true,
			'form_id'	=> $form_id,
		) );

		if ( 0 === count( $results ) ) {
			return $this->show_not_found_notice();
		}

		$columns = $this->get_columns();

		$html = '<table id="torro-entries-table" class="widefat entries">';
		$html .= '<thead>';
		$html .= '<tr>';

		foreach ( $columns as $key => $data ) {
			$class = isset( $data['class'] ) ? $data['class'] : 'column-ten';
			if ( ! empty( $class ) ) {
				$class .= ' ';
			}
			$class .= 'column-' . $key;

			$html .= '<th class="' . $class . '">' . esc_html( $data['title'] ) . '</th>';
		}

		$html .= '<th class="export-links">' . sprintf( __( 'Export as <a href="%s">XLS</a> or <a href="%s">CSV</a>', 'torro-forms' ), admin_url( 'edit.php' ) . '?post_type=torro_form&torro_export=xls&form_id=' . $form_id, admin_url( 'edit.php' ) . '?post_type=torro_form&torro_export=csv&form_id=' . $form_id ) . '</th>';
		$html .= '</tr>';
		$html .= '</thead>';

		$html .= '<tbody>';

		foreach ( $results as $result ) {
			$html .= '<tr>';

			foreach ( $columns as $key => $data ) {
				$val = '';
				if ( isset( $data['callback'] ) && is_callable( $data['callback'] ) ) {
					$val = call_user_func( $data['callback'], $result );
				} elseif ( isset( $data['raw_callback'] ) && is_callable( $data['raw_callback'] ) ) {
					$val = call_user_func( $data['raw_callback'], $result );
				}
				$html .= '<td class="column-' . $key . '">' . $val . '</td>';
			}

			$html .= '<td class="entry-actions">';
			$html .= '<a type="button" class="button torro-show-entry" rel="' . $result->id . '" >' . esc_html__( 'Show Details', 'torro-forms' ) . '</a>';
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
		$count = $total_count <= $length ? $total_count : $length;

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

		if( $total_count > 0 ) {
			$html .= '<div class="torro-nav-info">' . sprintf( esc_attr__( '%s - %s of %s', 'torro-forms' ), $start + 1, $count, $total_count ) . '</div>';
		}

		$html .= '<div class="torro-nav-next-link">';
		if ( $total_count > $next ) {
			$next_url = $this->get_admin_url( $form_id, array(
				'torro-entries-start'	=> $next,
				'torro-entries-length'	=> $length,
			) );
			$next_link = sprintf( __( '<a href="%s" class="torro-nav-button button">Next</a>', 'torro-forms' ), $next_url );
			$html .= $next_link;
		}
		$html .= '</div>';

		$html .= '</div>';

		return $html;
	}

	public function show_not_found_notice() {
		return '<p class="not-found-area">' . esc_html__( 'There are no results to show.', 'torro-forms' ) . '</p>';
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

	public function admin_styles() {
		if ( ! torro()->is_formbuilder() ) {
			return;
		}

		wp_enqueue_style( 'torro-results-entries', torro()->get_asset_url( 'results-entries', 'css' ), array( 'torro-form-edit' ) );
	}

	public function admin_scripts() {
		if ( ! torro()->is_formbuilder() ) {
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

		$form_id = absint( $data['form_id'] );
		$start = absint( $data['start'] );
		$length = absint( $data['length'] );

		$response = array(
			'html'	=> $this->show_results( $form_id, $start, $length ),
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
		if ( ! isset( $data['form_id'] ) ) {
			return new Torro_Error( 'ajax_show_entry_form_id_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'form_id' ) );
		}

		if ( ! isset( $data['result_id'] ) ) {
			return new Torro_Error( 'ajax_show_entry_result_id_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'result_id' ) );
		}

		$form_id = absint( $data['form_id'] );
		$result_id = absint( $data['result_id'] );

		if ( ! torro()->forms()->exists( $form_id ) ) {
			return array(
				'html'	=> __( 'Form not found.', 'torro-forms' ),
			);
		}

		$result = torro()->results()->get( $result_id );
		if ( is_wp_error( $result ) ) {
			return array(
				'html'	=> __( 'Entry not found.', 'torro-forms' ),
			);
		}

		$user = get_user_by( 'id', $result->user_id );

		$date_string = date_i18n( get_option( 'date_format' ), $result->timestamp );
		$time_string = date_i18n( get_option( 'time_format' ), $result->timestamp );
		$extra_info = '';
		if ( $user ) {
			$extra_info .= ' - ' . sprintf( __( 'User %s', 'torro-forms' ), $user->user_nicename );
		}


		$html = '<table id="torro-entry-table" class="widefat">';

		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th>' . esc_html__( 'Label', 'torro-forms' ) . '</th>';
		$html .= '<th>' . esc_html__( 'Value', 'torro-forms' ) . '</th>';
		$html .= '</tr>';
		$html .= '</thead>';

		$html .= '<tbody>';

		foreach ( $result->values as $result_value ) {
			$value = $result_value->value;
			if ( is_callable( array( $result_value->element, 'render_value' ) ) ) {
				$value = call_user_func( array( $result_value->element, 'render_value' ), $value );
			}

			$html .= '<tr>';
			$html .= '<td>' . $result_value->element->label . '</td>';
			$html .= '<td>' . $value . '</td>';
			$html .= '</tr>';
		}

		$html .= '</tbody>';
		$html .= '<tfoot>';
		$html .= '<tr>';
		$html .= '<td colspan="2"><small>' . esc_html__( 'Date', 'torro-forms' ) . ' ' . $date_string . ' - ' . esc_html__( 'Time', 'torro-forms' ) . ' ' . $time_string . $extra_info . '</small></td>';
		$html .= '</tr>';
		$html .= '</tfoot>';
		$html .= '</table>';

		$html .= '<div id="torro-entry-buttons">';
		$html .= '<input type="button" class="button torro-hide-entry" value="' . esc_attr__( 'Back to Results', 'torro-forms' ) . '">';
		$html .= '</div>';

		$response = array(
			'html'	=> $html,
		);

		return $response;
	}
}

torro()->resulthandlers()->register( 'Torro_Result_Entries' );
