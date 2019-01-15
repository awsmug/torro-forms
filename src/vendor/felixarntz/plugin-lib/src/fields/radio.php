<?php
/**
 * Radio field class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Radio' ) ) :

	/**
	 * Class for a radio field.
	 *
	 * @since 1.0.0
	 */
	class Radio extends Select_Base {
		/**
		 * Field type identifier.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $slug = 'radio';

		/**
		 * Label mode for this field's label.
		 *
		 * Accepts values 'explicit', 'implicit', 'no_assoc', 'aria_hidden' and 'skip'.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $label_mode = 'aria_hidden';

		/**
		 * Renders a single input for the field.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $current_value Current field value.
		 */
		protected function render_single_input( $current_value ) {
			$current_value = array_map( 'strval', (array) $current_value );

			$input_attrs = $this->get_input_attrs(
				array(
					'type' => 'radio',
				),
				false
			);

			if ( $this->multi ) {
				$input_attrs['type'] = 'checkbox';
			}

			$base_id = $input_attrs['id'];

			$count = 0;

			?>
			<fieldset>
				<legend class="screen-reader-text"><?php echo wp_kses_data( $this->label ); ?></legend>

				<?php foreach ( $this->choices as $value => $label ) : ?>
					<?php
					$count++;

					$input_attrs['id']      = $base_id . '-' . $count;
					$input_attrs['value']   = $value;
					$input_attrs['checked'] = in_array( (string) $value, $current_value, true );
					?>
					<div class="plugin-lib-input-choice-wrap">
						<input<?php echo $this->attrs( $input_attrs ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>>
						<label for="<?php echo esc_attr( $input_attrs['id'] ); ?>"><?php echo wp_kses_data( $label ); ?></label>
					</div>
				<?php endforeach; ?>
			</fieldset>
			<?php
		}

		/**
		 * Prints a single input template.
		 *
		 * @since 1.0.0
		 */
		protected function print_single_input_template() {
			if ( $this->multi ) {
				$type    = 'checkbox';
				$checked = '<# if ( _.isArray( data.currentValue ) && _.contains( data.currentValue, String( value ) ) ) { #> checked<# } #>';
			} else {
				$type    = 'radio';
				$checked = '<# if ( data.currentValue === String( value ) ) { #> checked<# } #>';
			}

			?>
			<fieldset>
				<legend class="screen-reader-text">{{ data.label }}</legend>

				<# _.each( data.choices, function( label, value, obj ) { #>
					<div class="plugin-lib-input-choice-wrap">
						<input type="<?php echo esc_attr( $type ); ?>"{{{ _.attrs( _.extend( {}, data.inputAttrs, {
							id: data.inputAttrs.id + _.indexOf( _.keys( obj ), value ),
							name: data.inputAttrs.name
						} ) ) }}} value="{{ value }}"<?php echo $checked; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>>
						<label for="{{ data.inputAttrs.id + _.indexOf( _.keys( obj ), value ) }}">{{ label }}</label>
					</div>
				<# } ) #>
			</fieldset>
			<?php
			$this->print_repeatable_remove_button_template();
		}
	}

endif;
