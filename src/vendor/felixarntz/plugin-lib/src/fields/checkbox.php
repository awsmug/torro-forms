<?php
/**
 * Checkbox field class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Checkbox' ) ) :

	/**
	 * Class for a checkbox field.
	 *
	 * @since 1.0.0
	 */
	class Checkbox extends Field {
		/**
		 * Field type identifier.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $slug = 'checkbox';

		/**
		 * Label mode for this field's label.
		 *
		 * Accepts values 'explicit', 'implicit', 'no_assoc', 'aria_hidden' and 'skip'.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $label_mode = 'skip';

		/**
		 * Text used as visual label displayed similar to other field labels.
		 *
		 * It is however not the actual semantic label, it only exists for visual purposes.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $visual_label = '';

		/**
		 * Renders the field's label.
		 *
		 * @since 1.0.0
		 */
		public function render_label() {
			$this->maybe_resolve_dependencies();

			echo '<div id="' . esc_attr( $this->get_id_attribute() . '-label-wrap' ) . '" class="label-wrap">';

			if ( ! empty( $this->visual_label ) ) {
				?>
				<span<?php echo $this->get_label_attrs(); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>>
					<?php echo wp_kses_data( $this->visual_label ); ?>
				</span>
				<?php
			}

			echo '</div>';
		}

		/**
		 * Prints a label template.
		 *
		 * @since 1.0.0
		 */
		public function print_label_template() {
			?>
			<div id="{{ data.id }}-label-wrap" class="label-wrap">
				<# if ( ! _.isEmpty( data.visualLabel ) ) { #>
					<span{{{ _.attrs( data.labelAttrs ) }}}>{{{ data.visualLabel }}}</span>
				<# } #>
			</div>
			<?php
		}

		/**
		 * Transforms all field data into an array to be passed to JavaScript applications.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $current_value Current value of the field.
		 * @return array Field data to be JSON-encoded.
		 */
		public function to_json( $current_value ) {
			$data = parent::to_json( $current_value );

			$data['visualLabel'] = $this->visual_label;

			return $data;
		}

		/**
		 * Renders a single input for the field.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $current_value Current field value.
		 */
		protected function render_single_input( $current_value ) {
			$input_attrs = array(
				'type'    => 'checkbox',
				'value'   => '1',
				'checked' => (bool) $current_value,
			);
			?>
			<input<?php echo $this->get_input_attrs( $input_attrs ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>>
			<label for="<?php echo esc_attr( $this->get_id_attribute() ); ?>"><?php echo wp_kses_data( $this->label ); ?></label>
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
			<input type="checkbox" value="1"{{{ _.attrs( data.inputAttrs ) }}}<# if ( data.currentValue ) { #> checked<# } #>>
			<label for="{{ data.id }}">{{ data.label }}</label>
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
			if ( ! $value ) {
				return false;
			}

			if ( is_string( $value ) && strtolower( $value ) === 'false' ) {
				return false;
			}

			return true;
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
			return false;
		}

		/**
		 * Returns the attributes for the field's label.
		 *
		 * @since 1.0.0
		 *
		 * @param array $label_attrs Array of custom label attributes.
		 * @param bool  $as_string   Optional. Whether to return them as an attribute
		 *                           string. Default true.
		 * @return array|string Either an array of `$key => $value` pairs, or an
		 *                      attribute string if `$as_string` is true.
		 */
		protected function get_label_attrs( $label_attrs = array(), $as_string = true ) {
			$label_attrs['aria-hidden'] = 'true';

			return parent::get_label_attrs( $label_attrs, $as_string );
		}
	}

endif;
