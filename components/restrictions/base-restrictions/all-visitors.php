<?php
/**
 * Restrict form to all Visitors of site and does some checks
 *
 * Motherclass for all Restrictions
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package Questions/Restrictions
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

class Questions_Restriction_AllVisitors extends Questions_Restriction
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->title = __( 'All Visitors', 'wcsc-locale' );
		$this->slug = 'allvisitors';

		$this->option_name = __( 'All Visitors of site', 'wcsc-locale' );

		add_action( 'questions_save_form', array( $this, 'save' ), 10, 1 );
		add_action( 'questions_save_response', array( $this, 'set_cookie' ), 10 );
	}

	/**
	 * Adds content to the option
	 */
	public function option_content()
	{
		global $post;

		$form_id = $post->ID;

		$html = '<h3>' . esc_attr( 'Restrict Visitors', 'questions-locale' ) . '</h3>';

		/**
		 * Check IP
		 */
		$restrictions_check_ip = get_post_meta( $form_id, 'questions_restrictions_check_ip', TRUE );
		$checked = 'yes' == $restrictions_check_ip ? ' checked' : '';

		$html .= '<div class="questions-restrictions-allvisitors-userfilter">';
		$html .= '<input type="checkbox" name="questions_restrictions_check_ip" value="yes" ' . $checked . '/>';
		$html .= '<label for="questions_restrictions_check_ip">' . esc_attr( 'Prevent multiple entries from same IP', 'questions-locale' ) . '</label>';
		$html .= '</div>';

		/**
		 * Check Cookie
		 */
		$restrictions_check_cookie = get_post_meta( $form_id, 'questions_restrictions_check_cookie', TRUE );
		$checked = 'yes' == $restrictions_check_cookie ? ' checked' : '';

		$html .= '<div class="questions-restrictions-allvisitors-userfilter">';
		$html .= '<input type="checkbox" name="questions_restrictions_check_cookie" value="yes" ' . $checked . '/>';
		$html .= '<label for="questions_restrictions_check_cookie">' . esc_attr( 'Prevent multiple entries by checking cookie', 'questions-locale' ) . '</label>';
		$html .= '</div>';

		ob_start();
		do_action( 'questions_restrictions_allvisitors_userfilters' );
		$html .= ob_get_clean();

		return $html;
	}



	/**
	 * Checks if the user can pass
	 */
	public function check()
	{
		global $questions_form_id;

		$restrictions_check_ip = get_post_meta( $questions_form_id, 'questions_restrictions_check_ip', TRUE );

		if( 'yes' == $restrictions_check_ip && $this->ip_has_participated() ){
			$this->add_message( 'error', esc_attr( 'You have already filled out this form.', 'wcsc-locale' ) );

			return FALSE;
		}

		$restrictions_check_cookie = get_post_meta( $questions_form_id, 'questions_restrictions_check_cookie', TRUE );

		if( 'yes' == $restrictions_check_cookie && isset( $_COOKIE[ 'questions_has_participated_form_' . $questions_form_id ] )  ){

			if( $_COOKIE[ 'questions_has_participated_form_' . $questions_form_id ] == 'yes' ){
				$this->add_message( 'error', esc_attr( 'You have already filled out this form.', 'wcsc-locale' ) );
			}

			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Setting Cookie for one year
	 */
	public function set_cookie(){
		global $questions_form_id;
		setcookie( 'questions_has_participated_form_' . $questions_form_id, 'yes', time() + 60 * 60 * 24 * 365 );
	}

	/**
	 * Has IP already participated
	 *
	 * @param $questions_id
	 *
	 * @return bool $has_participated
	 * @since 1.0.0
	 *
	 */
	public function ip_has_participated()
	{
		global $wpdb, $questions_global, $questions_form_id;

		$remote_ip = $_SERVER[ 'REMOTE_ADDR' ];

		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$questions_global->tables->responds} WHERE questions_id=%d AND remote_addr=%s", $questions_form_id, $remote_ip );
		$count = $wpdb->get_var( $sql );

		if( 0 == $count ){
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
		 * Check IP
		 */
		if( array_key_exists( 'questions_restrictions_check_ip', $_POST ) ){
			$restrictions_check_ip = $_POST[ 'questions_restrictions_check_ip' ];
			update_post_meta( $form_id, 'questions_restrictions_check_ip', $restrictions_check_ip );
		}else{
			update_post_meta( $form_id, 'questions_restrictions_check_ip', '' );
		}

		/**
		 * Check Cookie
		 */
		if( array_key_exists( 'questions_restrictions_check_cookie', $_POST ) ){
			$restrictions_check_cookie = $_POST[ 'questions_restrictions_check_cookie' ];
			update_post_meta( $form_id, 'questions_restrictions_check_cookie', $restrictions_check_cookie );
		}else{
			update_post_meta( $form_id, 'questions_restrictions_check_cookie', '' );
		}
	}

}

qu_register_restriction( 'Questions_Restriction_AllVisitors' );
