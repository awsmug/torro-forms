<?php
/**
 * Core: Torro_Form_Settings_Manager class
 *
 * @package TorroForms
 * @subpackage CoreManagers
 * @version 1.0.0beta1
 * @since 1.0.0beta1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms form setting manager class
 *
 * @since 1.0.0beta1
 */
final class Torro_Form_Settings_Manager extends Torro_Manager {

	/**
	 * Instance
	 *
	 * @var null|Torro_Form_Settings_Manager
	 * @since 1.0.0
	 */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function __construct() {
		parent::__construct();
	}

	protected function allowed_modules(){
		$allowed = array(
			'form_setting' => 'Torro_Form_Setting'
		);
		return $allowed;
	}

	protected function get_category() {
		return 'form_setting';
	}
}
