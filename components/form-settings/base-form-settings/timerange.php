<?php
/**
 * Components: Torro_Form_Setting_Timerange class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.5
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Torro_Form_Setting_Timerange extends Torro_Form_Setting {
	/**
	 * Instance
	 *
	 * @var null|Torro_Form_Setting_Timerange
	 * @since 1.0.0
	 */
	private static $instance = null;

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
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->option_name = $this->title = __( 'Timerange', 'torro-forms' );
		$this->name = 'timerange';

		add_action( 'torro_form_show', array( $this, 'check' ) );
	}

	/**
	 * Timerange meta box
	 *
	 * @param int $form_id
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	public function option_content( $form_id ) {
		$start_date = get_post_meta( $form_id, 'start_date', true );
		$end_date = get_post_meta( $form_id, 'end_date', true );

		$html  = '<div class="torro-form-options">';

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
	 * Checks if the user can pass
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	public function check( $form_show ) {
		$form_id = torro()->forms()->get_current_form_id();

		$actual_date = time();
		$start_date = get_post_meta( $form_id, 'start_date', true );
		$end_date = get_post_meta( $form_id, 'end_date', true );

		if ( ! empty( $start_date ) && strtotime( $start_date ) > $actual_date ) {
			$this->add_message( 'error', __( 'The form is not accessible at this time.', 'torro-forms' ) );
			return $this->messages();
		}

		if ( ! empty( $end_date )  && strtotime( $end_date ) < $actual_date ) {
			$this->add_message( 'error', __( 'The form is not accessible at this time.', 'torro-forms' ) );
			return $this->messages();
		}

		return $form_show;
	}

	/**
	 * Saving data
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public function save( $form_id ) {
		$start_date = wp_unslash( $_POST['start_date'] );
		$end_date = wp_unslash( $_POST['end_date'] );

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
