<?php
/**
 * Restrict form to all members of site and does some checks
 *
 * Motherclass for all Restrictions
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

final class Torro_Restriction_AllMembers extends Torro_Restriction {
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	protected function init() {
		$this->title = __( 'All Members', 'torro-forms' );
		$this->name = 'allmembers';

		$this->option_name = __( 'All Members of site', 'torro-forms' );

		add_action( 'torro_formbuilder_save', array( $this, 'save' ), 10, 1 );
	}

	/**
	 * Saving data
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public function save( $form_id ) {
		/**
		 * Saving restriction options
		 */
		if ( array_key_exists( 'form_restrictions_allmembers_same_users', $_POST ) ) {
			$restrictions_same_users = $_POST['form_restrictions_allmembers_same_users'];
			update_post_meta( $form_id, 'form_restrictions_allmembers_same_users', $restrictions_same_users );
		} else {
			update_post_meta( $form_id, 'form_restrictions_allmembers_same_users', '' );
		}
	}

	/**
	 * Adds content to the option
	 */
	public function option_content() {
		global $post;

		$form_id = $post->ID;

		$html = '<h3>' . esc_html__( 'Restrict Members', 'torro-forms' ) . '</h3>';

		/**
		 * Check User
		 */
		$restrictions_same_users = get_post_meta( $form_id, 'form_restrictions_allmembers_same_users', true );
		$checked = 'yes' === $restrictions_same_users ? ' checked' : '';

		$html .= '<div class="form-restrictions-same-users-userfilter">';
		$html .= '<input type="checkbox" name="form_restrictions_allmembers_same_users" value="yes" ' . $checked . '/>';
		$html .= '<label for="form_restrictions_allmembers_same_users">' . esc_html__( 'Prevent multiple entries from same User', 'torro-forms' ) . '</label>';
		$html .= '</div>';

		ob_start();
		do_action( 'form_restrictions_same_users_userfilters' );
		$html .= ob_get_clean();

		return $html;
	}

	/**
	 * Checks if the user can pass
	 */
	public function check() {
		$torro_form_id = torro()->forms()->get_current_form_id();

		if ( ! is_user_logged_in() ) {
			$this->add_message( 'error', __( 'You have to be logged in to participate.', 'torro-forms' ) );
			return false;
		}

		$restrictions_same_users = get_post_meta( $torro_form_id, 'form_restrictions_allmembers_same_users', true );

		if ( 'yes' === $restrictions_same_users && torro()->forms( $torro_form_id )->has_participated() ) {
			$this->add_message( 'error', __( 'You have already entered your data.', 'torro-forms' ) );
			return false;
		}

		return true;
	}
}

torro()->restrictions()->add( 'Torro_Restriction_AllMembers' );
