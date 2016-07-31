<?php
/**
 * Components: Torro_Form_Setting_Timerange class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.6
 * @since 1.0.0-beta.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Torro_Form_Setting_General extends Torro_Form_Setting {
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
		$this->option_name = $this->title = __( 'General', 'torro-forms' );
		$this->name = 'general';

		add_filter( 'torro_form_container_show_title', array( $this, 'show_page_title' ), 10, 2 );
		add_filter( 'torro_form_button_previous_step_text', array( $this, 'previous_button_text' ), 10, 2 );
		add_filter( 'torro_form_button_next_step_text', array( $this, 'next_button_text' ), 10, 2 );
		add_filter( 'torro_form_button_send_text', array( $this, 'send_button_text' ), 10, 2 );
	}

	/**
	 * Switching page title on/off in frontend
	 *
	 * @param $show
	 * @param $form_id
	 *
	 * @return bool
	 */
	public function show_page_title( $show, $form_id ) {
		$show_page_title = get_post_meta( $form_id, 'show_page_title', true );

		if( 'yes' === $show_page_title || empty( $show_page_title ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Filtering 'previous' button text
	 *
	 * @param string $text
	 * @param int $form_id
	 *
	 * @return string
	 */
	public function previous_button_text( $text, $form_id ) {
		$previous_button_text = get_post_meta( $form_id, 'previous_button_text', true );

		if( ! empty( $previous_button_text ) ) {
			return $previous_button_text;
		}

		return $text;
	}

	/**
	 * Filtering 'next' button text
	 *
	 * @param string $text
	 * @param int $form_id
	 *
	 * @return string
	 */
	public function next_button_text( $text, $form_id ) {
		$next_button_text = get_post_meta( $form_id, 'next_button_text', true );

		if( ! empty( $next_button_text ) ) {
			return $next_button_text;
		}

		return $text;
	}

	/**
	 * Filtering 'send' button text
	 *
	 * @param string $text
	 * @param int $form_id
	 *
	 * @return string
	 */
	public function send_button_text( $text, $form_id ) {
		$send_button_text = get_post_meta( $form_id, 'send_button_text', true );

		if( ! empty( $send_button_text ) ) {
			return $send_button_text;
		}

		return $text;
	}

	/**
	 * General options
	 *
	 * @param int $form_id
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	public function option_content( $form_id ) {
		$show_page_title = get_post_meta( $form_id, 'show_page_title', true );

		$previous_button_text = get_post_meta( $form_id, 'previous_button_text', true );
		$next_button_text = get_post_meta( $form_id, 'next_button_text', true );
		$send_button_text = get_post_meta( $form_id, 'send_button_text', true );

		$already_entered_text = get_post_meta( $form_id, 'already_entered_text', true );
		$not_allowed_text = get_post_meta( $form_id, 'not_allowed_text', true );
		$to_be_logged_in_text = get_post_meta( $form_id, 'to_be_logged_in_text', true );

		$allow_get_param = get_post_meta( $form_id, 'allow_get_param', true );

		$show_page_title_checked = '';
		if( 'yes' === $show_page_title || empty( $show_page_title )  ) {
			$show_page_title_checked = ' checked="checked"';
		}

		$allow_get_param_checked = '';
		if( 'yes' === $allow_get_param ) {
			$allow_get_param_checked = ' checked="checked"';
		}

		$html  = '<div class="torro-form-options">';
		$html .= '<h4>' . esc_html__( 'Page Title', 'torro-forms' ) . '</h4>';

		$html .= '<div class="flex-options" role="group">';
		$html .= '<label for="show_page_title">' . esc_html__( 'Show Page Title', 'torro-forms' ) . '</label>';
		$html .= '<div><input type="checkbox" id="show_page_title" name="show_page_title" value="yes" aria-describedby="show-page-title-desc" ' . $show_page_title_checked . ' />';
		$html .= '<div id="show-page-title-desc">' . esc_html__( 'Show title of page (e.g. Page 1) on top of the form.', 'torro-forms' ) .'</div></div>';
		$html .= '</div>';

		$html .= '<h4>' . esc_html__( 'Button Labels', 'torro-forms' ) . '</h4>';

		$html .= '<div class="flex-options" role="group">';
		$html .= '<label for="previous_button_text">' . esc_html__( 'Previous', 'torro-forms' ) . '</label>';
		$html .= '<div><input type="text" id="previous_button_text" name="previous_button_text" value="' . $previous_button_text . '" placeholder="' . esc_attr__( 'Back', 'torro-forms' ) . '" /></div>';
		$html .= '</div>';

		$html .= '<div class="flex-options" role="group">';
		$html .= '<label for="next_button_text">' . esc_html__( 'Next', 'torro-forms' ) . '</label>';
		$html .= '<div><input type="text" id="next_button_text" name="next_button_text" value="' . $next_button_text . '" placeholder="' . esc_attr__( 'Next', 'torro-forms' ) . '" /></div>';
		$html .= '</div>';

		$html .= '<div class="flex-options" role="group">';
		$html .= '<label for="send_button_text">' . esc_html__( 'Send', 'torro-forms' ) . '</label>';
		$html .= '<div><input type="text" id="send_button_text" name="send_button_text" value="' . $send_button_text . '" placeholder="' . esc_attr__( 'Send', 'torro-forms' ) . '" /></div>';
		$html .= '</div>';

		$html .= '<h4>' . esc_html__( 'Messages', 'torro-forms' ) . '</h4>';

		$html .= '<div class="flex-options" role="group">';
		$html .= '<label for="already_entered_text">' . esc_html__( 'Already entered data', 'torro-forms' ) . '</label>';
		$html .= '<div><input type="text" id="already_entered_text" name="already_entered_text" value="' . $already_entered_text . '" placeholder="' .  esc_attr( 'You have already entered your data.', 'torro-forms' ) . '" /></div>';
		$html .= '</div>';

		$html .= '<div class="flex-options" role="group">';
		$html .= '<label for="not_allowed_text">' . esc_html__( 'Not allowed', 'torro-forms' ) . '</label>';
		$html .= '<div><input type="text" id="not_allowed_text" name="not_allowed_text" value="' . $not_allowed_text . '" placeholder="' .  esc_attr( 'You are not allowed to participate.', 'torro-forms' ) . '" /></div>';
		$html .= '</div>';

		$html .= '<div class="flex-options" role="group">';
		$html .= '<label for="to_be_logged_in_text">' . esc_html__( 'Need to login', 'torro-forms' ) . '</label>';
		$html .= '<div><input type="text" id="to_be_logged_in_text" name="to_be_logged_in_text" value="' . $to_be_logged_in_text . '" placeholder="' .  esc_attr( 'You have to be logged in to participate.', 'torro-forms' ) . '" /></div>';
		$html .= '</div>';

		$html .= '<h4>' . esc_html__( 'Misc', 'torro-forms' ) . '</h4>';

		$html .= '<div class="flex-options" role="group">';
		$html .= '<label for="allow_get_param">' . esc_html__( 'Allow $_GET parameters', 'torro-forms' ) . '</label>';
		$html .= '<div><input type="checkbox" id="allow_get_param" name="allow_get_param" value="yes" aria-describedby="show-allow-get-param-desc" ' . $allow_get_param_checked . ' />';
		$html .= '<div id="show-allow-get-param-desc">' . esc_html__( 'Allow setting of element values by $_GET parameter (by using ?torro_input_value_ELEMENT_ID=VALUE).', 'torro-forms' ) .'</div></div>';
		$html .= '</div>';

		ob_start();
		do_action( 'torro_formbuilder_options' );
		$html .= ob_get_clean();

		$html .= '</div>';

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
		$show_page_title = 'no';
		if( isset( $_POST['show_page_title']  ) ) {
			$show_page_title = wp_unslash( $_POST[ 'show_page_title' ] );
		}
		$next_button_text = wp_unslash( $_POST['next_button_text'] );
		$previous_button_text = wp_unslash( $_POST['previous_button_text'] );
		$send_button_text = wp_unslash( $_POST['send_button_text'] );

		$already_entered_text = wp_unslash( $_POST['already_entered_text'] );
		$not_allowed_text = wp_unslash( $_POST['not_allowed_text'] );
		$to_be_logged_in_text = wp_unslash( $_POST['to_be_logged_in_text'] );

		$allow_get_param= 'no';
		if( isset( $_POST['show_page_title']  ) ) {
			$allow_get_param = wp_unslash( $_POST[ 'allow_get_param' ] );
		}

		/**
		 * Saving start and end date
		 */
		update_post_meta( $form_id, 'show_page_title', $show_page_title );

		update_post_meta( $form_id, 'next_button_text', $next_button_text );
		update_post_meta( $form_id, 'previous_button_text', $previous_button_text );
		update_post_meta( $form_id, 'send_button_text', $send_button_text );

		update_post_meta( $form_id, 'already_entered_text', $already_entered_text );
		update_post_meta( $form_id, 'not_allowed_text', $not_allowed_text );
		update_post_meta( $form_id, 'to_be_logged_in_text', $to_be_logged_in_text );

		update_post_meta( $form_id, 'allow_get_param', $allow_get_param );
	}
}

torro()->form_settings()->register( 'Torro_Form_Setting_General' );
