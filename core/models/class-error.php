<?php
/**
 * General error class
 *
 * Instances of this class represent an error.
 * They are at the same time used as dummy objects to prevent fatal errors when chaining functions.
 *
 * @author  awesome.ug <contact@awesome.ug>
 * @package TorroForms
 * @version 1.0.0alpha1
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 rheinschmiede (contact@awesome.ug)
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

class Torro_Error extends WP_Error {
	public function __construct( $code = '', $message = '', $data = '' ) {
		parent::__construct( $code, $message, $data );
	}

	public function __call( $function, $args ) {
		if ( method_exists( $this, $function ) ) {
			return call_user_func_array( array( $this, $function ), $args );
		}

		return $this;
	}

	public function __get( $property ) {
		if ( property_exists( $this, $property ) ) {
			return $this->$property;
		}

		return $this;
	}
}
