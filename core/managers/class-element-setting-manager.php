<?php
/**
 * Core: Torro_Element_Setting_Manager class
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
 * Torro Forms element setting manager class
 *
 * This class holds and manages all element setting class instances.
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Element_Setting_Manager extends Torro_Instance_Manager {

	/**
	 * Instance
	 *
	 * @var null|Torro_Element_Setting_Manager
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
		$this->table_name = 'torro_element_settings';
		$this->class_name = 'Torro_Element_Setting';
	}

	protected function get_category() {
		return 'element_settings';
	}
}
