<?php
/**
 * Components: Torro_Form_Action class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.2
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Torro_Form_Action extends Torro_Base {
	/**
	 * Settings name
	 *
	 * @since 1.0.0
	 */
	protected $settings_name = 'actions';

	/**
	 * Contains the option_content
	 *
	 * @since 1.0.0
	 */
	protected $option_content = null;

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
	 * Handles the data after user submitted the form
	 *
	 * @param int $form_id
	 * @param int $response_id
	 * @param array $response
	 *
	 * @return mixed
	 * since 1.0.0
	 */
	public function handle( $form_id, $response_id, $response ){
		return false;
	}

	/**
	 * Will be displayed on page after submitting data
	 *
	 * @param int $form_id
	 * @param $int $response_id
	 * @param array $response
	 *
	 * @return string $html
	 * since 1.0.0
	 */
	public function notification( $form_id, $response_id, $response ){
		return false;
	}

	/**
	 * Checks if there is an option content
	 */
	public function has_option() {
		$reflector = new ReflectionMethod( $this, 'option_content' ) ;
		return ( $reflector->getDeclaringClass()->getName() !== __CLASS__ );
	}

	/**
	 * Content of option in Form builder
	 *
	 * @param int $form_id
	 * @return string $html
	 *
	 * @since 1.0.0
	 */
	public function option_content( $form_id ) {
		return null;
	}

	/**
	 * Saving data from option_content
	 *
	 * @param $form_id
	 * @since 1.0.0
	 */
	public function save( $form_id ) {}
}
