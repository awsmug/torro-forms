<?php
/**
 * Textarea Form Element
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

class Torro_Form_Element_Textarea extends Torro_Form_Element {
	public function init() {
		$this->name = 'textarea';
		$this->title = __( 'Textarea', 'torro-forms' );
		$this->description = __( 'Add an Element which can be answered within a text area.', 'torro-forms' );
		$this->icon_url = torro()->asset_url( 'icon-textarea', 'png' );
	}

	public function input_html() {
		$html  = '<label for="' . $this->get_input_name() . '">' . esc_html( $this->label ) . '</label>';

		$html .= '<textarea name="' . $this->get_input_name() . '" maxlength="' . $this->settings[ 'max_length' ] . '" rows="' . $this->settings[ 'rows' ] . '" cols="' . $this->settings[ 'cols' ] . '">' . esc_html( $this->response ) . '</textarea>';

		if ( ! empty( $this->settings['description'] ) ) {
			$html .= '<small>';
			$html .= esc_html( $this->settings['description'] );
			$html .= '</small>';
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
				'default'		=> '1000'
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
		);
	}

	public function validate( $input ) {
		$min_length = $this->settings[ 'min_length' ];
		$max_length = $this->settings[ 'max_length' ];

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

		return ! $error;
	}

	public function after_element() {
		$html = '';

		if ( ! empty( $this->settings[ 'description' ] ) ) {
			$html = '<p class="form-element-description">';
			$html .= esc_html( $this->settings['description'] );
			$html .= '</p>';
		}

		return $html;
	}
}

torro()->form_elements()->add( 'Torro_Form_Element_Textarea' );
