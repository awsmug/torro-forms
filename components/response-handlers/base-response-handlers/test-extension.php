<?php
/**
 * Email notifications Response handler
 *
 * Adds Email notifications for forms
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

class Questions_TestExtension extends  Questions_ResponseHandler{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->title = __( 'Test Extension', 'wcsc-locale' );
		$this->slug = 'testextension';
	}
	/**
	 * Handles the data after user submitted the form
	 * @param $response_id
	 * @param $response
	 */
	public function handle( $response_id, $response ){

	}

	public function option_content(){
		$html = 'Schau mal hier!';
		return $html;
	}
}
// qu_register_response_handler( 'Questions_TestExtension' );