<?php
/**
 * Core: Torro_Results_Manager class
 *
 * @package TorroForms
 * @subpackage CoreManagers
 * @version 1.0.0-beta.7
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms result manager class
 *
 * This class holds and manages all result class instances.
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Results_Manager extends Torro_Instance_Manager {

	/**
	 * Instance
	 *
	 * @var null|Torro_Results_Manager
	 * @since 1.0.0
	 */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected function init() {
		$this->table_name = 'torro_results';
		$this->class_name = 'Torro_Result';
	}

	protected function get_category() {
		return 'results';
	}
}
