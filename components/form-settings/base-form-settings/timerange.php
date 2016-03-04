<?php
/**
 * Restrict form to a timerange
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Restrictions
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

final class Torro_Form_Setting_Timerange extends Torro_Form_Setting {
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
		$this->option_name = $this->title = __( 'Timerange', 'torro-forms' );
		$this->name = 'timerange';

		add_action( 'torro_formbuilder_save', array( $this, 'save' ), 10, 1 );

		add_action( 'torro_additional_access_controls_check_start', array( $this, 'check' ) );
	}

	/**
	 * Timerange meta box
	 */
	public function option_content() {
		global $post;

		$form_id = $post->ID;

		$start_date = get_post_meta( $form_id, 'start_date', true );
		$end_date = get_post_meta( $form_id, 'end_date', true );

		$html = '<div id="form-access-controls-content-timerange" class="general-settings timerange">';

		$html .= '<div class="option">';
		$html .= '<label for="start_date">' . esc_html__( 'Date Start:', 'torro-forms' ) . '</label>';
		$html .= '<input type="text" id="start_date" name="start_date" value="' . $start_date . '"/>';
		$html .= '</div>';

		$html .= '<div class="option">';
		$html .= '<label for="end_date">' . esc_html__( 'Date End:', 'torro-forms' ) . '</label>';
		$html .= '<input type="text" id="end_date" name="end_date" value="' . $end_date . '"/>';
		$html .= '</div>';

		$html .= '<div style="clear:both"></div>';

		$html .= '</div>';

		return $html;
	}

	/**
	 * Checks if the user can pass
	 */
	public function check() {
		$torro_form_id = torro()->forms()->get_current_form_id();

		$actual_date = time();
		$start_date = get_post_meta( $torro_form_id, 'start_date', true );
		$end_date = get_post_meta( $torro_form_id, 'end_date', true );

		if ( ! empty( $start_date ) && strtotime( $start_date ) > $actual_date ) {
			$this->add_message( 'error', __( 'The Form is not accessible at this time.', 'torro-forms' ) );
			echo $this->messages();

			return false;
		}

		if ( ! empty( $end_date )  && strtotime( $end_date ) < $actual_date ) {
			$this->add_message( 'error', __( 'The Form is not accessible at this time.', 'torro-forms' ) );
			echo $this->messages();

			return false;
		}

		return true;
	}

	/**
	 * Saving data
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public function save( $form_id ) {
		$start_date = $_POST[ 'start_date' ];
		$end_date = $_POST[ 'end_date' ];

		/**
		 * Saving start and end date
		 */
		update_post_meta( $form_id, 'start_date', $start_date );
		update_post_meta( $form_id, 'end_date', $end_date );
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @since 1.0.0
	 */
	public function admin_scripts() {
		$translations = array(
			'dateformat'		=> __( 'yy/mm/dd', 'torro-forms' ),
			'min_sun'			=> __( 'Su', 'torro-forms' ),
			'min_mon'			=> __( 'Mo', 'torro-forms' ),
			'min_tue'			=> __( 'Tu', 'torro-forms' ),
			'min_wed'			=> __( 'We', 'torro-forms' ),
			'min_thu'			=> __( 'Th', 'torro-forms' ),
			'min_fri'			=> __( 'Fr', 'torro-forms' ),
			'min_sat'			=> __( 'Sa', 'torro-forms' ),
			'january'			=> __( 'January', 'torro-forms' ),
			'february'			=> __( 'February', 'torro-forms' ),
			'march'				=> __( 'March', 'torro-forms' ),
			'april'				=> __( 'April', 'torro-forms' ),
			'may'				=> __( 'May', 'torro-forms' ),
			'june'				=> __( 'June', 'torro-forms' ),
			'july'				=> __( 'July', 'torro-forms' ),
			'august'			=> __( 'August', 'torro-forms' ),
			'september'			=> __( 'September', 'torro-forms' ),
			'october'			=> __( 'October', 'torro-forms' ),
			'november'			=> __( 'November', 'torro-forms' ),
			'december'			=> __( 'December', 'torro-forms' ),
			'select_date'		=> __( 'Select Date', 'torro-forms' ),
			'calendar_icon_url'	=> torro()->get_asset_url( 'calendar-icon', 'png' ),
		);

		wp_enqueue_script( 'torro-access-controls-timerange', torro()->get_asset_url( 'access-controls-timerange', 'js' ), array( 'torro-form-edit', 'jquery-ui-datepicker' ) );
		wp_localize_script( 'torro-access-controls-timerange', 'translation_tr', $translations );
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public function admin_styles() {
		wp_enqueue_style( 'torro-access-controls-timerange', torro()->get_asset_url( 'access-controls-timerange', 'css' ), array( 'torro-form-edit' ) );
	}
}

torro()->form_settings()->register( 'Torro_Form_Setting_Timerange' );
