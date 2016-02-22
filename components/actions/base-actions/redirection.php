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
	 * Initializing action
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->title = __( 'Redirections', 'torro-forms' );
		$this->name  = 'redirections';

		add_action( 'torro_formbuilder_save', array( $this, 'save_option_content' ) );
	}

	/**
	 * Adds the text after submitting
	 *
	 * @param $response_id
	 * @param $response
	 *
	 * @since 1.0.0
	 */
	public function notification( $form_id, $response_id ) {
		$notification = get_post_meta( $form_id, 'redirect_text', true );

		return $notification;
	}

	/**
	 * Handles Redirections
	 *
	 * @param $form_id
	 * @param $response_id
	 * @param $response
	 *
	 * @since 1.0.0
	 */
	public function handle( $form_id, $response_id, $response ) {
		$redirect_type = get_post_meta( $form_id, 'redirect_type', true );

		switch ( $redirect_type ) {
			case 'redirect_url':
				$redirect_url = get_post_meta( $form_id, 'redirect_url', true );

				if( !empty( $redirect_url ) ){
					session_destroy();
					wp_redirect( $redirect_url );
					exit;
				}

				break;
			case 'redirect_page':
				$page_id = get_post_meta( $form_id, 'redirect_page', true );

				if( !empty( $page_id ) ) {
					session_destroy();
					$redirect_url = get_page_link( $page_id );
					wp_redirect( $redirect_url );
					exit;
				}

				break;
			default:
				break;
		}
	}

	public function option_content() {
		global $post;

		$form_id = $post->ID;

		$redirect_type = get_post_meta( $form_id, 'redirect_type', true );
		$redirect_url = get_post_meta( $form_id, 'redirect_url', true );
		$redirect_page = get_post_meta( $form_id, 'redirect_page', true );
		$redirect_text = get_post_meta( $form_id, 'redirect_text', true );

		if ( '' == $redirect_text ) {
			$redirect_text = esc_html__( 'Thank you for submitting!', 'torro-forms' );
		}

		$html = '<div id="form-redirections">';

		$html .= '<div class="redirection-type">';
		$html .= '<label for="redirect_type">' . esc_attr__( 'After submitting redirect user to:', 'torro-forms' ) . '</label>';
		$html .= '<select id="redirect_type" name="redirect_type">';

		$selected = $redirect_type == 'redirect_text' ? ' selected="selected"' : '';
		$html .= '<option value="redirect_text"' . $selected . '>' . esc_attr__( 'Text Message', 'torro-forms' ) . '</option>';

		$selected = $redirect_type == 'redirect_page' ? ' selected="selected"' : '';
		$html .= '<option value="redirect_page"' . $selected . '>' . esc_attr__( 'Page Redirection', 'torro-forms' ) . '</option>';

		$selected = $redirect_type == 'redirect_url' ? ' selected="selected"' : '';
		$html .= '<option value="redirect_url"' . $selected . '>' . esc_attr__( 'URL Redirection', 'torro-forms' ) . '</option>';

		$html .= '</select>';

		$html .= '</div>';

		$display = $redirect_type == 'redirect_url' ? ' style="display:block;"' : ' style="display:none;"';

		$html .= '<div id="redirect_url" class="redirect-content"' . $display . '>';
		$html .= '<label for="redirect_url">' . esc_attr__( 'Url: ' ) . '</label><input name="redirect_url" type="text" value="' . $redirect_url . '" />';
		$html .= '</div>';

		$display = $redirect_type == 'redirect_page' ? ' style="display:block;"' : ' style="display:none;"';
		$pages = get_pages();

		$html .= '<div id="redirect_page" class="redirect-content"' . $display . '>';
		$html .= '<label for="redirect_page">' . esc_attr__( 'Page: ' ) . '</label>';
		$html .= '<select name="redirect_page">';
		foreach ( $pages as $page ) {
			$selected = $page->ID == $redirect_page ? ' selected="selected"' : '';
			$html .= '<option value="' . $page->ID . '"' . $selected . '>' . $page->post_title . '</option>';
		}
		$html .= '</select>';
		$html .= '</div>';

		$display = $redirect_type == 'redirect_text' ? ' style="display:block;"' : ' style="display:none;"';
		$html .= '<div id="redirect_text" class="redirect-content"' . $display . '>';

		$settings = array( 'textarea_rows', 25 );

		ob_start();
		wp_editor( $redirect_text, 'redirect_text_content', $settings );
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

		$redirect_type = $_POST[ 'redirect_type' ];
		update_post_meta( $post->ID, 'redirect_type', $redirect_type );

		$redirect_url = $_POST[ 'redirect_url' ];
		update_post_meta( $post->ID, 'redirect_url', $redirect_url );

		$redirect_page = $_POST[ 'redirect_page' ];
		update_post_meta( $post->ID, 'redirect_page', $redirect_page );

		$redirect_text = $_POST[ 'redirect_text_content' ];
		update_post_meta( $post->ID, 'redirect_text_content', $redirect_text );
	}
}

torro()->actions()->register( 'Torro_Redirection_Action' );
