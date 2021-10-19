<?php
/**
 * Email field class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Email' ) ) :

	/**
	 * Class for an email field.
	 *
	 * @since 1.0.0
	 */
	class Email extends Text_Base {
		/**
		 * Field type identifier.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $slug = 'email';

		/**
		 * Type attribute for the input.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $type = 'email';

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
				if ( ! is_email( $value ) ) {
					return new WP_Error( 'field_email_invalid', sprintf( $this->manager->get_message( 'field_email_invalid' ), $value, $this->label ) );
				}
			}

			return $value;
		}
	}

endif;
