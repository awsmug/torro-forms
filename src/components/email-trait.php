<?php

/**
 * Email trait
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Components;

use WP_Error;

trait Email_Trait {
	/**
	 * Temporary storage for email from name.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $email_from_name = '';

	/**
	 * Temporary storage for email from email.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $email_from_email = '';

	/**
	 * Temporary storage for email to email.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $email_to_email = '';

	/**
	 * Temporary storage for email subject.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $email_subject = '';

	/**
	 * Temporary storage for email message
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $email_message = '';

	/**
	 * Temporary storage for email headers
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $email_headers = '';

	/**
	 * Temporary storage for PHPMailer error object.
	 *
	 * @since 1.1.0
	 * @var WP_Error|null
	 */
	private $phpmailer_error = null;

	/**
	 * Setup Email to send.
	 *
	 * @since 1.1.0
	 *
	 * @param string $from_name      From name in email.
	 * @param string $from_email     From email address.
	 * @param string $to_email       To email address.
	 * @param string $subject        Email Subject.
	 * @param string $message        Email Message.
	 * @param string string $headers Additional Email Headers.
	 */
	protected function setup_mail( $from_name, $from_email, $to_email, $subject, $message, $headers = '' ) {
		$this->email_from_name = $from_name;
		$this->email_from_email = $from_email;
		$this->email_to_email = $to_email;
		$this->email_subject = $subject;
		$this->email_message = $message;
		$this->email_headers = $headers;
	}

	/**
	 * Sending out email with setting up filters.
	 *
	 * @since 1.1.0
	 *
	 * @return WP_Error|bool True if sending out email was without errors, otherwise false.
	 */
	protected function send_mail() {
		$this->add_filters();
		$sent = $this->wp_mail();
		$this->remove_filters();

		return $sent;
	}

	/**
	 * Sending out email
	 *
	 * @since 1.1.0
	 *
	 * @return WP_Error|bool True if sending out email was without errors, otherwise false.
	 */
	protected function wp_mail() {
		$sent = wp_mail( $this->email_to_email, $this->email_subject, $this->email_message, $this->email_headers );
		$error = new WP_Error();

		if ( ! $sent ) {
			/* translators: %s: email address */
			$error_message = sprintf( __( 'Email notification to %s could not be sent.', 'torro-forms' ), $notification['to_email'] );
			if ( $this->phpmailer_error ) {
				/* translators: %s: error message */
				$error_message .= ' ' . sprintf( __( 'Original error message: %s', 'torro-forms' ), $this->phpmailer_error->get_error_message() );

				$this->phpmailer_error = null;
			}

			$error->add( 'email_notification_not_sent', $error_message );
		}

		if ( ! empty( $error->errors ) ) {
			return $error;
		}

		return true;
	}

	/**
	 * Adding filters for sending out Email.
	 *
	 * @since 1.1.0
	 */
	public function add_filters() {
		add_filter( 'wp_mail_content_type', array( $this, 'override_content_type' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'override_from_name' ) );
		add_filter( 'wp_mail_from', array( $this, 'override_from_email' ) );
		add_action( 'wp_mail_failed', array( $this, 'store_phpmailer_error' ) );
	}

	/**
	 * Removing filters for sending out Email.
	 *
	 * @since 1.1.0
	 */
	private function remove_filters() {
		remove_filter( 'wp_mail_content_type', array( $this, 'override_content_type' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'override_from_name' ) );
		remove_filter( 'wp_mail_from', array( $this, 'override_from_email' ) );
		remove_action( 'wp_mail_failed', array( $this, 'store_phpmailer_error' ) );
	}

	/**
	 * Gets the email content type.
	 *
	 * @since 1.0.0
	 *
	 * @return string Email content type.
	 */
	public function override_content_type() {
		return 'text/html';
	}

	/**
	 * Gets the email from name.
	 *
	 * @since 1.0.0
	 *
	 * @return string Email from name.
	 */
	public function override_from_name() {
		return $this->from_name;
	}

	/**
	 * Gets the email from email.
	 *
	 * @since 1.0.0
	 *
	 * @return string Email from email.
	 */
	public function override_from_email() {
		return $this->from_email;
	}

	/**
	 * Stores an error object as the internal PHPMailer error.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Error $error Error object.
	 */
	public function store_phpmailer_error( $error ) {
		$this->phpmailer_error = $error;
	}
}
