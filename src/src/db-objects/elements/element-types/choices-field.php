<?php
/**
 * Choices field class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements\Element_Types;

use Leaves_And_Love\Plugin_Lib\Fields\Field_Manager;
use Leaves_And_Love\Plugin_Lib\Fields\Text;

/**
 * Class for a choices field. Should only be used internally.
 *
 * @since 1.0.0
 */
class Choices_Field extends Text {
	/**
	 * Field type identifier.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $slug = 'torrochoices';

	/**
	 * Whether this is a repeatable field.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected $repeatable = true;

	/**
	 * Element ID to which the choices apply.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $element_id = 0;

	/**
	 * Field identifier to which the choices apply.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $field = '_main';

	/**
	 * Sort value for the choice.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $sort = 0;

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

		if ( null !== $this->index ) {
			$this->sort = $this->index;
		}

		?>
		<span class="torro-element-choice-move" aria-hidden="true">
			<?php torro()->assets()->render_icon( 'torro-icon-move' ); ?>
		</span>
		<input<?php echo $this->get_input_attrs( $input_attrs ); // WPCS: XSS OK. ?>>
		<input type="hidden" name="<?php echo esc_attr( str_replace( '[value]', '[element_id]', $input_attrs['name'] ) ); ?>" value="<?php echo esc_attr( $this->element_id ); ?>">
		<input type="hidden" name="<?php echo esc_attr( str_replace( '[value]', '[field]', $input_attrs['name'] ) ); ?>" value="<?php echo esc_attr( $this->field ); ?>">
		<input type="hidden" name="<?php echo esc_attr( str_replace( '[value]', '[sort]', $input_attrs['name'] ) ); ?>" value="<?php echo esc_attr( $this->sort ); ?>">
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
		<span class="torro-element-choice-move" aria-hidden="true">
			<?php torro()->assets()->render_icon( 'torro-icon-move' ); ?>
		</span>
		<input type="<?php echo esc_attr( $this->type ); ?>"{{{ _.attrs( data.inputAttrs ) }}} value="{{ data.currentValue }}">
		<input type="hidden" name="{{ data.inputAttrs.name.replace( '[value]', '[element_id]' ) }}" value="{{ data.element_id }}">
		<input type="hidden" name="{{ data.inputAttrs.name.replace( '[value]', '[field]' ) }}" value="{{ data.field }}">
		<input type="hidden" name="{{ data.inputAttrs.name.replace( '[value]', '[sort]' ) }}" value="{{ data.sort }}">
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

		$data['element_id'] = $this->element_id;
		$data['field']      = $this->field;
		$data['sort']       = $this->index;

		return $data;
	}
}
