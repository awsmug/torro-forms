<?php
/**
 * Core: Torro_Email_Notifications_Manager class
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
 * Torro Forms email notification manager class
 *
 * This class holds and manages all email notification class instances.
 *
 * @since 1.0.0beta1
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

	protected function __construct() {
		parent::__construct();
	}

	public function create( $form_id, $args = array() ) {
		return parent::create( $form_id, $args );
	}

	public function move( $id, $form_id ) {
		return parent::move( $id, $form_id );
	}

	public function copy( $id, $form_id ) {
		return parent::copy( $id, $form_id );
	}

	protected function init() {
		$this->table_name = 'torro_email_notifications';
		$this->class_name = 'Torro_Email_Notification';
	}

	protected function get_category() {
		return 'email_notifications';
	}
}
