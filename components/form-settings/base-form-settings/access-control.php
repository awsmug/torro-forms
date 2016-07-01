<?php
/**
 * Components: Torro_Form_Setting_Access_Control class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.6
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Torro_Form_Setting_Access_Control extends Torro_Form_Setting {
	/**
	 * Instance
	 *
	 * @var null|Torro_Form_Setting_Access_Control
	 * @since 1.0.0
	 */
	private static $instance = null;

	/**
	 * Settings fields array
	 *
	 * @since 1.0.0
	 */
	protected $settings_name = 'visitors';

	/**
	 * Singleton
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
	 * Initializing
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->option_name = $this->title = __( 'Access Control', 'torro-forms' );
		$this->name = 'access_control';
	}

	/**
	 * Adds content to the option
	 *
	 * @param int $form_id
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	public function option_content( $form_id ) {
		$access_controls = torro()->access_controls()->get_all_registered();

		if ( ! is_array( $access_controls ) || 0 === count( $access_controls ) ) {
			return;
		}

		/**
		 * Select field for Restriction
		 */
		$access_controls_option = get_post_meta( $form_id, 'access_controls_option', true );

		if ( empty( $access_controls_option ) ) {
			$access_controls_option = 'allvisitors';
		}

		ob_start();
		do_action( 'torro_form_setting_visitors_content_top' );
		$html = ob_get_clean();

		$html .= '<div class="torro-form-options">';
		$html .= '<div class="flex-options" role="group">';
		$html .= '<label for="form_access_controls_option">' . esc_html__( 'Give access to', 'torro-forms' ) . '</label>';
		$html .= '<div><select name="form_access_controls_option" id="form-access-controls-option" aria-describedby="form-access-controls-option-desc">';
		foreach ( $access_controls as $name => $access_control ) {
			if ( ! $access_control->has_option() ) {
				continue;
			}
			$selected = '';
			if ( $name === $access_controls_option ) {
				$selected = ' selected="selected"';
			}
			$html .= '<option value="' . $name . '"' . $selected . '>' . $access_control->option_name . '</option>';
		}
		$html .= '</select>';
		$html .= '<div id="form-access-controls-option-desc">' . __( 'Users which are not listet will be forbidden to access the form.', 'torro-forms' ) . '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';

		/**
		 * Option content
		 */
		foreach ( $access_controls as $name => $access_control ) {
			$html .= '<div id="form-access-controls-content-' . $access_control->name . '" class="form-access-controls-content form-access-controls-content-' . $access_control->name . '">' . $access_control->option_content( $form_id ) . '</div>';
		}

		ob_start();
		do_action( 'torro_form_setting_access_controls_content_bottom' );
		$html .= ob_get_clean();

		return $html;
	}

	/**
	 * Saving data
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public function save( $form_id ) {
		/**
		 * Saving access-control options
		 */
		$access_controls_option = wp_unslash( $_POST['form_access_controls_option'] );
		update_post_meta( $form_id, 'access_controls_option', $access_controls_option );
	}
}

torro()->form_settings()->register( 'Torro_Form_Setting_Access_Control' );
