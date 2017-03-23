<?php
/**
 * Components: Torro_Form_Setting_Timerange class
 *
 * @package    TorroForms
 * @subpackage Components
 * @version    1.0.0-beta.7
 * @since      1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Torro_Form_Setting_Timerange extends Torro_Form_Setting {
	/**
	 * Instance
	 *
	 * @since 1.0.0
	 * @var null|Torro_Form_Setting_Timerange
	 */
	private static $instance = null;

	/**
	 * Date format used in WordPress
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $date_format;

	/**
	 * Singleton.
	 *
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Timerange meta box
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id
	 *
	 * @return string $html
	 */
	public function option_content( $form_id ) {
		$start_date = get_post_meta( $form_id, 'start_date', true );
		$end_date   = get_post_meta( $form_id, 'end_date', true );

		// For compability with dates before 1.0.0 beta 8
		if ( ! is_numeric( $start_date ) ) {
			$start_date = strtotime( $start_date );
		}
		if ( ! is_numeric( $end_date ) ) {
			$end_date = strtotime( $end_date );
		}

		if ( ! empty( $start_date ) ) {
			$start_date = $this->get_date( $start_date );
		}
		if ( ! empty( $end_date ) ) {
			$end_date = $this->get_date( $end_date );
		}

		$html = '<div class="torro-form-options">';

		$html .= '<div class="flex-options" role="group">';
		$html .= '<label for="start_date">' . esc_html__( 'Input Start Date', 'torro-forms' ) . '</label>';
		$html .= '<div><input type="text" id="start_date" name="start_date" value="' . $start_date . '"/></div>';
		$html .= '</div>';

		$html .= '<div class="flex-options" role="group">';
		$html .= '<label for="end_date">' . esc_html__( 'Input End Date', 'torro-forms' ) . '</label>';
		$html .= '<div><input type="text" id="end_date" name="end_date" value="' . $end_date . '"/></div>';
		$html .= '</div>';

		$html .= '</div>';

		return $html;
	}

	/**
	 * Getting WordPress date for a timestamp
	 *
	 * @since 1.0.0
	 *
	 * @param int $timestamp
	 *
	 * @return string $date
	 */
	private function get_date( $timestamp ) {
		return date( $this->date_format, $timestamp );
	}

	/**
	 * Checks if the user can pass
	 *
	 * @since 1.0.0
	 *
	 * @param
	 *
	 * @return boolean
	 */
	public function check( $form_show ) {
		$form_id = torro()->forms()->get_current_form_id();

		$actual_date = time();
		$start_date  = get_post_meta( $form_id, 'start_date', true );
		$end_date    = get_post_meta( $form_id, 'end_date', true );

		// For compability with dates before 1.0.0 beta 8
		if ( ! is_numeric( $start_date ) ) {
			$start_date = strtotime( $start_date );
		}
		if ( ! is_numeric( $end_date ) ) {
			$end_date = strtotime( $end_date );
		}

		if ( ! empty( $start_date ) && $start_date > $actual_date ) {
			$this->add_message( 'error', __( 'The form is not accessible at this time.', 'torro-forms' ) );

			return $this->messages();
		}

		if ( ! empty( $end_date ) && $end_date < $actual_date ) {
			$this->add_message( 'error', __( 'The form is not accessible at this time.', 'torro-forms' ) );

			return $this->messages();
		}

		return $form_show;
	}

	/**
	 * Saving data
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id
	 */
	public function save( $form_id ) {
		$start_date = wp_unslash( $_POST[ 'start_date' ] );
		$end_date   = wp_unslash( $_POST[ 'end_date' ] );

		$start_date = $this->get_timestamp( $start_date );
		$end_date   = $this->get_timestamp( $end_date );

		/**
		 * Saving start and end date
		 */
		update_post_meta( $form_id, 'start_date', $start_date );
		update_post_meta( $form_id, 'end_date', $end_date );
	}

	/**
	 * Getting a timestamp from a WordPress generated date
	 *
	 * @since 1.0.0
	 *
	 * @param string $date
	 *
	 * @return int $timestamp
	 */
	private function get_timestamp( $date ) {
		$timestamp = strtotime( $date );
		return $timestamp;
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @since 1.0.0
	 */
	public function admin_scripts() {
		$translations = array(
			'dateformat'        => __( 'yy/mm/dd', 'torro-forms' ),
			'min_sun'           => __( 'Su', 'torro-forms' ),
			'min_mon'           => __( 'Mo', 'torro-forms' ),
			'min_tue'           => __( 'Tu', 'torro-forms' ),
			'min_wed'           => __( 'We', 'torro-forms' ),
			'min_thu'           => __( 'Th', 'torro-forms' ),
			'min_fri'           => __( 'Fr', 'torro-forms' ),
			'min_sat'           => __( 'Sa', 'torro-forms' ),
			'january'           => __( 'January', 'torro-forms' ),
			'february'          => __( 'February', 'torro-forms' ),
			'march'             => __( 'March', 'torro-forms' ),
			'april'             => __( 'April', 'torro-forms' ),
			'may'               => __( 'May', 'torro-forms' ),
			'june'              => __( 'June', 'torro-forms' ),
			'july'              => __( 'July', 'torro-forms' ),
			'august'            => __( 'August', 'torro-forms' ),
			'september'         => __( 'September', 'torro-forms' ),
			'october'           => __( 'October', 'torro-forms' ),
			'november'          => __( 'November', 'torro-forms' ),
			'december'          => __( 'December', 'torro-forms' ),
			'select_date'       => __( 'Select Date', 'torro-forms' ),
			'calendar_icon_url' => torro()->get_asset_url( 'calendar-icon', 'png' ),
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

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->option_name = $this->title = __( 'Timerange', 'torro-forms' );
		$this->name        = 'timerange';
		// Not working on getting timestamp from dateformat at the moment, using own format
		// $this->date_format = get_option( 'date_format' );
		$this->date_format = 'd.m.Y';

		add_action( 'torro_form_show', array( $this, 'check' ) );
	}
}

torro()->form_settings()->register( 'Torro_Form_Setting_Timerange' );
