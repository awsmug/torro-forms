<?php
/**
 * Text Form Element
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core/Elements
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Torro_Form_Element_Textfield extends Torro_Form_Element {
	private static $instances = array();

	public static function instance( $id = null ) {
		$slug = $id;
		if ( null === $slug ) {
			$slug = 'CLASS';
		}
		if ( ! isset( self::$instances[ $slug ] ) ) {
			self::$instances[ $slug ] = new self( $id );
		}
		return self::$instances[ $slug ];
	}

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct( $id = null ) {
		parent::__construct( $id );
	}

	protected function init() {
		$this->type = 'textfield';
		$this->title = __( 'Textfield', 'torro-forms' );
		$this->description = __( 'Add an Element which can be answered within a text field.', 'torro-forms' );
		$this->icon_url = torro()->get_asset_url( 'icon-textfield', 'png' );
	}

	public function input_html() {
		$input_type = 'text';

		$validation = $this->settings[ 'validation' ];
		$validation_data = $this->get_validations( $validation );

		if ( $validation_data && isset( $validation_data['html_field_type'] ) && $validation_data['html_field_type'] ) {
			$input_type = $validation_data['html_field_type'];
		}

		$html  = '<label for="' . $this->get_input_name() . '">' . esc_html( $this->label ) . '</label>';

		$html .= '<input type="' . $input_type . '" name="' . $this->get_input_name() . '" value="' . esc_attr( $this->response ) . '" />';

		if ( ! empty( $this->settings['description'] ) ) {
			$html .= '<small>';
			$html .= esc_html( $this->settings['description'] );
			$html .= '</small>';
		}

		return $html;
	}

	public function settings_fields() {
		$_validations = $this->get_validations();
		$validations = array();
		foreach ( $_validations as $value => $data ) {
			if ( ! isset( $data['title'] ) || ! $data['title'] ) {
				continue;
			}
			$validations[ $value ] = $data['title'];
		}

		$this->settings_fields = array(
			'description'	=> array(
				'title'			=> __( 'Description', 'torro-forms' ),
				'type'			=> 'textarea',
				'description'	=> __( 'The description will be shown after the Element.', 'torro-forms' ),
				'default'		=> ''
			),
			'min_length'	=> array(
				'title'			=> __( 'Minimum length', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'The minimum number of chars which can be typed in.', 'torro-forms' ),
				'default'		=> '0'
			),
			'max_length'	=> array(
				'title'			=> __( 'Maximum length', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'The maximum number of chars which can be typed in.', 'torro-forms' ),
				'default'		=> '100'
			),
			'validation'	=> array(
				'title'			=> __( 'String Validation', 'torro-forms' ),
				'type'			=> 'radio',
				'values'		=> $validations,
				'description'	=> __( 'The will do a validation for the input.', 'torro-forms' ),
				'default'		=> 'none'
			),
		);
	}

	protected function get_validations( $value = false ) {
		$validations = array(
			'none'				=> array(
				'title'				=> __( 'No validation', 'torro-forms' ),
			),
			'number'			=> array(
				'title'				=> __( 'Number', 'torro-forms' ),
				'html_field_type'	=> 'number',
				'pattern'			=> '^[0-9]{1,}$',
				'error_message'		=> __( 'Please input a number.', 'torro-forms' ),
			),
			'number_decimal'	=> array(
				'title'				=> __( 'Decimal Number', 'torro-forms' ),
				'html_field_type'	=> 'number',
				'pattern'			=> '^-?([0-9])+\.?([0-9])+$',
				'error_message'		=> __( 'Please input a decimal number.', 'torro-forms' ),
			),
			'email_address'		=> array(
				'title'				=> __( 'Email-Address', 'torro-forms' ),
				'html_field_type'	=> 'email',
				'callback'			=> 'is_email',
				'error_message'		=> __( 'Please input a valid Email-Address.', 'torro-forms' ),
			),
		);

		$validations = apply_filters( 'torro_text_field_validations', $validations );

		if ( $value ) {
			if ( isset( $validations[ $value ] ) ) {
				return $validations[ $value ];
			}
			return false;
		}

		return $validations;
	}

	public function validate( $input ) {
		$min_length = $this->settings[ 'min_length' ];
		$max_length = $this->settings[ 'max_length' ];
		$validation = $this->settings[ 'validation' ];

		$error = false;

		if ( ! empty( $min_length ) ) {
			if ( strlen( $input ) < $min_length ) {
				$this->validate_errors[] = __( 'The input ist too short.', 'torro-forms' ) . ' ' . sprintf( __( 'It have to be at minimum %d and maximum %d chars.', 'torro-forms' ), $min_length, $max_length );
				$error = true;
			}
		}

		if ( ! empty( $max_length ) ) {
			if ( strlen( $input ) > $max_length ) {
				$this->validate_errors[] = __( 'The input is too long.', 'torro-forms' ) . ' ' . sprintf( __( 'It have to be at minimum %d and maximum %d chars.', 'torro-forms' ), $min_length, $max_length );
				$error = true;
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

		return ! $error;
	}
}

torro()->elements()->register( 'Torro_Form_Element_Textfield' );
