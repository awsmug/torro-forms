<?php
/**
 * Template tag email field class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Components;

/**
 * Class for a template tag email field.
 *
 * @since 1.0.0
 */
class Template_Tag_Email_Field extends Template_Tag_Text_Field {

	/**
	 * Field type identifier.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $slug = 'templatetagemail';

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
			// If only a placeholder is contained, let's assume it's a valid email placeholder.
			if ( 1 === substr_count( $value, '{' ) && 1 === substr_count( $value, '}' ) && '{' === substr( $value, 0, 1 ) && '}' === substr( $value, -1, 1 ) ) {
				return $value;
			}

			if ( ! is_email( $value ) ) {
				return new WP_Error( 'field_email_invalid', sprintf( $this->manager->get_message( 'field_email_invalid' ), $value, $this->label ) );
			}
		}

		return $value;
	}
}
