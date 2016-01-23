<?php
/**
 * Torro Forms Processing Restrictions Extension
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Actions
 * @version 2015-08-16
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

class Torro_Actions_FormProcessExtension {
	/**
	 * Init in WordPress, run on constructor
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public static function init() {
		add_action( 'torro_response_save', array( __CLASS__, 'action' ), 10, 3 );
		add_action( 'torro_form_finished', array( __CLASS__, 'notification' ), 10, 2 );
	}

	/**
	 * Starting response handler
	 */
	public static function action( $form_id, $response_id, $response ) {
		$actions = torro()->actions()->get_all_registered();

		if ( 0 === count( $actions ) ) {
			return;
		}

		foreach ( $actions as $action ) {
			$action->handle( $form_id, $response_id, $response );
		}
	}

	/**
	 * Show notifcations for users
	 * @param $form_id
	 */
	public static function notification( $form_id, $response_id ){
		$actions = torro()->actions()->get_all_registered();

		if ( 0 === count( $actions ) ) {
			return;
		}

		$html = '';

		foreach ( $actions as $action ) {
			if( false !== $action->notification( $form_id, $response_id ) ) {
				$html .= $action->notification( $form_id, $response_id );
			}
		}

		if( empty( $html ) ) {
			$show_spaceholder = apply_filters( 'torro_action_notification_spaceholder', true );

			if ( $show_spaceholder ) {
				$html .= '<div id="torro-thank-submitting">';
				$html .= '<p>' . esc_html__( 'Thank you for submitting!', 'torro-forms' ) . '</p>';
				$html .= '</div>';
			}
		}

		echo $html;
	}
}

Torro_Actions_FormProcessExtension::init();
