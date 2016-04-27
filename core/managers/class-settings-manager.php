<?php
/**
 * Core: Torro_Settings_Manager class
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
 * Torro Forms settings manager class
 *
 * @since 1.0.0beta1
 */
final class Torro_Settings_Manager extends Torro_Manager {

	/**
	 * Instance
	 *
	 * @var null|Torro_Settings_Manager
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
			'settings' => 'Torro_Settings'
		);
		return $allowed;
	}

	protected function get_category() {
		return 'settings';
	}
}
