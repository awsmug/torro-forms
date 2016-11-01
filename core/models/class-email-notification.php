<?php
/**
 * Core: Torro_Email_Notification class
 *
 * @package TorroForms
 * @subpackage CoreModels
 * @version 1.0.0-beta.7
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email notification class
 *
 * @since 1.0.0-beta.1
 *
 * @property string $notification_name
 * @property string $from_name
 * @property string $from_email
 * @property string $to_name
 * @property string $to_email
 * @property string $subject
 * @property string $message
 */
class Torro_Email_Notification extends Torro_Instance_Base {

	protected $notification_name = '';

	protected $from_name = '';

	protected $from_email = '';

	protected $reply_email = '';

	protected $to_name = '';

	protected $to_email = '';

	protected $subject = '';

	protected $message = '';

	protected function init() {
		$this->table_name = 'torro_email_notifications';
		$this->superior_id_name = 'form_id';
		$this->manager_method = 'email_notifications';
		$this->valid_args = array(
			'notification_name'	=> 'string',
			'from_name'			=> 'string',
			'from_email'		=> 'string',
			'reply_email'		=> 'string',
			'to_name'			=> 'string',
			'to_email'			=> 'string',
			'subject'			=> 'string',
			'message'			=> 'string',
		);
	}
}
