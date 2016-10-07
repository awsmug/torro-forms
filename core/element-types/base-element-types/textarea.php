<?php
/**
 * Core: Torro_Element_Type_Textarea class
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
 * Element type class for a textarea input
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Element_Type_Textarea extends Torro_Element_Type {
	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->name = 'textarea';
		$this->title = __( 'Textarea', 'torro-forms' );
		$this->description = __( 'Add an element which can be answered within a text area.', 'torro-forms' );
		$this->icon_url = torro()->get_asset_url( 'icon-textarea', 'png' );
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

		$data['limits_text'] = '';
		if ( ! empty( $element->settings['min_length'] ) && ! empty( $element->settings['min_length']->value ) && ! empty( $element->settings['max_length'] ) && ! empty( $element->settings['max_length']->value ) ) {
			$data['limits_text'] = sprintf( __( 'Between %1$s and %2$s characters are required.', 'torro-forms' ), number_format_i18n( $element->settings['min_length']->value ), number_format_i18n( $element->settings['max_length']->value ) );
		} elseif ( ! empty( $element->settings['min_length'] ) && ! empty( $element->settings['min_length']->value ) ) {
			$data['limits_text'] = sprintf( __( 'At least %s characters are required.', 'torro-forms' ), number_format_i18n( $element->settings['min_length']->value ) );
		} elseif ( ! empty( $element->settings['max_length'] ) && ! empty( $element->settings['max_length']->value ) ) {
			$data['limits_text'] = sprintf( __( 'A maximum of %s characters are allowed.', 'torro-forms' ), number_format_i18n( $element->settings['max_length']->value ) );
		}

		$data['placeholder'] = '';
		if ( ! empty( $element->settings['placeholder'] ) && ! empty( $element->settings['placeholder']->value ) ) {
			$data['placeholder'] = $element->settings['placeholder']->value;
		}
		$data['placeholder'] = apply_filters( 'torro_input_placeholder', $data['placeholder'], $element->id );

		$data['extra_attr'] = ' maxlength="' . $element->settings['max_length']->value . '" rows="' . $element->settings['rows']->value . '" cols="' . $element->settings['cols']->value . '"';

		return $data;
	}

	/**
	 * Setting fields
	 *
	 * @since 1.0.0
	 */
	protected function settings_fields() {
		$this->settings_fields = array(
			'placeholder'	=> array(
				'title'			=> __( 'Placeholder', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'Placeholder text will be shown until data have been putted in.', 'torro-forms' ),
				'default'		=> ''
			),
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
				'description'	=> __( 'Whether the user must input something.', 'torro-forms' ),
				'default'		=> 'no',
			),
			'min_length'	=> array(
				'title'			=> __( 'Minimum length', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'Minimum number of chars which have to be typed in.', 'torro-forms' ),
				'default'		=> ''
			),
			'max_length'	=> array(
				'title'			=> __( 'Maximum length', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'The maximum number of chars which can be typed in.', 'torro-forms' ),
				'default'		=> ''
			),
			'rows'			=> array(
				'title'			=> __( 'Rows', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'Number of rows for typing in  (can be overwritten by CSS).', 'torro-forms' ),
				'default'		=> '10'
			),
			'cols'			=> array(
				'title'			=> __( 'Columns', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'Number of columns for typing in (can be overwritten by CSS).', 'torro-forms' ),
				'default'		=> '75'
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
	 * @return array|mixed|string|Torro_Error
	 */
	public function validate( $input, $element ) {
		$min_length = $element->settings[ 'min_length' ]->value;
		$max_length = $element->settings[ 'max_length' ]->value;

		$input = trim( stripslashes( $input ) );

		if ( isset( $element->settings['required'] ) && 'yes' === $element->settings['required']->value && empty( $input ) ) {
			return new Torro_Error( 'missing_input', __( 'You must input something.', 'torro-forms' ) );
		}

		if( isset( $element->settings['required'] ) && 'no' === $element->settings['required']->value && empty( $input ) ) {
			return $input;
		}

		if ( ! empty( $min_length ) ) {
			if ( strlen( $input ) < $min_length ) {
				return new Torro_Error( 'input_too_short', __( 'The input ist too short.', 'torro-forms' ) );
			}
		}

		if ( ! empty( $max_length ) ) {
			if ( strlen( $input ) > $max_length ) {
				return new Torro_Error( 'input_too_long', __( 'The input ist too long.', 'torro-forms' ) );
			}
		}

		return parent::validate( $input, $element );
	}
}

torro()->element_types()->register( 'Torro_Element_Type_Textarea' );
