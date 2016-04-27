<?php
/**
 * Core: Torro_Result_Handlers_Manager class
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
 * Torro Forms result handler manager class
 *
 * @since 1.0.0beta1
 */
final class Torro_Result_Handlers_Manager extends Torro_Manager {

	/**
	 * Instance
	 *
	 * @var Torro
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
			'resulthandlers' => 'Torro_Result_Handler',
			'resultcharts' => 'Torro_Result_Charts'
		);
		return $allowed;
	}

	protected function get_category() {
		return 'resulthandlers';
	}
}
