<?php
/**
 * Torro Forms Processing Restrictions Extension
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Actions
 * @version 1.0.0alpha1
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

class Torro_Form_Actions_FormProcessExtension {
	/**
	 * Init in WordPress, run on constructor
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public static function init() {
		add_action( 'torro_response_saved', array( __CLASS__, 'action' ), 10, 3 );
		add_filter( 'torro_response_saved_content', array( __CLASS__, 'notification' ), 10, 4 );
	}

	/**
	 * Handling all Actions
	 *
	 * @since 1.0.0
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
	 *
	 * @param int $form_id
	 * @param int $response_id
	 * @param array $response
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	public static function notification( $notification, $form_id, $response_id, $response ){
		$actions = torro()->actions()->get_all_registered();

		if ( 0 === count( $actions ) ) {
			return;
		}

		$html = '';

		foreach ( $actions as $action ) {
			if( false !== $action->notification( $form_id, $response_id, $response ) ) {
				$html .= $action->notification( $form_id, $response_id, $response );
			}
		}

		if( ! empty( $html ) ) {
			$notification = $html;
		}

		return $notification;
	}
}

Torro_Form_Actions_FormProcessExtension::init();
