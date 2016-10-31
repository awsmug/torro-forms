<?php
/**
 * Core: Torro_Error class
 *
 * @package TorroForms
 * @subpackage Core
 * @version 1.0.0-beta.7
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * General error class
 *
 * Instances of this class represent an error.
 * They are at the same time used as dummy objects to prevent fatal errors when chaining functions.
 *
 * @since 1.0.0-beta.1
 */
class Torro_Error extends WP_Error {
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
