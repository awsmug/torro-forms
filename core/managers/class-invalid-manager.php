<?php
/**
 * Torro Forms dummy manager class for invalid instances.
 *
 * Prevents fatal errors from using chained functions.
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 2015-04-16
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

class Torro_Invalid_Manager extends Torro_Manager {
	protected $function_name = '';

	public function __construct( $base_class = 'invalid', $added_callback = null ) {
		$this->base_class = 'invalid';
		$this->added_callback = null;
	}

	public function set_invalid_function( $function_name ) {
		$this->function_name = $function_name;
	}

	public function add( $class_name ) {
		return new WP_Error( 'torro_instance_invalid', sprintf( __( 'The function name %s does not correspond to a valid instance manager.', 'torro-forms' ), $this->function_name ) );
	}

	public function get( $name ) {
		return false;
	}

	public function get_all() {
		return array();
	}
}
