<?php
/**
 * Core: Torro_Containers_Manager class
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
 * Torro Forms container manager class
 *
 * This class holds and manages all container class instances.
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Containers_Manager extends Torro_Instance_Manager {

	/**
	 * Instance
	 *
	 * @var Torro_Containers_Manager
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
		$this->table_name = 'torro_containers';
		$this->class_name = 'Torro_Container';
	}

	protected function get_category() {
		return 'containers';
	}
}
