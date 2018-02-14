<?php
/**
 * Number field class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Number' ) ) :

	/**
	 * Class for a number field.
	 *
	 * @since 1.0.0
	 */
	class Number extends Field {
		/**
		 * Field type identifier.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $slug = 'number';

		/**
		 * Type attribute for the input.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $type = 'number';

		/**
		 * Unit to show after the control, if any.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $unit = '';

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
			?>
			<input<?php echo $this->get_input_attrs( $input_attrs ); ?>>
			<?php if ( ! empty( $this->unit ) ) : ?>
				<span class="plugin-lib-unit"><?php echo $this->unit; ?></span>
			<?php endif;

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
			<# if ( ! _.isEmpty( data.unit ) ) { #>
				<span class="plugin-lib-unit">{{{ data.unit }}}</span>
			<# } #>

			<?php
			$this->print_repeatable_remove_button_template();
		}

		/**
		 * Transforms single field data into an array to be passed to JavaScript applications.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $current_value Current value of the field.
		 * @return array Field data to be JSON-encoded.
		 */
		protected function single_to_json( $current_value ) {
			$data = parent::single_to_json( $current_value );
			$data['unit'] = $this->unit;

			return $data;
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
			$format_as_int = ! empty( $this->input_attrs['step'] ) && is_int( $this->input_attrs['step'] );

			if ( empty( $value ) ) {
				if ( ! empty( $this->input_attrs['min'] ) ) {
					return $this->parse( $this->input_attrs['min'], $format_as_int );
				}
				return $this->parse( 0.0, $format_as_int );
			}

			$value = $this->parse( $value, $format_as_int );

			if ( ! empty( $this->input_attrs['min'] ) && $value < $this->parse( $this->input_attrs['min'], $format_as_int ) ) {
				return new WP_Error( 'field_number_lower_than', sprintf( $this->manager->get_message( 'field_number_lower_than' ), $this->format( $value, $format_as_int ), $this->label, $this->format( $this->input_attrs['min'], $format_as_int ) ) );
			}

			if ( ! empty( $this->input_attrs['max'] ) && $value > $this->parse( $this->input_attrs['max'], $format_as_int ) ) {
				return new WP_Error( 'field_number_greater_than', sprintf( $this->manager->get_message( 'field_number_greater_than' ), $this->format( $value, $format_as_int ), $this->label, $this->format( $this->input_attrs['max'], $format_as_int ) ) );
			}

			return $value;
		}

		/**
		 * Parses a numeric value.
		 *
		 * @since 1.0.0
		 *
		 * @param float|int|string $value         Numeric value.
		 * @param bool             $format_as_int Optional. Whether to parse the value as an integer. Default false.
		 * @return float|int Parsed value.
		 */
		protected function parse( $value, $format_as_int = false ) {
			if ( $format_as_int ) {
				return intval( $value );
			}

			return floatval( $value );
		}

		/**
		 * Formats a numeric value.
		 *
		 * @since 1.0.0
		 *
		 * @param float|int|string $value         Numeric value.
		 * @param bool             $format_as_int Optional. Whether to format the value as an integer. Default false.
		 * @param int|null         $decimals      Optional. Amount of decimals to output. Will be automatically determined
		 *                                        if not provided. Default null.
		 * @return string Formatted value.
		 */
		protected function format( $value, $format_as_int = false, $decimals = null ) {
			$value = $this->parse( $value, $format_as_int );

			if ( null === $decimals ) {
				if ( $format_as_int ) {
					$decimals = 0;
				} else {
					$decimals = 1;

					$detector = explode( '.', '' . $value );
					if ( isset( $detector[1] ) ) {
						$decimals = strlen( $detector[1] );
					}
				}
			}

			return number_format_i18n( $value, $decimals );
		}

		/**
		 * Returns names of the properties that must not be set through constructor arguments.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of forbidden properties.
		 */
		protected function get_forbidden_keys() {
			$keys = parent::get_forbidden_keys();
			$keys[] = 'type';

			return $keys;
		}
	}

endif;
