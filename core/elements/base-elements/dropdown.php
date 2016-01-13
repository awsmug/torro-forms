<?php
/**
 * Dropdown Form Element
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

class Torro_Form_Element_Dropdown extends Torro_Form_Element {
	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct( $id = null ) {
		parent::__construct( $id );
	}

	protected function init() {
		$this->name = 'dropdown';
		$this->title = __( 'Dropdown', 'torro-forms' );
		$this->description = __( 'Add an Element which can be answered within a dropdown field.', 'torro-forms' );
		$this->icon_url = torro()->asset_url( 'icon-dropdown', 'png' );

		$this->has_answers = true;
		$this->answer_is_multiple = false;
		$this->is_analyzable = true;
	}

	public function input_html() {
		$html  = '<label for="' . $this->get_input_name() . '">' . esc_html( $this->label ) . '</label>';

		$html .= '<select name="' . $this->get_input_name() . '">';
		$html .= '<option value="please-select"> - ' . esc_html__( 'Please select', 'torro-forms' ) . ' -</option>';

		foreach ( $this->answers AS $answer ) {
			$checked = '';

			if ( $this->response === $answer['text'] ) {
				$checked = ' selected="selected"';
			}

			$html .= '<option value="' . esc_attr( $answer['text'] ) . '" ' . $checked . '/> ' . esc_html( $answer['text'] ) . '</option>';
		}

		$html .= '</select>';

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
		);
	}

	public function validate( $input ) {
		$error = false;

		if ( 'please-select' === $input ) {
			$this->validate_errors[] = __( 'Please select a value.', 'torro-forms' );
			$error = true;
		}

		return ! $error;
	}

}

torro()->elements()->add( 'Torro_Form_Element_Dropdown' );
