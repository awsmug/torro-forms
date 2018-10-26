<?php
/**
 * Field mappings field class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Actions\API_Action;

use Leaves_And_Love\Plugin_Lib\Fields\Field;

/**
 * Class for a  (very custom) field mappings field.
 *
 * @since 1.1.0
 */
class Field_Mappings_Field extends Field {

	/**
	 * Field type identifier.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $slug = 'fieldmappings';

	/**
	 * Backbone view class name to use for this field.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $backbone_view = 'FieldmappingsFieldView';

	/**
	 * Label mode for this field's label.
	 *
	 * Accepts values 'explicit', 'implicit', 'no_assoc', 'aria_hidden' and 'skip'.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $label_mode = 'skip';

	/**
	 * Data for the parameters that require field mappings.
	 *
	 * @since 1.1.0
	 * @var array
	 */
	protected $params = array();

	/**
	 * Renders a single input for the field.
	 *
	 * @since 1.1.0
	 *
	 * @param mixed $current_value Current field value.
	 */
	protected function render_single_input( $current_value ) {
		$id   = $this->get_id_attribute();
		$name = $this->get_name_attribute();

		$class = '';
		if ( ! empty( $this->input_classes ) ) {
			$class = ' class="' . esc_attr( implode( ' ', $this->input_classes ) ) . '"';
		}

		if ( ! is_array( $current_value ) ) {
			$current_value = (array) $current_value;
		}

		?>
		<fieldset id="<?php echo esc_attr( $id ); ?>"<?php echo $class; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>>
			<legend><?php echo wp_kses_data( $this->label ); ?></legend>
			<?php $this->render_repeatable_remove_button(); ?>
			<?php foreach ( $this->params as $param_slug => $param_info ) : ?>
				<?php $param_value = isset( $current_value[ $param_slug ] ) ? $current_value[ $param_slug ] : ( isset( $param_info['default'] ) ? $param_info['default'] : '' ); ?>
				<div id="<?php echo esc_attr( $id . '-' . $param_slug . '-wrap' ); ?>" class="field-mappings-param-wrap">
					<div class="field-mappings-param-label-wrap">
						<label id="<?php echo esc_attr( $id . '-' . $param_slug . '-label' ); ?>" for="<?php echo esc_attr( $id . '-' . $param_slug ); ?>" class="field-mappings-param-label">
							<?php echo esc_html( str_replace( '___', ':', $param_slug ) ); ?>
						</label>
					</div>
					<div class="field-mappings-param-content-wrap">
						<?php if ( 'array' === $param_info['type'] ) : ?>
							<div class="field-mappings-param-control"><?php esc_html_e( 'Array parameters are currently not supported.', 'torro-forms' ); ?></div>
						<?php elseif ( 'boolean' === $param_info['type'] ) : ?>
							<input type="checkbox" id="<?php echo esc_attr( $id . '-' . $param_slug ); ?>" name="<?php echo esc_attr( $name . '[' . $param_slug . ']' ); ?>" class="field-mappings-param-control plugin-lib-control plugin-lib-checkbox-control"<?php echo $param_value ? ' checked' : ''; ?>>
						<?php elseif ( 'integer' === $param_info['type'] ) : ?>
							<input type="number" id="<?php echo esc_attr( $id . '-' . $param_slug ); ?>" name="<?php echo esc_attr( $name . '[' . $param_slug . ']' ); ?>" class="field-mappings-param-control plugin-lib-control plugin-lib-number-control" value="<?php echo esc_attr( $param_value ); ?>"<?php echo ! empty( $param_info['minimum'] ) ? ' min="' . esc_attr( $param_info['minimum'] ) . '"' : ''; ?><?php echo ! empty( $param_info['maximum'] ) ? ' max="' . esc_attr( $param_info['maximum'] ) . '"' : ''; ?> step="1">
						<?php elseif ( 'float' === $param_info['type'] || 'number' === $param_info['type'] ) : ?>
							<input type="number" id="<?php echo esc_attr( $id . '-' . $param_slug ); ?>" name="<?php echo esc_attr( $name . '[' . $param_slug . ']' ); ?>" class="field-mappings-param-control plugin-lib-control plugin-lib-number-control" value="<?php echo esc_attr( $param_value ); ?>">
						<?php elseif ( 'string' === $param_info['type'] && ! empty( $param_info['enum'] ) ) : ?>
							<select id="<?php echo esc_attr( $id . '-' . $param_slug ); ?>" name="<?php echo esc_attr( $name . '[' . $param_slug . ']' ); ?>" class="field-mappings-param-control plugin-lib-control plugin-lib-select-control">
								<?php foreach ( $param_info['enum'] as $enum_value ) : ?>
									<option value="<?php echo esc_attr( $enum_value ); ?>"<?php echo $param_value === $enum_value ? ' selected' : ''; ?>><?php echo esc_html( $enum_value ); ?></option>
								<?php endforeach; ?>
							</select>
						<?php else : ?>
							<input type="text" id="<?php echo esc_attr( $id . '-' . $param_slug ); ?>" name="<?php echo esc_attr( $name . '[' . $param_slug . ']' ); ?>" class="field-mappings-param-control plugin-lib-control plugin-lib-text-control" value="<?php echo esc_attr( $param_value ); ?>">
						<?php endif; ?>
						<?php if ( ! empty( $param_info['description'] ) ) : ?>
							<p class="description"><?php echo wp_kses_data( $param_info['description'] ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</fieldset>
		<?php
	}

	/**
	 * Prints a single input template.
	 *
	 * @since 1.1.0
	 */
	protected function print_single_input_template() {
		?>
		<fieldset id="{{ data.id }}"<# if ( data.inputAttrs.class ) { #> class="{{ data.inputAttrs.class }}"<# } #>>
			<legend>{{{ data.label }}}</legend>
			<?php $this->print_repeatable_remove_button_template(); ?>
			<# _.each( data.params, function( paramInfo, paramSlug ) { #>
				<# var paramValue = data.currentValue[ paramSlug ] || paramInfo['default']; #>
				<div id="{{ data.id + '-' + paramSlug + '-wrap' }}" class="field-mappings-param-wrap">
					<div class="field-mappings-param-label-wrap">
						<label id="{{ data.id + '-' + paramSlug + '-label' }}" for="{{ data.id + '-' + paramSlug }}" class="field-mappings-param-label">
							{{ paramSlug.replace( '___', ':' ) }}
						</label>
					</div>
					<div class="field-mappings-param-content-wrap">
						<# if ( 'array' === paramInfo.type ) { #>
							<div class="field-mappings-param-control"><?php esc_html_e( 'Array parameters are currently not supported.', 'torro-forms' ); ?></div>
						<# } else if ( 'boolean' === paramInfo.type ) { #>
							<input type="checkbox" id="{{ data.id + '-' + paramSlug }}" name="{{ data.name + '[' + paramSlug + ']' }}" class="field-mappings-param-control" value="1"<# if ( paramValue ) { #> checked<# } #>>
						<# } else if ( 'integer' === paramInfo.type ) { #>
							<input type="number" id="{{ data.id + '-' + paramSlug }}" name="{{ data.name + '[' + paramSlug + ']' }}" class="field-mappings-param-control" value="{{ paramValue }}"<# if ( paramInfo.minimum ) { #> min="{{ paramInfo.minimum }}"<# } #><# if ( paramInfo.maximum ) { #> max="{{ paramInfo.maximum }}"<# } #> step="1">
						<# } else if ( 'float' === paramInfo.type || 'number' === paramInfo.type ) { #>
							<input type="number" id="{{ data.id + '-' + paramSlug }}" name="{{ data.name + '[' + paramSlug + ']' }}" class="field-mappings-param-control" value="{{ paramValue }}">
						<# } else if ( 'string' === paramInfo.type && paramInfo.enum && paramInfo.enum.length ) { #>
							<select id="{{ data.id + '-' + paramSlug }}" name="{{ data.name + '[' + paramSlug + ']' }}" class="field-mappings-param-control">
								<# _.each( paramInfo.enum, function( enumValue ) { #>
									<option value="{{ enumValue }}"<# if ( paramValue === String( enumValue ) ) { #> selected<# } #>>{{ enumValue }}</option>
								<# } ) #>
							</select>
						<# } else { #>
							<input type="text" id="{{ data.id + '-' + paramSlug }}" name="{{ data.name + '[' + paramSlug + ']' }}" class="field-mappings-param-control" value="{{ paramValue }}">
						<# } #>
						<# if ( ! _.isEmpty( paramInfo.description ) ) { #>
							<p class="description">{{{ paramInfo.description }}}</p>
						<# } #>
					</div>
				</div>
			<# } ) #>
		</fieldset>
		<?php
	}

	/**
	 * Transforms single field data into an array to be passed to JavaScript applications.
	 *
	 * @since 1.1.0
	 *
	 * @param mixed $current_value Current value of the field.
	 * @return array Field data to be JSON-encoded.
	 */
	protected function single_to_json( $current_value ) {
		if ( ! is_array( $current_value ) ) {
			$current_value = (array) $current_value;
		}

		$data = parent::single_to_json( $current_value );

		$data['params'] = $this->params;
		if ( empty( $data['params'] ) ) {
			$data['params'] = new \stdClass(); // Ensure an object in JavaScript.
		}

		return $data;
	}

	/**
	 * Validates a single value for the field.
	 *
	 * @since 1.1.0
	 *
	 * @param mixed $value Value to validate. When null is passed, the method
	 *                     assumes no value was sent.
	 * @return mixed|WP_Error The validated value on success, or an error
	 *                        object on failure.
	 */
	protected function validate_single( $value = null ) {
		// For now, skip actual validation due to complexity.
		if ( empty( $value ) ) {
			return array();
		}

		if ( ! is_array( $value ) ) {
			return array();
		}

		return $value;
	}

	/**
	 * Checks whether a value is considered empty.
	 *
	 * @since 1.1.0
	 *
	 * @param mixed $value Value to check whether its empty.
	 * @return bool True if the value is considered empty, false otherwise.
	 */
	protected function is_value_empty( $value ) {
		return empty( $value );
	}
}
