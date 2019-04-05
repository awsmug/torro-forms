<?php
/**
 * Text field base class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Text_Base' ) ) :

	/**
	 * Base class for any text field.
	 *
	 * @since 1.0.0
	 */
	abstract class Text_Base extends Field {
		/**
		 * Type attribute for the input.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $type = 'text';

		/**
		 * Renders a single input for the field.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $current_value Current field value.
		 */
		protected function render_single_input( $current_value ) {
			$input_attrs = array(
				'type'  => $this->type,
				'value' => $current_value,
			);

			if ( ! empty( $this->input_attrs['minlength'] ) ) {
				$input_attrs['minlength'] = absint( $this->input_attrs['minlength'] );
			}

			if ( ! empty( $this->input_attrs['maxlength'] ) ) {
				$input_attrs['maxlength'] = absint( $this->input_attrs['maxlength'] );
			}

			?>
			<input<?php echo $this->get_input_attrs( $input_attrs ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>>
			<?php
			$this->render_repeatable_remove_button();
		}

		/**
		 * Prints a single input template.
		 *
		 * @since 1.0.0
		 */
		protected function print_single_input_template() {
			?>
			<input type="<?php echo esc_attr( $this->type ); ?>"{{{ _.attrs( data.inputAttrs ) }}} value="{{ data.currentValue }}">
			<?php
			$this->print_repeatable_remove_button_template();
		}

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
			if ( empty( $value ) ) {
				return '';
			}

			$value = trim( $value );

			if ( ! empty( $this->input_attrs['minlength'] ) ) {
				$minlength = absint( $this->input_attrs['minlength'] );

				if ( strlen( $value ) < $minlength ) {
					return new WP_Error( 'field_text_too_short', sprintf( translate_nooped_plural( $this->manager->get_message( 'field_text_too_short', true ), $minlength ), $value, $this->label, number_format_i18n( $minlength ) ) );
				}
			}

			if ( ! empty( $this->input_attrs['maxlength'] ) ) {
				$maxlength = absint( $this->input_attrs['maxlength'] );

				if ( strlen( $value ) > $maxlength ) {
					return new WP_Error( 'field_text_too_long', sprintf( translate_nooped_plural( $this->manager->get_message( 'field_text_too_long', true ), $maxlength ), $value, $this->label, number_format_i18n( $maxlength ) ) );
				}
			}

			return $value;
		}

		/**
		 * Checks whether a value is considered empty.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $value Value to check whether its empty.
		 * @return bool True if the value is considered empty, false otherwise.
		 */
		protected function is_value_empty( $value ) {
			if ( is_string( $value ) ) {
				$value = trim( $value );
			}

			return empty( $value );
		}

		/**
		 * Returns names of the properties that must not be set through constructor arguments.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of forbidden properties.
		 */
		protected function get_forbidden_keys() {
			$keys   = parent::get_forbidden_keys();
			$keys[] = 'type';

			return $keys;
		}
	}

endif;
