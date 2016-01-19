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
		add_action( 'torro_response_save', array( __CLASS__, 'action' ), 10, 1 );
	}

	/**
	 * Starting response handler
	 */
	public static function action( $response_id ) {
		global $torro_form_id;

		$actions = torro()->actions()->get_all();

		if ( 0 === count( $actions ) ) {
			return;
		}

		foreach ( $actions as $action ) {
			$action->handle( $response_id, $_SESSION['torro_response'][ $torro_form_id ] );
		}
	}
}

Torro_Actions_FormProcessExtension::init();
