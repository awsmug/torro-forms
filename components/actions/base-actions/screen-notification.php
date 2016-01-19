<?php
/**
 * Email notifications Action
 *
 * Adds Email notifications for forms
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Restrictions
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Torro_Screen_Notifications extends Torro_Action {
	private static $instance = null;

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Adds the text after submitting
	 *
	 * @param $response_id
	 * @param $response
	 */
	public function notification( $form_id, $response_id ) {
		$notification = get_post_meta( $form_id, 'notification_content', true );
		return $notification;
	}

	public function option_content() {
		global $post;

		$notification = get_post_meta( $post->ID, 'notification_content', true );

		if( '' == $notification )
		{
			$notification = esc_html__( 'Thank you for submitting!', 'torro-forms' );
		}

		$html = '<div id="form-screen-notifications">';

		$html .= '<div class="actions">';
		$html .= '<p class="intro-text">' . esc_attr__( 'This notification will be shown after successfull submitting', 'torro-forms' ) . '</p>';
		$html .= '</div>';

		$html .= '<div class="notification-content">';

		$settings = array( 'textarea_rows', 50 );

		ob_start();
		wp_editor( $notification, 'notification_content', $settings );
		$html .= ob_get_clean();

		$html .= '</div>';

		$html .= '</div>';
		$html .= '<div class="clear"></div>';

		return $html;
	}

	/**
	 * Saving option content
	 */
	public function save_option_content() {
		global $post;

		$notification_content = $_POST[ 'notification_content' ];
		update_post_meta( $post->ID, 'notification_content', $notification_content );
	}

	protected function init() {
		$this->title = __( 'Screen Notification', 'torro-forms' );
		$this->name  = 'screennotifications';

		add_action( 'torro_formbuilder_save', array( $this, 'save_option_content' ) );
	}
}

torro()->actions()->add( 'Torro_Screen_Notifications' );
