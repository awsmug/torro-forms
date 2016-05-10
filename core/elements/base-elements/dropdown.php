<?php
/**
 * Core: Torro_Element_Type_Dropdown class
 *
 * @package TorroForms
 * @subpackage CoreElements
 * @version 1.0.0-beta.1
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Element type class for a dropdown input
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Element_Type_Dropdown extends Torro_Element_Type {
	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->name = 'dropdown';
		$this->title = __( 'Dropdown', 'torro-forms' );
		$this->description = __( 'Add an Element which can be answered within a dropdown field.', 'torro-forms' );
		$this->icon_url = torro()->get_asset_url( 'icon-dropdown', 'png' );

		$this->input_answers = true;
		$this->answer_array = false;
		$this->input_answers = true;
	}

	protected function get_input_html( $element ) {
		$star_required = '';
		if ( isset( $element->settings['required'] ) && 'yes' === $element->settings['required']->value ) {
			$star_required = ' <span class="required">*</span>';
			$aria_required = ' aria-required="true"';
		}

		$html  = '<label for="' . $this->get_input_id( $element ) . '">' . esc_html( $element->label ) . $star_required . '</label>';

		$html .= '<select id="' . $this->get_input_id( $element ) . '" name="' . $this->get_input_name() . '" aria-describedby="' . $this->get_input_id( $element ) . '_description ' . $this->get_input_id( $element ) . '_errors"' . $aria_required . '>';
		$html .= '<option value="please-select"> - ' . esc_html__( 'Please select', 'torro-forms' ) . ' -</option>';

		foreach ( $element->answers as $answer ) {
			$checked = '';

			if ( $element->response === $answer->answer ) {
				$checked = ' selected="selected"';
			}

			$html .= '<option value="' . esc_attr( $answer->answer ) . '" ' . $checked . '/> ' . esc_html( $answer->answer ) . '</option>';
		}

		$html .= '</select>';

		if ( ! empty( $element->settings['description']->value ) ) {
			$html .= '<div id="' . $this->get_input_id( $element ) . '_description" class="element-description">';
			$html .= esc_html( $element->settings['description']->value );
			$html .= '</div>';
		}

		return $html;
	}

	public function settings_fields() {
		$this->settings_fields = array(
			'description'	=> array(
				'title'			=> __( 'Description', 'torro-forms' ),
				'type'			=> 'textarea',
				'description'	=> __( 'The description will be shown after the field.', 'torro-forms' ),
				'default'		=> ''
			),
			'required'		=> array(
				'title'			=> __( 'Required?', 'torro-forms' ),
				'type'			=> 'radio',
				'values'		=> array(
					'yes'			=> __( 'Yes', 'torro-forms' ),
					'no'			=> __( 'No', 'torro-forms' ),
				),
				'description'	=> __( 'Whether the user must select a value.', 'torro-forms' ),
				'default'		=> 'yes',
			),
		);
	}

	public function validate( $input, $element ) {
		$input = stripslashes( $input );

		if ( isset( $element->settings['required'] ) && 'yes' === $element->settings['required']->value && 'please-select' === $input ) {
			return new Torro_Error( 'missing_value', __( 'Please select a value.', 'torro-forms' ) );
		}

		foreach ( $element->answers as $answer ) {
			if ( $input == $answer->answer ) {
				return $input;
			}
		}

		return new Torro_Error( 'invalid_value', __( 'Please select one of the provided values.', 'torro-forms' ) );
	}

}

torro()->element_types()->register( 'Torro_Element_Type_Dropdown' );
