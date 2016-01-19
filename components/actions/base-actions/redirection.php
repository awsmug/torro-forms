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

final class Torro_Redirection_Action extends Torro_Action {
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

		$redirection_type = get_post_meta( $post->ID, 'redirection_type', true );
		$notification     = get_post_meta( $post->ID, 'notification_content', true );

		if ( '' == $notification ) {
			$notification = esc_html__( 'Thank you for submitting!', 'torro-forms' );
		}

		$html = '<div id="form-redirections">';

		$html .= '<div class="actions">';
		$html .= '<p class="intro-text">' . esc_attr__( 'This notification will be shown after successfull submitting', 'torro-forms' ) . '</p>';
		$html .= '<select name="redirection_type">';

		$selected = $redirection_type == 'url_redirect' ? ' selected="selected"' : '';
		$html .= '<option value="url_redirect"' . $selected . '>' . esc_attr__( 'URL Redirection', 'torro-forms' ) . '</option>';

		$selected = $redirection_type == 'page' ? ' selected="selected"' : '';
		$html .= '<option value="page"' . $selected . '>' . esc_attr__( 'Page Redirection', 'torro-forms' ) . '</option>';

		$selected = $redirection_type == 'text_message' ? ' selected="selected"' : '';
		$html .= '<option value="text_message"' . $selected . '>' . esc_attr__( 'Text Message', 'torro-forms' ) . '</option>';

		$html .= '</select>';

		$html .= '</div>';

		$display = $redirection_type == 'url_redirect' ? ' style="display:block;"' : ' style="display:none;"';

		$html .= '<div class="redirect-content url-redirect"' . $display . '>';
		$html .= 'URL';
		$html .= '</div>';

		$display = $redirection_type == 'page' ? ' style="display:block;"' : ' style="display:none;"';

		$html .= '<div class="redirect-content notification-content"' . $display . '>';
		$html .= 'Page';
		$html .= '</div>';

		$display = $redirection_type == 'text_message' ? ' style="display:block;"' : ' style="display:none;"';
		$html .= '<div class="redirect-content notification-content"' . $display . '>';

		$settings = array( 'textarea_rows', 25 );

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

		$notification_content = $_POST[ 'redirection_type' ];
		update_post_meta( $post->ID, 'redirection_type', $notification_content );

		$notification_content = $_POST[ 'notification_content' ];
		update_post_meta( $post->ID, 'notification_content', $notification_content );
	}

	protected function init() {
		$this->title = __( 'Redirections', 'torro-forms' );
		$this->name  = 'redirections';

		add_action( 'torro_formbuilder_save', array( $this, 'save_option_content' ) );
	}
}

torro()->actions()->add( 'Torro_Redirection_Action' );
