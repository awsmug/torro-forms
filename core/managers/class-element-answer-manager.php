<?php
/**
 * Core: Torro_Element_Answer_Manager class
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
 * Torro Forms element answer manager class
 *
 * This class holds and manages all element answer class instances.
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Element_Answer_Manager extends Torro_Instance_Manager {

	/**
	 * Instance
	 *
	 * @var null|Torro_Element_Answer_Manager
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
		$this->table_name = 'torro_element_answers';
		$this->class_name = 'Torro_Element_Answer';
	}

	protected function get_category() {
		return 'element_answers';
	}
}
