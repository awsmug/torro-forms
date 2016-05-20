<?php
/**
 * Core: Torro_Element_Dropdown class
 *
 * @package TorroForms
 * @subpackage CoreElements
 * @version 1.0.0-beta.2
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Element class for a dropdown input
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Element_Dropdown extends Torro_Element {
	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		parent::init();

		$this->type = $this->name = 'dropdown';
		$this->title = __( 'Dropdown', 'torro-forms' );
		$this->description = __( 'Add an element which can be answered within a dropdown field.', 'torro-forms' );
		$this->icon_url = torro()->get_asset_url( 'icon-dropdown', 'png' );

		$this->input_answers = true;
		$this->answer_array = false;
		$this->input_answers = true;
	}

	protected function get_input_html() {
		$star_required = '';
		if ( isset( $this->settings['required'] ) && 'yes' === $this->settings['required']->value ) {
			$star_required = ' <span class="required">*</span>';
			$aria_required = ' aria-required="true"';
		}

		$html  = '<label for="' . $this->get_input_id() . '">' . esc_html( $this->label ) . $star_required . '</label>';

		$html .= '<select id="' . $this->get_input_id() . '" name="' . $this->get_input_name() . '" aria-describedby="' . $this->get_input_id() . '_description ' . $this->get_input_id() . '_errors"' . $aria_required . '>';
		$html .= '<option value="please-select"> - ' . esc_html__( 'Please select', 'torro-forms' ) . ' -</option>';

		foreach ( $this->answers as $answer ) {
			$checked = '';

			if ( $this->response === $answer->answer ) {
				$checked = ' selected="selected"';
			}

			$html .= '<option value="' . esc_attr( $answer->answer ) . '" ' . $checked . '/> ' . esc_html( $answer->answer ) . '</option>';
		}

		$html .= '</select>';

		if ( ! empty( $this->settings['description']->value ) ) {
			$html .= '<div id="' . $this->get_input_id() . '_description" class="element-description">';
			$html .= esc_html( $this->settings['description']->value );
			$html .= '</div>';
		}

		return $html;
	}

	public function settings_fields() {
		$this->settings_fields = array(
			'description'	=> array(
				'title'			=> __( 'Description', 'torro-forms' ),
				'type'			=> 'textarea',
				'description'	=> __( 'The description will be shown after the input.', 'torro-forms' ),
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

	public function validate( $input ) {
		$input = stripslashes( $input );

		if ( isset( $this->settings['required'] ) && 'yes' === $this->settings['required']->value && 'please-select' === $input ) {
			return new Torro_Error( 'missing_value', __( 'Please select a value.', 'torro-forms' ) );
		}

		foreach ( $this->answers as $answer ) {
			if ( $input == $answer->answer ) {
				return $input;
			}
		}

		return new Torro_Error( 'invalid_value', __( 'Please select one of the provided values.', 'torro-forms' ) );
	}

}

torro()->element_types()->register( 'Torro_Element_Dropdown' );
