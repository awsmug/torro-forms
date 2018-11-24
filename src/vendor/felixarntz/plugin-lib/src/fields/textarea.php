<?php
/**
 * Textarea field class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Textarea' ) ) :

	/**
	 * Class for a textarea field.
	 *
	 * @since 1.0.0
	 */
	class Textarea extends Text_Base {
		/**
		 * Field type identifier.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $slug = 'textarea';

		/**
		 * Type attribute for the input.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $type = 'textarea';

		/**
		 * Renders a single input for the field.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $current_value Current field value.
		 */
		protected function render_single_input( $current_value ) {
			?>
			<textarea<?php echo $this->get_input_attrs(); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>><?php echo esc_textarea( $current_value ); ?></textarea>
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
			<textarea{{{ _.attrs( data.inputAttrs ) }}}>{{ data.currentValue }}</textarea>
			<?php
			$this->print_repeatable_remove_button_template();
		}
	}

endif;
