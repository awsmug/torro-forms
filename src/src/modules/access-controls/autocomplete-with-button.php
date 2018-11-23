<?php
/**
 * Members access control class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Access_Controls;

use Leaves_And_Love\Plugin_Lib\Fields\Autocomplete;

/**
 * Class for an autocomplete with a button to use with custom JS logic following it.
 *
 * @since 1.0.0
 */
class Autocomplete_With_Button extends Autocomplete {

	/**
	 * Field type identifier.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $slug = 'autocompletewithbutton';

	/**
	 * Label for the custom button.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $button_label = '';

	/**
	 * Attributes for the custom button.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $button_attrs = array();

	/**
	 * Renders a single input for the field.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $current_value Current field value.
	 */
	protected function render_single_input( $current_value ) {
		$current_label = '';

		if ( ! empty( $current_value ) && ! empty( $this->autocomplete['rest_placeholder_label_route'] ) ) {
			$rest_url = rest_url( str_replace( '%value%', $current_value, $this->autocomplete['rest_placeholder_label_route'] ) );
			$request  = WP_REST_Request::from_url( $rest_url );
			if ( $request ) {
				$response = rest_do_request( $request );
				if ( ! is_wp_error( $response ) ) {
					$current_label = $this->replace_placeholders_with_data( $this->autocomplete['label_generator'], $response->get_data() );
				}
			}
		}

		$input_attrs = array(
			'type'  => $this->type,
			'value' => $current_label,
		);

		$hidden_attrs = array(
			'type'  => 'hidden',
			'name'  => $this->get_name_attribute(),
			'value' => $current_value,
		);
		?>
		<input<?php echo $this->get_input_attrs( $input_attrs ); // WPCS: XSS OK. ?>>
		<input<?php echo $this->attrs( $hidden_attrs ); // WPCS: XSS OK. ?>>
		<?php if ( ! empty( $this->button_label ) ) : ?>
			<button type="button"<?php echo $this->attrs( $this->button_attrs ); // WPCS: XSS OK. ?>><?php echo esc_html( $this->button_label ); ?></button>
		<?php endif; ?>
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
		<input type="<?php echo esc_attr( $this->type ); ?>"{{{ _.attrs( data.inputAttrs ) }}} value="{{ data.currentLabel }}">
		<input type="hidden" name="{{ data.name }}" value="{{ data.currentValue }}">
		<# if ( data.buttonLabel.length ) { #>
			<button type="button"{{{ _.attrs( data.buttonAttrs ) }}}>{{ data.buttonLabel }}</button>
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

		$data['buttonLabel'] = $this->button_label;
		$data['buttonAttrs'] = $this->button_attrs;

		return $data;
	}
}
