<?php
/**
 * Core: Torro_Extensions_Manager class
 *
 * @package TorroForms
 * @subpackage CoreManagers
 * @version 1.0.0-beta.6
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms extension manager class
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Extensions_Manager extends Torro_Manager {

	/**
	 * Instance
	 *
	 * @var null|Torro_Extensions_Manager
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
			'extensions' => 'Torro_Extension'
		);
		return $allowed;
	}

	protected function after_instance_added( $instance ) {
		$instance->check_and_start();
		return $instance;
	}

	protected function get_category() {
		return 'extensions';
	}
}
