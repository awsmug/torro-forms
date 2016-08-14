<?php
/**
 * Components: Torro_Form_Setting class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.7
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Torro_Form_Setting extends Torro_Base {
	/**
	 * Option name
	 *
	 * @since 1.0.0
	 */
	protected $option_name = false;

	/**
	 * Settings fields array
	 *
	 * @since 1.0.0
	 */
	protected $settings_name = 'settings';

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

	/**
	 * Adds a Restriction option to the access-controls meta box
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function has_option() {
		$reflector = new ReflectionMethod( $this, 'option_content' ) ;
		return ( $reflector->getDeclaringClass()->getName() !== __CLASS__ );
	}

	/**
	 * Adds content to the option
	 *
	 * @since 1.0.0
	 */
	public function option_content( $form_id ) {
		return false;
	}

	/**
	 * Saving data from option_content
	 *
	 * @param $form_id
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
