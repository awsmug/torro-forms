<?php
/**
 * Restrict form to all members of site and does some checks
 *
 * Motherclass for all Restrictions
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

if( !defined( 'ABSPATH' ) ){
	exit;
}

class AF_Restriction_AllMembers extends AF_Restriction
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->title = __( 'All Members', 'af-locale' );
		$this->name = 'allmembers';

		$this->option_name = __( 'All Members of site', 'af-locale' );

		add_action( 'af_save_form', array( $this, 'save' ), 10, 1 );
	}

	/**
	 * Adds content to the option
	 */
	public function option_content()
	{
		global $post;

		$form_id = $post->ID;

		$html = '<h3>' . esc_attr( 'Restrict Members', 'af-locale' ) . '</h3>';

		/**
		 * Check User
		 */
		$restrictions_same_users = get_post_meta( $form_id, 'form_restrictions_allmembers_same_users', TRUE );
		$checked = 'yes' == $restrictions_same_users ? ' checked' : '';

		$html .= '<div class="form-restrictions-same-users-userfilter">';
			$html .= '<input type="checkbox" name="form_restrictions_allmembers_same_users" value="yes" ' . $checked . '/>';
			$html .= '<label for="form_restrictions_allmembers_same_users">' . esc_attr( 'Prevent multiple entries from same User', 'af-locale' ) . '</label>';
		$html .= '</div>';

		ob_start();
		do_action( 'form_restrictions_same_users_userfilters' );
		$html .= ob_get_clean();

		return $html;
	}

	/**
	 * Checks if the user can pass
	 */
	public function check()
	{
		global $questions_form_id;

		if( !is_user_logged_in() ){
			$this->add_message( 'error', esc_attr( 'You have to be logged in to participate.', 'af-locale' ) );

			return FALSE;
		}

		$restrictions_same_users = get_post_meta( $questions_form_id, 'form_restrictions_allmembers_same_users', TRUE );

		if( 'yes' == $restrictions_same_users && af_user_has_participated( $questions_form_id ) ){
			$this->add_message( 'error', esc_attr( 'You have already entered your data.', 'af-locale' ) );

			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Saving data
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public static function save( $form_id )
	{
		/**
		 * Saving restriction options
		 */
		if( array_key_exists( 'form_restrictions_allmembers_same_users', $_POST ) ){
			$restrictions_same_users = $_POST[ 'form_restrictions_allmembers_same_users' ];
			update_post_meta( $form_id, 'form_restrictions_allmembers_same_users', $restrictions_same_users );
		}else{
			update_post_meta( $form_id, 'form_restrictions_allmembers_same_users', '' );
		}
	}
}
af_register_restriction( 'AF_Restriction_AllMembers' );