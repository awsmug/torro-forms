<?php
/**
 * Restrict form to all Visitors of site and does some checks
 *
 * Retriction functions for visitors
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

final class Torro_Restriction_AllVisitors extends Torro_Restriction {
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
		$this->title = __( 'All Visitors', 'torro-forms' );
		$this->name = 'allvisitors';
		$this->option_name = __( 'All Visitors of site', 'torro-forms' );

		add_action( 'torro_formbuilder_save', array( $this, 'save_settings' ), 10, 1 );

		add_action( 'torro_form_end', array( $this, 'add_fingerprint_input' ) );

		add_action( 'torro_response_save', array( $this, 'set_cookie' ), 10 );
		add_action( 'torro_response_save', array( $this, 'save_ip' ), 10 );
		add_action( 'torro_response_save', array( $this, 'save_fingerprint' ), 10 );
	}

	/**
	 * Saving data
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public function save_settings( $form_id ) {
		/**
		 * Check IP
		 */
		if ( array_key_exists( 'form_restrictions_check_ip', $_POST ) ) {
			$restrictions_check_ip = $_POST['form_restrictions_check_ip'];
			update_post_meta( $form_id, 'form_restrictions_check_ip', $restrictions_check_ip );
		} else {
			update_post_meta( $form_id, 'form_restrictions_check_ip', '' );
		}

		/**
		 * Check Cookie
		 */
		if ( array_key_exists( 'form_restrictions_check_cookie', $_POST ) ) {
			$restrictions_check_cookie = $_POST['form_restrictions_check_cookie'];
			update_post_meta( $form_id, 'form_restrictions_check_cookie', $restrictions_check_cookie );
		} else {
			update_post_meta( $form_id, 'form_restrictions_check_cookie', '' );
		}

		/**
		 * Check browser fingerprint
		 */
		if ( array_key_exists( 'form_restrictions_check_fingerprint', $_POST ) ) {
			$restrictions_check_fingerprint = $_POST['form_restrictions_check_fingerprint'];
			update_post_meta( $form_id, 'form_restrictions_check_fingerprint', $restrictions_check_fingerprint );
		} else {
			update_post_meta( $form_id, 'form_restrictions_check_fingerprint', '' );
		}
	}

	/**
	 * Loading fingerprint scripts
	 */
	public function frontend_scripts() {
		wp_enqueue_script( 'detection', torro()->asset_url( 'detection', 'vendor-js' ) );
	}

	/**
	 * Adds content to the option
	 */
	public function option_content() {
		global $post;

		$form_id = $post->ID;

		$html = '<h3>' . esc_html__( 'Restrict Visitors', 'torro-forms' ) . '</h3>';

		/**
		 * Check IP
		 */
		$restrictions_check_ip = get_post_meta( $form_id, 'form_restrictions_check_ip', true );
		$checked = 'yes' === $restrictions_check_ip ? ' checked' : '';

		$html .= '<div class="form-restrictions-allvisitors-userfilter">';
		$html .= '<input type="checkbox" name="form_restrictions_check_ip" value="yes" ' . $checked . '/>';
		$html .= '<label for="form_restrictions_check_ip">' . esc_attr__( 'Prevent multiple entries from same IP', 'torro-forms' ) . '</label>';
		$html .= '</div>';

		/**
		 * Check Cookie
		 */
		$restrictions_check_cookie = get_post_meta( $form_id, 'form_restrictions_check_cookie', true );
		$checked = 'yes' === $restrictions_check_cookie ? ' checked' : '';

		$html .= '<div class="form-restrictions-allvisitors-userfilter">';
		$html .= '<input type="checkbox" name="form_restrictions_check_cookie" value="yes" ' . $checked . '/>';
		$html .= '<label for="form_restrictions_check_cookie">' . esc_attr__( 'Prevent multiple entries by checking cookie', 'torro-forms' ) . '</label>';
		$html .= '</div>';

		/**
		 * Check browser fingerprint
		 */
		$restrictions_check_fingerprint = get_post_meta( $form_id, 'form_restrictions_check_fingerprint', true );
		$checked = 'yes' === $restrictions_check_fingerprint ? ' checked' : '';

		$html .= '<div class="form-restrictions-allvisitors-userfilter">';
		$html .= '<input type="checkbox" name="form_restrictions_check_fingerprint" value="yes" ' . $checked . '/>';
		$html .= '<label for="form_restrictions_check_fingerprint">' . esc_attr__( 'Prevent multiple entries by checking browser fingerprint', 'torro-forms' ) . '</label>';
		$html .= '</div>';

		ob_start();
		do_action( 'form_restrictions_allvisitors_userfilters' );
		$html .= ob_get_clean();

		return $html;
	}

	/**
	 * Checks if the user can pass
	 */
	public function check() {
		global $ar_form_id, $torro_skip_fingerrint_check;

		$restrictions_check_ip = get_post_meta( $ar_form_id, 'form_restrictions_check_ip', true );

		if ( 'yes' === $restrictions_check_ip && $this->ip_has_participated() ) {
			$this->add_message( 'error', __( 'You have already entered your data.', 'torro-forms' ) );

			return false;
		}

		$restrictions_check_cookie = get_post_meta( $ar_form_id, 'form_restrictions_check_cookie', true );

		if ( 'yes' === $restrictions_check_cookie && isset( $_COOKIE[ 'torro_has_participated_form_' . $ar_form_id ] ) ) {
			if( 'yes' === $_COOKIE[ 'torro_has_participated_form_' . $ar_form_id ] ) {
				$this->add_message( 'error', __( 'You have already entered your data.', 'torro-forms' ) );
			}

			return false;
		}

		$restrictions_check_fingerprint = get_post_meta( $ar_form_id, 'form_restrictions_check_fingerprint', true );

		if ( 'yes' === $restrictions_check_fingerprint && true !== $torro_skip_fingerrint_check ) {
			$actual_step = 0;
			if ( isset( $_POST['torro_actual_step'] ) ) {
				$actual_step = $_POST['torro_actual_step'];
			}

			$next_step = 0;
			if ( isset( $_POST['torro_next_step'] ) ) {
				$next_step = $_POST['torro_next_step'];
			}

			$maybe_vars = '';

			if ( isset( $_POST['torro_submission_back'] ) ) {
				$maybe_vars = 'torro_submission_back: \'yes\',';
			}

			$nonce = torro()->ajax()->get_nonce( 'check_fngrprnt' );

			$html = '<script language="JavaScript">
	(function ($) {
		"use strict";
		$( function () {
			new Fingerprint2().get(function(fngrprnt){

				var data = {
					action: \'torro_check_fngrprnt\',
					nonce: \'' . $nonce . '\',
					torro_form_id: ' . $ar_form_id . ',
					torro_actual_step: ' . $actual_step . ',
					torro_next_step: ' . $next_step . ',
					' . $maybe_vars . '
					action_url: \'' . $_SERVER[ 'REQUEST_URI' ] . '\',
					fngrprnt: fngrprnt
				};

				var ajaxurl = "' . admin_url( 'admin-ajax.php' ) . '";

				$.post( ajaxurl, data, function( response ) {
					if ( response.success ) {
						$( \'#torro-ajax-form\' ).html( response.data.html );
						$( \'#torro-fngrprnt\' ).val( fngrprnt );
					}
				});
			});
		});
	}(jQuery))
</script><div id="torro-ajax-form"></div>';

			$this->add_message( 'check', $html );

			return false;
		}

		return true;
	}

	/**
	 * Has IP already participated
	 *
	 * @return bool $has_participated
	 * @since 1.0.0
	 *
	 */
	public function ip_has_participated() {
		global $wpdb, $ar_form_id;

		$remote_ip = $_SERVER['REMOTE_ADDR'];

		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->torro_results WHERE form_id=%d AND remote_addr=%s", $ar_form_id, $remote_ip );
		$count = $wpdb->get_var( $sql );

		if ( 0 === $count ) {
			return false;
		}

		return true;
	}

	/**
	 * Setting Cookie for one year
	 */
	public function set_cookie() {
		global $ar_form_id;

		setcookie( 'torro_has_participated_form_' . $ar_form_id, 'yes', time() + YEAR_IN_SECONDS );
	}

	/**
	 * Setting Cookie for one year
	 */
	public function save_ip( $response_id ) {
		global $wpdb, $ar_form_id;

		$restrictions_check_ip = get_post_meta( $ar_form_id, 'form_restrictions_check_ip', true );
		if ( empty( $restrictions_check_ip ) ) {
			return;
		}

		// Adding IP to response
		$wpdb->update( $wpdb->torro_results, array(
			'remote_addr' => $_SERVER['REMOTE_ADDR'], // string
		), array(
			'id' => $response_id,
		) );
	}

	/**
	 * Setting Cookie for one year
	 */
	public function save_fingerprint( $response_id ) {
		global $wpdb, $ar_form_id;

		$restrictions_check_fingerprint = get_post_meta( $ar_form_id, 'form_restrictions_check_fingerprint', true );
		if ( empty( $restrictions_check_fingerprint ) ) {
			return;
		}

		$wpdb->update( $wpdb->torro_results, array(
			'cookie_key' => $_POST[ 'torro_fngrprnt' ],    // string
		), array(
			'id' => $response_id,
		) );
	}

	/**
	 * Adding fingerprint post field
	 */
	public function add_fingerprint_input() {
		global $ar_form_id;

		$restrictions_check_fingerprint = get_post_meta( $ar_form_id, 'form_restrictions_check_fingerprint', true );
		if( empty( $restrictions_check_fingerprint ) ) {
			return;
		}

		echo '<input type="hidden" id="torro-fngrprnt" name="torro_fngrprnt" />';
	}

}

torro()->restrictions()->add( 'Torro_Restriction_AllVisitors' );
