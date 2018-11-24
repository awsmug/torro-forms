<?php
/**
 * URL field class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\URL' ) ) :

	/**
	 * Class for an URL field.
	 *
	 * @since 1.0.0
	 */
	class URL extends Text_Base {
		/**
		 * Field type identifier.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $slug = 'url';

		/**
		 * Type attribute for the input.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $type = 'url';

		/**
		 * Validates a single value for the field.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $value Value to validate. When null is passed, the method
		 *                     assumes no value was sent.
		 * @return mixed|WP_Error The validated value on success, or an error
		 *                        object on failure.
		 */
		protected function validate_single( $value = null ) {
			$value = parent::validate_single( $value );
			if ( is_wp_error( $value ) ) {
				return $value;
			}

			if ( ! empty( $value ) ) {
				if ( preg_match( '/\s/', $value ) ) {
					return new WP_Error( 'field_url_invalid', sprintf( $this->manager->get_message( 'field_url_invalid' ), $value, $this->label ) );
				}

				$parsed_url = wp_parse_url( $value );
				if ( empty( $parsed_url['host'] ) ) {
					return new WP_Error( 'field_url_invalid', sprintf( $this->manager->get_message( 'field_url_invalid' ), $value, $this->label ) );
				}

				if ( empty( $parsed_url['scheme'] ) && 0 !== strpos( $value, '//' ) ) {
					$value = 'http://' . $value;
				}
			}

			return $value;
		}
	}

endif;
