<?php
/**
 * Core: Torro_Element_Type_Dropdown class
 *
 * @package TorroForms
 * @subpackage CoreElements
 * @version 1.0.0-beta.7
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
		$this->description = __( 'Add an element which can be answered within a dropdown field.', 'torro-forms' );
		$this->icon_url = torro()->get_asset_url( 'icon-dropdown', 'png' );

		$this->input_answers = true;
		$this->answer_array = false;
		$this->input_answers = true;
	}

	/**
	 * Prepares data to render the element type HTML output.
	 *
	 * @since 1.0.0
	 *
	 * @param Torro_Element $element
	 *
	 * @return array
	 */
	public function to_json( $element ) {
		$data = parent::to_json( $element );

		array_unshift( $data['answers'], array(
			'answer_id'		=> 0,
			'label'			=> '- ' . __( 'Please select', 'torro-forms' ),
			'value'			=> 'please-select',
		) );

		return $data;
	}

	protected function settings_fields() {
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
			'css_classes'	=> array(
				'title'			=> __( 'CSS Classes', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'Additional CSS Classes separated by whitespaces.', 'torro-forms' ),
				'default'		=> ''
			),
		);
	}

	/**
	 * Validating user input
	 *
	 * @since 1.0.0
	 *
	 * @param array|string  $input      User input
	 * @param Torro_Element $element    Element object instance
	 *
	 * @return mixed|Torro_Error
	 */
	public function validate( $input, $element ) {
		$input = stripslashes( $input );

		if ( isset( $element->settings['required'] ) && 'yes' === $element->settings['required']->value && 'please-select' === $input ) {
			return new Torro_Error( 'missing_value', __( 'Please select a value.', 'torro-forms' ) );
		}

		$found = false;
		foreach ( $element->answers as $answer ) {
			if ( $input == $answer->answer ) {
				$found = TRUE;
			}
		}

		if( false === $found ) {
			new Torro_Error( 'invalid_value', __( 'Please select one of the provided values.', 'torro-forms' ) );
		}

		return parent::validate( $input, $element );
	}

}

torro()->element_types()->register( 'Torro_Element_Type_Dropdown' );
