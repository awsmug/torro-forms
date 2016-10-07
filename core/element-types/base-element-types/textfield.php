<?php
/**
 * Core: Torro_Element_Type_Textfield class
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
 * Element type class for a text input
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Element_Type_Textfield extends Torro_Element_Type {
	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->name = 'textfield';
		$this->title = __( 'Textfield', 'torro-forms' );
		$this->description = __( 'Add an element which can be answered within a text field.', 'torro-forms' );
		$this->icon_url = torro()->get_asset_url( 'icon-textfield', 'png' );
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

		$input_type_value = $element->settings[ 'input_type' ]->value;
		$input_type_value = empty( $input_type_value ) ? 'text' : $input_type_value;

		$input_type_data = $this->get_input_types( $input_type_value );
		$data['type'] = $input_type_data['html_field_type'];

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

		return $data;
	}

	protected function settings_fields() {
		$_input_types = $this->get_input_types();
		$input_types = array();
		foreach ( $_input_types as $value => $data ) {
			if ( ! isset( $data['title'] ) || ! $data['title'] ) {
				continue;
			}
			$input_types[ $value ] = $data['title'];
		}

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
				'description'	=> __( 'The description will be shown after the element.', 'torro-forms' ),
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
				'description'	=> __( 'The minimum number of chars which can be typed in.', 'torro-forms' ),
				'default'		=> ''
			),
			'max_length'	=> array(
				'title'			=> __( 'Maximum length', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'The maximum number of chars which can be typed in.', 'torro-forms' ),
				'default'		=> ''
			),
			'input_type'	=> array(
				'title'			=> __( 'Input type', 'torro-forms' ),
				'type'			=> 'radio',
				'values'		=> $input_types,
				'description'	=> sprintf( __( '* Will be validated | Not all <a href="%s" target="_blank">HTML5 input types</a> are supportet by browsers!', 'torro-forms' ), 'http://www.wufoo.com/html5/' ),
				'default'		=> 'text'
			),
			'css_classes'	=> array(
				'title'			=> __( 'CSS Classes', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'Additional CSS Classes separated by whitespaces.', 'torro-forms' ),
				'default'		=> ''
			),
		);
	}

	protected function get_input_types( $value = false ) {
		$input_types = array(
			'text'				=> array(
				'title'				=> __( 'Standard Text', 'torro-forms' ),
				'html_field_type'	=> 'text',
			),
			'date'	=> array(
				'title'				=> __( 'Date', 'torro-forms' ),
				'html_field_type'	=> 'date',
			),
			'email_address'		=> array(
				'title'				=> __( 'Email-Address *', 'torro-forms' ),
				'html_field_type'	=> 'email',
				'callback'			=> 'is_email',
				'error_message'		=> __( 'Please input a valid email-address.', 'torro-forms' ),
			),
			'color'	=> array(
				'title'				=> __( 'Color', 'torro-forms' ),
				'html_field_type'	=> 'color',
			),
			'number'			=> array(
				'title'				=> __( 'Number *', 'torro-forms' ),
				'html_field_type'	=> 'number',
				'pattern'			=> '^[0-9]{1,}$',
				'error_message'		=> __( 'Please input a number.', 'torro-forms' ),
			),
			'number_decimal'	=> array(
				'title'				=> __( 'Decimal Number *', 'torro-forms' ),
				'html_field_type'	=> 'number',
				'pattern'			=> '^-?([0-9])+\.?([0-9])+$',
				'error_message'		=> __( 'Please input a decimal number.', 'torro-forms' ),
			),
			'search'	=> array(
				'title'				=> __( 'Search', 'torro-forms' ),
				'html_field_type'	=> 'search',
			),
			'tel'	=> array(
				'title'				=> __( 'Telephone', 'torro-forms' ),
				'html_field_type'	=> 'tel',
			),
			'time'	=> array(
				'title'				=> __( 'Time', 'torro-forms' ),
				'html_field_type'	=> 'time',
			),
			'url'	=> array(
				'title'				=> __( 'URL *', 'torro-forms' ),
				'pattern'	        => '\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]',
				'html_field_type'	=> 'url',
				'error_message'		=> __( 'Please input a valid URL.', 'torro-forms' ),
			),
			'week'	=> array(
				'title'				=> __( 'Week', 'torro-forms' ),
				'html_field_type'	=> 'week',
			),
		);

		$input_types = apply_filters( 'torro_element_textfield_input_types', $input_types );

		if ( ! empty( $value ) ) {
			if ( isset( $input_types[ $value ] ) ) {
				return $input_types[ $value ];
			}
			return false;
		}

		return $input_types;
	}

	public function validate( $input, $element ) {
		$min_length = $element->settings['min_length']->value;
		$max_length = $element->settings['max_length']->value;
		$input_type = $element->settings['input_type']->value;

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

		$input_types = $this->get_input_types( $input_type );

		if ( $input_types ) {
			$status = true;
			if ( isset( $input_types['callback'] ) && $input_types['callback'] && is_callable( $input_types['callback'] ) ) {
				$status = call_user_func( $input_types['callback'], $input );
			} elseif ( isset( $input_types['pattern'] ) && $input_types['pattern'] ) {
				$status = preg_match( '/' . $input_types['pattern'] . '/i', $input );
			}

			if ( ! $status ) {
				if ( isset( $input_types['error_message'] ) && $input_types['error_message'] ) {
					return new Torro_Error( 'invalid_input', $input_types['error_message'] );
				}
				return new Torro_Error( 'invalid_input', __( 'Invalid input.', 'torro-forms' ) );
			}
		}

		return $input;
	}
}

torro()->element_types()->register( 'Torro_Element_Type_Textfield' );
