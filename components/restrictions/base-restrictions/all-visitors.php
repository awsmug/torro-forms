<?php
/**
 * Restrict form to all Visitors of site and does some checks
 *
 * Retriction functions for visitors
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

class AF_Restriction_AllVisitors extends AF_Restriction
{

	/**
	 * Constructor
	 */
	public function init()
	{
		$this->title = __( 'All Visitors', 'af-locale' );
		$this->name = 'allvisitors';
		$this->option_name = __( 'All Visitors of site', 'af-locale' );

		add_action( 'init', array( $this, 'enqueue_fingerprint_scipts' ), 20 );
		add_action( 'af_formbuilder_save', array( $this, 'save_settings' ), 10, 1 );

		add_action( 'af_form_end', array( $this, 'add_fingerprint_input' ) );

		add_action( 'af_response_save', array( $this, 'set_cookie' ), 10 );
		add_action( 'af_response_save', array( $this, 'save_ip' ), 10 );
		add_action( 'af_response_save', array( $this, 'save_fingerprint' ), 10 );

		add_action( 'wp_ajax_af_check_fngrprnt', array( __CLASS__, 'ajax_check_fingerprint' ) );
		add_action( 'wp_ajax_nopriv_af_check_fngrprnt', array( __CLASS__, 'ajax_check_fingerprint' ) );
	}

	/**
	 * Checking browser fingerprint by ajax
	 */
	public static function ajax_check_fingerprint()
	{
		global $wpdb, $af_global, $ar_form_id, $af_skip_fingerrint_check;

		$content = '';
		$restrict = FALSE;

		if( !isset( $_POST[ 'af_form_id' ] ) )
		{
			$content .= esc_attr( 'Form ID is missing.' . 'af-locale' );
			$restrict = TRUE;
		}

		if( !isset( $_POST[ 'fngrprnt' ] ) )
		{
			$content .= esc_attr( 'Error on processing form' . 'af-locale' );
			$restrict = TRUE;
		}

		if( FALSE == $restrict )
		{
			$ar_form_id = $_POST[ 'af_form_id' ];
			$fingerprint = $_POST[ 'fngrprnt' ];

			$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$af_global->tables->results} WHERE form_id=%d AND cookie_key=%s", $ar_form_id, $fingerprint );
			$count = $wpdb->get_var( $sql );

			if( 0 == $count )
			{
				$af_skip_fingerrint_check = TRUE;

				$af_form_process = new AF_FormProcess( $ar_form_id, $_POST[ 'action_url' ] );
				$content .= $af_form_process->show_form();
			}
			else
			{
				$content .= '<div class="form-message error">' . esc_attr( 'You have already entered your data.', 'af-locale' ) . '</div>';
			}
		}

		echo $content;
		die();
	}

	/**
	 * Saving data
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public static function save_settings( $form_id )
	{
		/**
		 * Check IP
		 */
		if( array_key_exists( 'form_restrictions_check_ip', $_POST ) )
		{
			$restrictions_check_ip = $_POST[ 'form_restrictions_check_ip' ];
			update_post_meta( $form_id, 'form_restrictions_check_ip', $restrictions_check_ip );
		}
		else
		{
			update_post_meta( $form_id, 'form_restrictions_check_ip', '' );
		}

		/**
		 * Check Cookie
		 */
		if( array_key_exists( 'form_restrictions_check_cookie', $_POST ) )
		{
			$restrictions_check_cookie = $_POST[ 'form_restrictions_check_cookie' ];
			update_post_meta( $form_id, 'form_restrictions_check_cookie', $restrictions_check_cookie );
		}
		else
		{
			update_post_meta( $form_id, 'form_restrictions_check_cookie', '' );
		}

		/**
		 * Check browser fingerprint
		 */
		if( array_key_exists( 'form_restrictions_check_fingerprint', $_POST ) )
		{
			$restrictions_check_fingerprint = $_POST[ 'form_restrictions_check_fingerprint' ];
			update_post_meta( $form_id, 'form_restrictions_check_fingerprint', $restrictions_check_fingerprint );
		}
		else
		{
			update_post_meta( $form_id, 'form_restrictions_check_fingerprint', '' );
		}
	}

	/**
	 * Enqueueing fingerprint scripts
	 */
	public static function enqueue_fingerprint_scipts()
	{
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_fingerprint_scripts' ) );
	}

	/**
	 * Loading fingerprint scripts
	 */
	public static function load_fingerprint_scripts()
	{
		wp_enqueue_script( 'admin-form-restrictions-fingerprint-script', AF_URLPATH . 'components/restrictions/base-restrictions/includes/js/detection.min.js' );
	}

	/**
	 * Adds content to the option
	 */
	public function option_content()
	{
		global $post;

		$form_id = $post->ID;

		$html = '<h3>' . esc_attr( 'Restrict Visitors', 'af-locale' ) . '</h3>';

		/**
		 * Check IP
		 */
		$restrictions_check_ip = get_post_meta( $form_id, 'form_restrictions_check_ip', TRUE );
		$checked = 'yes' == $restrictions_check_ip ? ' checked' : '';

		$html .= '<div class="form-restrictions-allvisitors-userfilter">';
			$html .= '<input type="checkbox" name="form_restrictions_check_ip" value="yes" ' . $checked . '/>';
			$html .= '<label for="form_restrictions_check_ip">' . esc_attr( 'Prevent multiple entries from same IP', 'af-locale' ) . '</label>';
		$html .= '</div>';

		/**
		 * Check Cookie
		 */
		$restrictions_check_cookie = get_post_meta( $form_id, 'form_restrictions_check_cookie', TRUE );
		$checked = 'yes' == $restrictions_check_cookie ? ' checked' : '';

		$html .= '<div class="form-restrictions-allvisitors-userfilter">';
			$html .= '<input type="checkbox" name="form_restrictions_check_cookie" value="yes" ' . $checked . '/>';
			$html .= '<label for="form_restrictions_check_cookie">' . esc_attr( 'Prevent multiple entries by checking cookie', 'af-locale' ) . '</label>';
		$html .= '</div>';

		/**
		 * Check browser fingerprint
		 */
		$restrictions_check_fingerprint = get_post_meta( $form_id, 'form_restrictions_check_fingerprint', TRUE );
		$checked = 'yes' == $restrictions_check_fingerprint ? ' checked' : '';

		$html .= '<div class="form-restrictions-allvisitors-userfilter">';
			$html .= '<input type="checkbox" name="form_restrictions_check_fingerprint" value="yes" ' . $checked . '/>';
			$html .= '<label for="form_restrictions_check_fingerprint">' . esc_attr( 'Prevent multiple entries by checking browser fingerprint', 'af-locale' ) . '</label>';
		$html .= '</div>';

		ob_start();
		do_action( 'form_restrictions_allvisitors_userfilters' );
		$html .= ob_get_clean();

		return $html;
	}

	/**
	 * Checks if the user can pass
	 */
	public function check()
	{
		global $ar_form_id, $af_skip_fingerrint_check;

		$restrictions_check_ip = get_post_meta( $ar_form_id, 'form_restrictions_check_ip', TRUE );

		if( 'yes' == $restrictions_check_ip && $this->ip_has_participated() )
		{
			$this->add_message( 'error', esc_attr( 'You have already entered your data.', 'af-locale' ) );

			return FALSE;
		}

		$restrictions_check_cookie = get_post_meta( $ar_form_id, 'form_restrictions_check_cookie', TRUE );

		if( 'yes' == $restrictions_check_cookie && isset( $_COOKIE[ 'af_has_participated_form_' . $ar_form_id ] ) )
		{

			if( $_COOKIE[ 'af_has_participated_form_' . $ar_form_id ] == 'yes' )
			{
				$this->add_message( 'error', esc_attr( 'You have already entered your data.', 'af-locale' ) );
			}

			return FALSE;
		}

		$restrictions_check_fingerprint = get_post_meta( $ar_form_id, 'form_restrictions_check_fingerprint', TRUE );

		if( 'yes' == $restrictions_check_fingerprint && $af_skip_fingerrint_check != TRUE )
		{
			$actual_step = 0;
			if( isset( $_POST[ 'af_actual_step' ] ) )
			{
				$actual_step = $_POST[ 'af_actual_step' ];
			}

			$next_step = 0;
			if( isset( $_POST[ 'af_next_step' ] ) )
			{
				$next_step = $_POST[ 'af_next_step' ];
			}

			$maybe_vars = '';

			if( isset( $_POST[ 'af_submission_back' ] ) )
			{
				$maybe_vars = 'af_submission_back: \'yes\',';
			}

			$html = '<script language="JavaScript">
					    (function ($) {
							"use strict";
							$( function () {
					            new Fingerprint2().get(function(fngrprnt){

								    var data = {
										action: \'af_check_fngrprnt\',
										af_form_id: ' . $ar_form_id . ',
										af_actual_step: ' . $actual_step . ',
										af_next_step: ' . $next_step . ',
										' . $maybe_vars . '
										action_url: \'' . $_SERVER[ 'REQUEST_URI' ] . '\',
										fngrprnt: fngrprnt
								    };

								    var ajaxurl = "' . admin_url( 'admin-ajax.php' ) . '";

								    $.post( ajaxurl, data, function( response ) {
								        $( \'#af-ajax-form\' ).html( response );
								        $( \'#af-fngrprnt\' ).val( fngrprnt );
								    });
						        });
							});
						}(jQuery))
					  </script><div id="af-ajax-form"></div>';

			$this->add_message( 'check', $html );

			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Has IP already participated
	 *
	 * @return bool $has_participated
	 * @since 1.0.0
	 *
	 */
	public function ip_has_participated()
	{
		global $wpdb, $af_global, $ar_form_id;

		$remote_ip = $_SERVER[ 'REMOTE_ADDR' ];

		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$af_global->tables->results} WHERE form_id=%d AND remote_addr=%s", $ar_form_id, $remote_ip );
		$count = $wpdb->get_var( $sql );

		if( 0 == $count )
		{
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Setting Cookie for one year
	 */
	public function set_cookie()
	{
		global $ar_form_id;
		setcookie( 'af_has_participated_form_' . $ar_form_id, 'yes', time() + 60 * 60 * 24 * 365 );
	}

	/**
	 * Setting Cookie for one year
	 */
	public function save_ip( $response_id )
	{
		global $wpdb, $af_global, $ar_form_id;

		$restrictions_check_ip = get_post_meta( $ar_form_id, 'form_restrictions_check_ip', TRUE );
		if( '' == $restrictions_check_ip )
		{
			return;
		}

		// Adding IP to response
		$wpdb->update( $af_global->tables->responds, array(
			                                           'remote_addr' => $_SERVER[ 'REMOTE_ADDR' ],    // string
		                                           ), array(
			               'id' => $response_id,
		               ) );
	}

	/**
	 * Setting Cookie for one year
	 */
	public function save_fingerprint( $response_id )
	{
		global $wpdb, $af_global, $ar_form_id;

		$restrictions_check_fingerprint = get_post_meta( $ar_form_id, 'form_restrictions_check_fingerprint', TRUE );
		if( '' == $restrictions_check_fingerprint )
		{
			return;
		}

		$wpdb->update( $af_global->tables->responds, array(
			                                           'cookie_key' => $_POST[ 'af_fngrprnt' ],    // string
		                                           ), array(
			               'id' => $response_id,
		               ) );
	}

	/**
	 * Adding fingerprint post field
	 */
	public function add_fingerprint_input()
	{
		global $ar_form_id;

		$restrictions_check_fingerprint = get_post_meta( $ar_form_id, 'form_restrictions_check_fingerprint', TRUE );
		if( '' == $restrictions_check_fingerprint )
		{
			return;
		}

		echo '<input type="hidden" id="af-fngrprnt" name="af_fngrprnt" />';
	}

}

af_register_restriction( 'AF_Restriction_AllVisitors' );
