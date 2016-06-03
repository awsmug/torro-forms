<?php
/**
 * Core: Torro_Element_Type_Onechoice class
 *
 * @package TorroForms
 * @subpackage CoreElements
 * @version 1.0.0-beta.4
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Element type class for a one choice input
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Element_Type_Onechoice extends Torro_Element_Type {
	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->name = 'onechoice';
		$this->title = __( 'One Choice', 'torro-forms' );
		$this->description = __( 'Add an element which can be answered by selecting one of the given answers.', 'torro-forms' );
		$this->icon_url = torro()->get_asset_url( 'icon-onechoice', 'png' );

		$this->input_answers = true;
		$this->answer_array = false;
		$this->input_answers = true;
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

	public function validate( $input, $element ) {
		$input = stripslashes( $input );

		if ( isset( $element->settings['required'] ) && 'yes' === $element->settings['required']->value && empty( $input ) ) {
			return new Torro_Error( 'missing_value', __( 'Please select a value.', 'torro-forms' ) );
		}

		return $input;
	}
}

torro()->element_types()->register( 'Torro_Element_Type_Onechoice' );
