<?php
/**
 * Length limits element type trait
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements\Element_Types;

use WP_Error;

/**
 * Trait for element type that supports minimum and/or maximum length limits.
 *
 * @since 1.1.0
 */
trait Length_Limits_Element_Type_Trait {

	/**
	 * Gets the text informing the user about minimum and/or maximum length limits.
	 *
	 * @since 1.1.0
	 *
	 * @param array   $settings Element settings.
	 * @param Element $element  Element object.
	 * @return string Length limits information text.
	 */
	protected function get_length_limits_text( $settings, $element ) {
		$limits_text = '';
		if ( ! empty( $settings['min_length'] ) && ! empty( $settings['max_length'] ) ) {
			/* translators: 1: minimum length, 2: maximum length */
			$limits_text = sprintf( __( 'Between %1$s and %2$s characters are required.', 'torro-forms' ), number_format_i18n( $settings['min_length'] ), number_format_i18n( $settings['max_length'] ) );
		} elseif ( ! empty( $settings['min_length'] ) ) {
			/* translators: %s: minimum length */
			$limits_text = sprintf( __( 'At least %s characters are required.', 'torro-forms' ), number_format_i18n( $settings['min_length'] ) );
		} elseif ( ! empty( $settings['max_length'] ) ) {
			/* translators: %s: maximum length */
			$limits_text = sprintf( __( 'A maximum of %s characters are allowed.', 'torro-forms' ), number_format_i18n( $settings['max_length'] ) );
		}

		/**
		 * Filters the text informing to the user about minimum and/or maximum length limits for an element.
		 *
		 * @since 1.1.0
		 *
		 * @param string  $limits_text Length limits information text.
		 * @param Element $element     Element object.
		 */
		return apply_filters( "{$this->manager->get_prefix()}element_length_limits_text", $limits_text, $element );
	}

	/**
	 * Validates the given value against minimum and/or maximum length limits.
	 *
	 * @since 1.1.0
	 *
	 * @param mixed   $value    Value to validate.
	 * @param array   $settings Element settings.
	 * @param Element $element  Element object.
	 * @return string|WP_Error Validated value, or error object on failure.
	 */
	protected function validate_length_limits( $value, $settings, $element ) {
		$value = (string) $value;

		if ( ! empty( $settings['min_length'] ) && strlen( $value ) < (int) $settings['min_length'] ) {
			return $this->create_error( 'value_too_short', __( 'The value you entered is too short.', 'torro-forms' ), $value );
		}

		if ( ! empty( $settings['max_length'] ) && strlen( $value ) > (int) $settings['max_length'] ) {
			return $this->create_error( 'value_too_long', __( 'The value you entered is too long.', 'torro-forms' ), $value );
		}

		return $value;
	}
}
