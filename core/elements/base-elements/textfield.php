<?php
/**
 * Text Form Element
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core/Elements
 * @version 1.0.0alpha1
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
	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		parent::init();

		$this->type = $this->name = 'textfield';
		$this->title = __( 'Textfield', 'torro-forms' );
		$this->description = __( 'Add an Element which can be answered within a text field.', 'torro-forms' );
		$this->icon_url = torro()->get_asset_url( 'icon-textfield', 'png' );
	}

	public function get_input_html() {
		$input_type_value = $this->settings[ 'input_type' ]->value;
		$input_type_data = $this->get_input_types( $input_type_value );
		$input_type = $input_type_data[ 'html_field_type' ];

		$maybe_required = '';
		if ( isset( $this->settings['required'] ) && 'yes' === $this->settings['required']->value ) {
			$maybe_required = ' <span class="required">*</span>';
		}

		$html  = '<label for="' . $this->get_input_name() . '">' . esc_html( $this->label ) . $maybe_required . '</label>';

		$html .= '<input type="' . $input_type . '" name="' . $this->get_input_name() . '" value="' . esc_attr( $this->response ) . '" />';

		if ( ! empty( $this->settings['description'] ) ) {
			$html .= '<small>';
			$html .= esc_html( $this->settings['description']->value );
			$html .= '</small>';
		}

		return $html;
	}

	public function settings_fields() {
		$_input_types = $this->get_input_types();
		$input_types = array();
		foreach ( $_input_types as $value => $data ) {
			if ( ! isset( $data['title'] ) || ! $data['title'] ) {
				continue;
			}
			$input_types[ $value ] = $data['title'];
		}

		$this->settings_fields = array(
			'description'	=> array(
				'title'			=> __( 'Description', 'torro-forms' ),
				'type'			=> 'textarea',
				'description'	=> __( 'The description will be shown after the Element.', 'torro-forms' ),
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
				'default'		=> '0'
			),
			'max_length'	=> array(
				'title'			=> __( 'Maximum length', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'The maximum number of chars which can be typed in.', 'torro-forms' ),
				'default'		=> '100'
			),
			'input_type'	=> array(
				'title'			=> __( 'Input type', 'torro-forms' ),
				'type'			=> 'radio',
				'values'		=> $input_types,
				'description'	=> sprintf( __( '* Will be validated | Not all <a href="%s" target="_blank">HTML5 input types</a> are supportet by browsers!', 'torro-forms' ), 'http://www.wufoo.com/html5/' ),
				'default'		=> 'text'
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
				'error_message'		=> __( 'Please input a valid Email-Address.', 'torro-forms' ),
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

		$input_types = apply_filters( 'torro_text_field_input_types', $input_types );

		if ( ! empty( $value ) ) {
			if ( isset( $input_types[ $value ] ) ) {
				return $input_types[ $value ];
			}
			return false;
		}

		return $input_types;
	}

	public function validate( $input ) {
		$min_length = $this->settings[ 'min_length' ]->value;
		$max_length = $this->settings[ 'max_length' ]->value;
		$input_type = $this->settings[ 'input_type' ]->value;

		$input = trim( stripslashes( $input ) );

		if ( isset( $this->settings['required'] ) && 'yes' === $this->settings['required']->value && empty( $input ) ) {
			return new Torro_Error( 'missing_input', __( 'You must input something.', 'torro-forms' ) );
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

torro()->elements()->register( 'Torro_Form_Element_Textfield' );
