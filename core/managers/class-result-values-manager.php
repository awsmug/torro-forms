<?php
/**
 * Core: Torro_Result_Values_Manager class
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
 * Torro Forms result value manager class
 *
 * This class holds and manages all result value class instances.
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Result_Values_Manager extends Torro_Instance_Manager {

	/**
	 * Instance
	 *
	 * @var null|Torro_Result_Values_Manager
	 * @since 1.0.0-beta.1
	 */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected function init() {
		$this->table_name = 'torro_result_values';
		$this->class_name = 'Torro_Result_Value';
	}

	protected function get_category() {
		return 'resultvalues';
	}
}
