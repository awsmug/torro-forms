<?php
/**
 * Core: Torro_Form_Access_Controls_Manager class
 *
 * @package TorroForms
 * @subpackage CoreManagers
 * @version 1.0.0-beta.3
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms access-control manager class
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Form_Access_Controls_Manager extends Torro_Manager {
	/**
	 * Instance
	 *
	 * @var null|Torro_Form_Access_Controls_Manager
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
			'access_controls' => 'Torro_Form_Access_Control'
		);
		return $allowed;
	}

	protected function get_category() {
		return 'access_controls';
	}
}
