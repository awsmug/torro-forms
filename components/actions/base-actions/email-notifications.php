<?php
/**
 * Email notifications Response handler
 *
 * Adds Email notifications for forms
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Restrictions
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if( !defined( 'ABSPATH' ) )
{
	exit;
}

class AF_EmailNotifications extends AF_Action
{

	private $from_name;
	private $from_email;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->title = __( 'Email Notifications', 'af-locale' );
		$this->name = 'emailnotifications';

		add_action( 'admin_print_styles', array( __CLASS__, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );
		add_action( 'af_save_form', array( __CLASS__, 'save_option_content' ) );

		add_action( 'wp_ajax_get_email_notification_html', array( __CLASS__, 'ajax_get_email_notification_html' ) );

		add_action( 'media_buttons', array( __CLASS__, 'add_media_button' ), 20 );
	}

	/**
	 * Handles the data after user submitted the form
	 *
	 * @param $response_id
	 * @param $response
	 */
	public function handle( $response_id, $response )
	{
		global $wpdb, $ar_form_id, $af_global, $af_response_id, $af_response;

		$af_response_id = $response_id;
		$af_response = $response;

		$sql = $wpdb->prepare( "SELECT * FROM {$af_global->tables->email_notifications} WHERE form_id = %d", $ar_form_id );
		$notifications = $wpdb->get_results( $sql );

		if( count( $notifications ) > 0 )
		{
			// Adding elements templatetags
			$form = new AF_Form( $ar_form_id );
			foreach( $form->elements AS $element )
			{
				af_add_element_templatetag( $element->id, $element->label );
			}

			foreach( $notifications AS $notification )
			{
				$from_name = af_filter_templatetags( $notification->from_name );
				$from_email = af_filter_templatetags( $notification->from_email );
				$to_email = af_filter_templatetags( $notification->to_email );
				$subject = af_filter_templatetags( $notification->subject );
				$message = apply_filters( 'the_content', af_filter_templatetags( $notification->message ) );

				$this->from_name = $from_name;
				$this->from_email = $from_email;

				add_filter( 'wp_mail_content_type', array( $this, 'set_email_html_content_type' ) );
				add_filter( 'wp_mail_from', array( $this, 'set_email_from' ) );
				add_filter( 'wp_mail_from_name', array( $this, 'set_email_from_name' ) );

				wp_mail( $to_email, $subject, $message );

				remove_filter( 'wp_mail_content_type', array( $this, 'set_email_html_content_type' ) );
				remove_filter( 'wp_mail_from', array( $this, 'set_email_from' ) );
				remove_filter( 'wp_mail_from_name', array( $this, 'set_email_from_name' ) );
			}
		}
	}

	/**
	 * Setting HTML Content-Type
	 */
	function set_email_html_content_type()
	{
		return 'text/html';
	}

	/**
	 * Setting From Email
	 */
	public function set_email_from()
	{
		return $this->from_email;
	}

	/**
	 * Setting From Email Name
	 */
	public function set_email_from_name()
	{
		return $this->from_name;
	}

	public function option_content()
	{
		global $wpdb, $post, $af_global;

		$sql = $wpdb->prepare( "SELECT * FROM {$af_global->tables->email_notifications} WHERE form_id = %d", $post->ID );
		$notifications = $wpdb->get_results( $sql );

		$html = '<div id="form-email-notifications">';
			$html.= '<div class="list">';

					$html.= '<div class="notifications widget-title">';

					if( count( $notifications ) > 0 ){

						foreach( $notifications AS $notification ){
							$html.= self::get_notification_settings_html(
								$notification->id,
								$notification->notification_name,
								$notification->from_name,
								$notification->from_email,
								$notification->to_email,
								$notification->subject,
								$notification->message
							);
						}
					}
					$html.= '<p class="no-entry-found">' . esc_attr( 'No Notification found.', 'af-locale' ) . '</p>';
				$html.= '</div>';
			$html.= '</div>';
			$html.= '<div class="actions">';
				$html.= '<input id="form_add_email_notification" type="button" value="' . esc_attr( 'Add Notification', 'af-locale' ) . '" class="button" />';
			$html.= '</div>';
		$html.= '</div>';
		$html.= '<div class="clear"></div>';

		$html.= '<script language="javascript">jQuery( document ).ready(function ($) {$.af_templatetag_buttons();});</script>';

		$html.= '<div id="delete_email_notification_dialog">' . esc_attr__( 'Do you really want to delete this Email-Notification?', 'af-locale' ) . '</div>';

		// Dirty hack: Running one time for fake, to get all variables
		ob_start();
		wp_editor( '', 'xxx' );
		ob_clean();

		return $html;
	}

	/**
	 * Adding media button
	 */
	public static function add_media_button( $editor_id )
	{
		$editor_id_arr = explode( '-', $editor_id );

		if( 'email_notification_message' != $editor_id_arr[ 0 ] )
		{
			return;
		}

		echo af_template_tag_button( $editor_id );
	}

	/**
	 * Saving option content
	 */
	public static function save_option_content()
	{
		global $wpdb, $post, $af_global;

		if( isset( $_POST[ 'email_notifications' ] ) && count( $_POST[ 'email_notifications' ] ) > 0 ){
			$wpdb->delete( $af_global->tables->email_notifications, array( 'form_id' => $post->ID ), array( '%d' ) );

			foreach(  $_POST[ 'email_notifications' ] AS $id => $notification  ){
				$wpdb->insert(
					$af_global->tables->email_notifications,
					array(
						'form_id'           => $post->ID,
						'notification_name' => $notification[ 'notification_name' ],
						'from_name'         => $notification[ 'from_name' ],
						'from_email'        => $notification[ 'from_email' ],
						'to_email'          => $notification[ 'to_email' ],
						'subject'           => $notification[ 'subject' ],
						'message'           => $_POST[ 'email_notification_message-' . $id ]
					),
					array(
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					)
				);
			}
		}
	}

	/**
	 * Getting HTML for notification
	 *
	 * @param $notification_name
	 * @param $notification_from_name
	 * @param $notification_from_email
	 * @param $notification_to_email
	 * @param $notification_subject
	 * @param $notification_message
	 *
	 * @return string $html
	 */
	public static function get_notification_settings_html( $id, $notification_name = '', $from_name = '', $from_email = '', $to_email = '', $subject = '', $message = '' )
	{
		$editor_id = 'email_notification_message-' . $id;

		$editor = af_wp_editor( $message, $editor_id );

		$html = '<h4 class="widget-top notification-' . $id . '">' . $notification_name . '</h4>';
		$html.= '<div class="notification widget-inside notification-' . $id . '-content">';

			$html.= '<table class="form-table">';
				$html.= '<tr>';
					$html.= '<th><label for="email_notifications[' . $id . '][notification_name]">' . esc_attr( 'Notification Name', 'af-locale' ) . '</label></th>';
					$html.= '<td><input type="text" name="email_notifications[' . $id . '][notification_name]" value="' . $notification_name . '"></td>';
				$html.= '</tr>';
				$html.= '<tr>';
					$html.= '<th><label for="email_notifications[' . $id . '][from_name]">' . esc_attr( 'From Name', 'af-locale' ) . '</label></th>';
					$html.= '<td><input type="text" name="email_notifications[' . $id . '][from_name]" value="' . $from_name . '">' . af_template_tag_button( 'email_notifications[' . $id . '][from_name]' ) . '</td>';
				$html.= '</tr>';
				$html.= '<tr>';
					$html.= '<th><label for="email_notifications[' . $id . '][from_email]">' . esc_attr( 'From Email', 'af-locale' ) . '</label></th>';
					$html.= '<td><input type="text" name="email_notifications[' . $id . '][from_email]" value="' . $from_email . '">' . af_template_tag_button( 'email_notifications[' . $id . '][from_email]' ) . '</td>';
				$html.= '</tr>';
				$html.= '<tr>';
					$html.= '<th><label for="email_notifications[' . $id . '][to_email]">' . esc_attr( 'To Email', 'af-locale' ) . '</label></th>';
					$html.= '<td><input type="text" name="email_notifications[' . $id . '][to_email]" value="' . $to_email . '">' . af_template_tag_button( 'email_notifications[' . $id . '][to_email]' ) . '</td>';
				$html.= '</tr>';
				$html.= '<tr>';
					$html.= '<th><label for="email_notifications[' . $id . '][subject]">' . esc_attr( 'Subject', 'af-locale' ) . '</label></th>';
					$html.= '<td><input type="text" name="email_notifications[' . $id . '][subject]" value="' . $subject . '">' . af_template_tag_button( 'email_notifications[' . $id . '][subject]' ) . '</td>';
				$html.= '</tr>';
				$html.= '<tr>';
					$html.= '<th><label for="email_notification_message-' . $id . '">' . esc_attr( 'Message', 'af-locale' ) . '</label></th>';
					$html.= '<td>' . $editor . '</td>';
				$html.= '</tr>';
				$html.= '<tr>';
					$html.= '<td colspan="2"><input type="button" class="button form-delete-email-notification" data-emailnotificationid="' . $id . '" value="' . esc_attr( 'Delete Notification', 'af-locale' ) . '" /></td>';
				$html.= '</tr>';
			$html.= '</table>';
		$html.= '</div>';

		return $html;
	}

	/**
	 * Get Email notification HTML
	 */
	public static function ajax_get_email_notification_html()
	{
		$id = time();
		$editor_id = 'email_notification_message-' . $id;

		$html = self::get_notification_settings_html( $id, esc_attr( 'New Email Notification' ) );

		$data = array(
			'id' => $id,
			'editor_id' => $editor_id,
			'html'      => $html
		);

		echo json_encode( $data );
		die();
	}

	/**
	 * Function to set standard editor to tinymce prevent tab issues on editor
	 * @return string
	 */
	public static function std_editor_tinymce(){
		return 'tinymce';
	}

	/**
	 * Enqueue admin scripts
	 */
	public static function enqueue_admin_scripts()
	{
		if( !af_is_formbuilder() )
			return;

		$translation = array( 'delete'                       => esc_attr__( 'Delete', 'af-locale' ),
		                      'yes'                          => esc_attr__( 'Yes', 'af-locale' ),
		                      'no'                           => esc_attr__( 'No', 'af-locale' ) );

		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'af-actions-email-notification', AF_URLPATH . '/components/actions/base-actions/includes/js/email-notifications.js' );
		wp_localize_script( 'af-actions-email-notification', 'translation_email_notifications', $translation );
	}

	/**
	 * Enqueue admin styles
	 */
	public static function enqueue_admin_styles()
	{
		wp_enqueue_style( 'af-actions-email-notification', AF_URLPATH . '/components/actions/base-actions/includes/css/email-notifications.css' );
	}
}
af_register_action( 'AF_EmailNotifications' );