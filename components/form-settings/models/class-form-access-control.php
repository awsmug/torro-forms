<?php
/**
 * Components: Torro_Form_Access_Control class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.5
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Torro_Form_Access_Control extends Torro_Base {
	/**
	 * Option name
	 *
	 * @since 1.0.0
	 */
	protected $option_name = false;

	/**
	 * Settings name
	 *
	 * @since 1.0.0
	 */
	protected $settings_name = 'form_settings';

	/**
	 * Message
	 *
	 * @since 1.0.0
	 */
	protected $messages = array();

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();

		add_action( 'torro_formbuilder_save', array( $this, 'save' ), 10, 1 );
	}

	protected function init() {
		$this->title = __( 'Form Restrictions', 'torro-forms' );
		$this->name = 'access_controls';
	}

	/**
	 * Checks if the user can pass
	 *
	 * @param int $form_id
	 */
	abstract function check( $form_id );

	/**
	 * Adds a Restriction option to the access-controls meta box
	 *
	 * @return bool
	 */
	public function has_option() {
		if ( false !== $this->option_name ) {
			return true;
		}

		return false;
	}

	/**
	 * Adds content to the option
	 *
	 * @param int $form_id
	 *
	 * @return string $html
	 */
	public function option_content( $form_id ) {
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
		do_action( 'form_access_controls_content_top' );
		$html = ob_get_clean();

		$html .= '<div class="section">';
		$html .= '<div id="form-access-controls-options">';
		$html .= '<label for"form_access_controls_option">' . esc_html__( 'Who has access to this form?', 'torro-forms' ) . '';
		$html .= '<select name="form_access_controls_option" id="form-access-controls-option">';
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
		$html .= '</select></label>';
		$html .= '</div>';

		/**
		 * Option content
		 */
		foreach ( $access_controls as $name => $access_control ) {
			$option_content = $access_control->option_content( $form_id );
			if ( ! $access_control->has_option() || ! $option_content ) {
				continue;
			}
			$html .= '<div id="form-access-controls-content-' . $access_control->name . '" class="form-access-controls-content form-access-controls-content-' . $access_control->name . '">' . $option_content . '</div>';
		}

		$html.= '</div>';

		ob_start();
		do_action( 'form_access_controls_content_bottom' );
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
	public function save( $form_id ) {}


	/**
	 * Printing out messages
	 */
	public function messages() {
		if ( 0 < count( $this->messages ) ) {
			$html = '';
			foreach ( $this->messages as $message ) {
				$html .= '<div class="form-message ' . $message['type'] . '">' . esc_html( $message['text'] ) . '</div>';
			}

			return $html;
		}

		return false;
	}

	/**
	 * Adding messages
	 *
	 * @param $type
	 * @param $text
	 */
	public function add_message( $type, $text ) {
		$this->messages[] = array(
			'type'	=> $type,
			'text'	=> $text
		);
	}
}
