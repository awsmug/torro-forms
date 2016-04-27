<?php
/**
 * Core: Torro_Email_Notifications_Manager class
 *
 * @package TorroForms
 * @subpackage CoreManagers
 * @version 1.0.0-beta.1
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

	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Creates a new email notification.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int   $form_id
	 * @param array $args
	 *
	 * @return Torro_Email_Notification|Torro_Error
	 */
	public function create( $form_id, $args = array() ) {
		return parent::create( $form_id, $args );
	}

	/**
	 * Updates an existing email notification.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int   $id
	 * @param array $args
	 *
	 * @return Torro_Email_Notification|Torro_Error
	 */
	public function update( $id, $args = array() ) {
		return parent::update( $id, $args );
	}

	/**
	 * Gets an email notification.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 *
	 * @return Torro_Email_Notification|Torro_Error
	 */
	public function get( $id ) {
		return parent::get( $id );
	}

	/**
	 * Moves an email notification to another form.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 * @param int $form_id
	 *
	 * @return Torro_Email_Notification|Torro_Error
	 */
	public function move( $id, $form_id ) {
		return parent::move( $id, $form_id );
	}

	/**
	 * Copies an email notification to another form.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 * @param int $form_id
	 *
	 * @return Torro_Email_Notification|Torro_Error
	 */
	public function copy( $id, $form_id ) {
		return parent::copy( $id, $form_id );
	}

	/**
	 * Deletes an email notification.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 *
	 * @return Torro_Email_Notification|Torro_Error
	 */
	public function delete( $id ) {
		return parent::delete( $id );
	}

	protected function init() {
		$this->table_name = 'torro_email_notifications';
		$this->class_name = 'Torro_Email_Notification';
	}

	protected function get_category() {
		return 'email_notifications';
	}
}
