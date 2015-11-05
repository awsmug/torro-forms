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

class AF_Form_Element_Text extends AF_Form_Element
{

	public function init()
	{
		$this->name = 'Text';
		$this->title = esc_attr__( 'Text', 'af-locale' );
		$this->description = esc_attr__( 'Add an Element which can be answered within a text field.', 'af-locale' );
		$this->icon_url = AF_URLPATH . '/assets/images/icon-textfield.png';
	}

	public function input_html()
	{
		$html  = '<label for="' . $this->get_input_name() . '">' . $this->label . '</label>';

		$html .= '<input type="text" name="' . $this->get_input_name() . '" value="' . $this->response . '" />';

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
				'values'      => array(
					'none'            => esc_attr__( 'No validation', 'af-locale' ),
					'numbers'         => esc_attr__( 'Numbers', 'af-locale' ),
					'numbers_decimal' => esc_attr__( 'Decimal Numbers', 'af-locale' ),
					'email_address'   => esc_attr__( 'Email-Address', 'af-locale' ),
				),
				'description' => esc_attr__( 'The will do a validation for the input.', 'af-locale' ),
				'default'     => 'none'
			),
		);
	}

	public function validate( $input )
	{

		$min_length = $this->settings[ 'min_length' ];
		$max_length = $this->settings[ 'max_length' ];
		$validation = $this->settings[ 'validation' ];

		$error = FALSE;

		if( !empty( $min_length ) )
		{
			if( strlen( $input ) < $min_length ):
				$this->validate_errors[] = esc_attr__( 'The input ist too short.', 'af-locale' ) . ' ' . sprintf( esc_attr__( 'It have to be at minimum %d and maximum %d chars.', 'af-locale' ), $min_length, $max_length );
				$error = TRUE;
			endif;
		}

		if( !empty( $max_length ) )
		{
			if( strlen( $input ) > $max_length ):
				$this->validate_errors[] = esc_attr__( 'The input is too long.', 'af-locale' ) . ' ' . sprintf( esc_attr__( 'It have to be at minimum %d and maximum %d chars.', 'af-locale' ), $min_length, $max_length );
				$error = TRUE;
			endif;
		}

		if( 'none' != $validation ):
			switch ( $validation )
			{
				case 'numbers':
					if( !preg_match( '/^[0-9]{1,}$/', $input ) ):
						$this->validate_errors[] = sprintf( esc_attr__( 'Please input a number.', 'af-locale' ), $max_length );
						$error = TRUE;
					endif;
					break;
				case 'numbers_decimal':
					if( !preg_match( '/^-?([0-9])+\.?([0-9])+$/', $input ) && !preg_match( '/^-?([0-9])+\,?([0-9])+$/', $input ) ):
						$this->validate_errors[] = sprintf( esc_attr__( 'Please input a decimal number.', 'af-locale' ), $max_length );
						$error = TRUE;
					endif;
					break;
				case 'email_address':
					if( !preg_match( '/^[\w-.]+[@][a-zA-Z0-9-.äöüÄÖÜ]{3,}\.[a-z.]{2,4}$/', $input ) ):
						$this->validate_errors[] = sprintf( esc_attr__( 'Please input a valid Email-Address.', 'af-locale' ), $max_length );
						$error = TRUE;
					endif;
					break;
			}
		endif;

		if( $error ):
			return FALSE;
		endif;

		return TRUE;
	}
}

af_register_form_element( 'AF_Form_Element_Text' );