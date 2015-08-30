<?php
/**
 * Awesome Forms Processing Restrictions Extension
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Core
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

if( !defined( 'ABSPATH' ) ){
	exit;
}

class AF_ResponseHandler_FormProcessExtension
{

	/**
	 * Init in WordPress, run on constructor
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public static function init()
	{
		add_filter( 'af_save_response', array( __CLASS__, 'response_handler' ), 10, 1 );
	}

	/**
	 * Starting response handler
	 */
	public static function response_handler( $response_id )
	{
		global $af_global, $ar_form_id;

		if( count( $af_global->response_handlers ) == 0 ){
			return;
		}

		foreach( $af_global->response_handlers AS $response_handler ){
			$response_handler->handle( $response_id, $_SESSION[ 'af_response' ][ $ar_form_id ] );
		}
	}
}

AF_ResponseHandler_FormProcessExtension::init();