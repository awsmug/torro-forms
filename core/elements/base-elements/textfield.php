<?php
/**
 * Text Form Element
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Core/Elements
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
// No direct access is allowed
if( !defined( 'ABSPATH' ) )
{
	exit;
}

class AF_Form_Element_Textfield extends AF_Form_Element
{

	public function init()
	{
		$this->name = 'textfield';
		$this->title = esc_attr__( 'Textfield', 'af-locale' );
		$this->description = esc_attr__( 'Add an Element which can be answered within a text field.', 'af-locale' );
		$this->icon_url = AF_URLPATH . 'assets/img/icon-textfield.png';
	}

	public function input_html()
	{
		$input_type = 'text';

		$validation = $this->settings[ 'validation' ];
		$validation_data = $this->get_validations( $validation );

		if ( $validation_data && isset( $validation_data['html_field_type'] ) && $validation_data['html_field_type'] ) {
			$input_type = $validation_data['html_field_type'];
		}

		$html  = '<label for="' . $this->get_input_name() . '">' . $this->label . '</label>';

		$html .= '<input type="' . $input_type . '" name="' . $this->get_input_name() . '" value="' . $this->response . '" />';

		if( !empty( $this->settings[ 'description' ] ) )
		{
			$html .= '<small>';
			$html .= $this->settings[ 'description' ];
			$html .= '</small>';
		}

		return $html;
	}

	public function settings_fields()
	{
		$_validations = $this->get_validations();
		$validations = array();
		foreach ( $_validations as $value => $data ) {
			if ( ! isset( $data['title'] ) || ! $data['title'] ) {
				continue;
			}
			$validations[ $value ] = $data['title'];
		}

		$this->settings_fields = array(
			'description' => array(
				'title'       => esc_attr__( 'Description', 'af-locale' ),
				'type'        => 'textarea',
				'description' => esc_attr__( 'The description will be shown after the Element.', 'af-locale' ),
				'default'     => ''
			),
			'min_length'  => array(
				'title'       => esc_attr__( 'Minimum length', 'af-locale' ),
				'type'        => 'text',
				'description' => esc_attr__( 'The minimum number of chars which can be typed in.', 'af-locale' ),
				'default'     => '0'
			),
			'max_length'  => array(
				'title'       => esc_attr__( 'Maximum length', 'af-locale' ),
				'type'        => 'text',
				'description' => esc_attr__( 'The maximum number of chars which can be typed in.', 'af-locale' ),
				'default'     => '100'
			),
			'validation'  => array(
				'title'       => esc_attr__( 'String Validation', 'af-locale' ),
				'type'        => 'radio',
				'values'      => $validations,
				'description' => esc_attr__( 'The will do a validation for the input.', 'af-locale' ),
				'default'     => 'none'
			),
		);
	}

	protected function get_validations( $value = false ) {
		$validations = array(
			'none'				=> array(
				'title'				=> esc_attr__( 'No validation', 'af-locale' ),
			),
			'number'			=> array(
				'title'				=> esc_attr__( 'Number', 'af-locale' ),
				'html_field_type'	=> 'number',
				'pattern'			=> '^[0-9]{1,}$',
				'error_message'		=> esc_attr__( 'Please input a number.', 'af-locale' ),
			),
			'number_decimal'	=> array(
				'title'				=> esc_attr__( 'Decimal Number', 'af-locale' ),
				'html_field_type'	=> 'number',
				'pattern'			=> '^-?([0-9])+\.?([0-9])+$',
				'error_message'		=> esc_attr__( 'Please input a decimal number.', 'af-locale' ),
			),
			'email_address'		=> array(
				'title'				=> esc_attr__( 'Email-Address', 'af-locale' ),
				'html_field_type'	=> 'email',
				'callback'			=> 'is_email',
				'error_message'		=> esc_attr__( 'Please input a valid Email-Address.', 'af-locale' ),
			),
		);

		$validations = apply_filters( 'af_text_field_validations', $validations );

		if ( $value ) {
			if ( isset( $validations[ $value ] ) ) {
				return $validations[ $value ];
			}
			return false;
		}

		return $validations;
	}

	public function validate( $input )
	{

		$min_length = $this->settings[ 'min_length' ];
		$max_length = $this->settings[ 'max_length' ];
		$validation = $this->settings[ 'validation' ];

		$error = FALSE;

		if ( !empty( $min_length ) ) {
			if ( strlen( $input ) < $min_length ) {
				$this->validate_errors[] = esc_attr__( 'The input ist too short.', 'af-locale' ) . ' ' . sprintf( esc_attr__( 'It have to be at minimum %d and maximum %d chars.', 'af-locale' ), $min_length, $max_length );
				$error = TRUE;
			}
		}

		if ( ! empty( $max_length ) ) {
			if ( strlen( $input ) > $max_length ) {
				$this->validate_errors[] = esc_attr__( 'The input is too long.', 'af-locale' ) . ' ' . sprintf( esc_attr__( 'It have to be at minimum %d and maximum %d chars.', 'af-locale' ), $min_length, $max_length );
				$error = TRUE;
			}
		}

		$validation_data = $this->get_validations( $validation );

		if ( $validation_data ) {
			$status = true;
			if ( isset( $validation_data['callback'] ) && $validation_data['callback'] && is_callable( $validation_data['callback'] ) ) {
				$status = call_user_func( $validation_data['callback'], $input );
			} elseif ( isset( $validation_data['pattern'] ) && $validation_data['pattern'] ) {
				$status = preg_match( '/' . $validation_data['pattern'] . '/', $input );
			}

			if ( ! $status ) {
				$error = true;
				if ( isset( $validation_data['error_message'] ) && $validation_data['error_message'] ) {
					$this->validate_errors[] = $validation_data['error_message'];
				}
			}
		}

		if( $error ) {
			return FALSE;
		}

		return TRUE;
	}
}

af_register_form_element( 'AF_Form_Element_Textfield' );
