<?php
/**
 * Core: Torro_Email_Notifications_Manager class
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
 * Torro Forms email notification manager class
 *
 * This class holds and manages all email notification class instances.
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Email_Notifications_Manager extends Torro_Instance_Manager {

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
		$this->table_name = 'torro_email_notifications';
		$this->class_name = 'Torro_Email_Notification';
	}

	protected function get_category() {
		return 'email_notifications';
	}
}
