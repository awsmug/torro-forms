<?php
/**
 * Multiple Choice Form Element
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

final class Torro_Form_Element_Multiplechoice extends Torro_Form_Element {
	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->type = $this->name = 'multiplechoice';
		$this->title = __( 'Multiple Choice', 'torro-forms' );
		$this->description = __( 'Add an Element which can be answered by selecting one ore more given answers.', 'torro-forms' );
		$this->icon_url = torro()->get_asset_url( 'icon-multiplechoice', 'png' );

		$this->input_answers = true;
		$this->answer_array = true;
		$this->input_answers = true;
	}

	public function input_html() {
		$html = '<label for="' . $this->get_input_name() . '">' . esc_html( $this->label ) . '</label>';

		foreach ( $this->answers as $answer ) {
			$checked = '';

			if ( is_array( $this->response ) && in_array( $answer->label, $this->response, true ) ) {
				$checked = ' checked="checked"';
			}

			$html .= '<div class="torro_element_checkbox"><input type="checkbox" name="' . $this->get_input_name() . '[]" value="' . esc_attr( $answer->label ) . '" ' . $checked . ' /> ' . esc_html( $answer->label ) . '</div>';
		}

		if ( ! empty( $this->settings['description']->value ) ) {
			$html .= '<small>';
			$html .= esc_html( $this->settings['description']->value );
			$html .= '</small>';
		}

		return $html;
	}

	public function settings_fields() {
		$this->settings_fields = array(
			'description'	=> array(
				'title'			=> __( 'Description', 'torro-forms' ),
				'type'			=> 'textarea',
				'description'	=> __( 'The description will be shown after the question.', 'torro-forms' ),
				'default'		=> ''
			),
			'min_answers'	=> array(
				'title'			=> __( 'Minimum Answers', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'The minimum number of answers which have to be choosed.', 'torro-forms' ),
				'default'		=> '1'
			),
			'max_answers'	=> array(
				'title'			=> __( 'Maximum Answers', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'The maximum number of answers which can be choosed.', 'torro-forms' ),
				'default'		=> '3'
			),
		);
	}

	public function validate( $input ) {
		$min_answers = $this->settings['min_answers']->value;
		$max_answers = $this->settings['max_answers']->value;

		$input = (array) $input;

		$input = array_map( 'stripslashes', $input );

		if ( ! empty( $min_answers ) ) {
			if ( ! is_array( $input ) || count( $input ) < $min_answers ) {
				return new Torro_Error( 'not_enough_choices', __( 'Not enough choices.', 'torro-forms' ) );
			}
		}

		if ( ! empty( $max_answers ) ) {
			if ( is_array( $input ) && count( $input ) > $max_answers ) {
				return new Torro_Error( 'too_many_choices', __( 'Too many choices.', 'torro-forms' ) );
			}
		}

		return $input;
	}
}

torro()->elements()->register( 'Torro_Form_Element_Multiplechoice' );
