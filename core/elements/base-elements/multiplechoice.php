<?php
/**
 * Multiple Choice Form Element
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

// No direct access is allowed
if( !defined( 'ABSPATH' ) )
{
	exit;
}

class Torro_Form_Element_MultipleChoice extends Torro_Form_Element
{

	public function init()
	{
		$this->name = 'MultipleChoice';
		$this->title = esc_attr__( 'Multiple Choice', 'torro-forms' );
		$this->description = esc_attr__( 'Add an Element which can be answered by selecting one ore more given answers.', 'torro-forms' );
		$this->icon_url = TORRO_URLPATH . 'assets/img/icon-multiplechoice.png';

		$this->has_answers = TRUE;
		$this->answer_is_multiple = TRUE;
		$this->is_analyzable = TRUE;
	}

	public function input_html()
	{
		$html = '<label for="' . $this->get_input_name() . '">' . $this->label . '</label>';

		foreach( $this->answers AS $answer )
		{
			$checked = '';

			if( is_array( $this->response ) && in_array( $answer[ 'text' ], $this->response ) )
			{
				$checked = ' checked="checked"';
			}

			$html .= '<div class="torro_element_checkbox"><input type="checkbox" name="' . $this->get_input_name() . '[]" value="' . $answer[ 'text' ] . '" ' . $checked . ' /> ' . $answer[ 'text' ] . '</div>';
		}

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
				'title'       => esc_attr__( 'Description', 'torro-forms' ),
				'type'        => 'textarea',
				'description' => esc_attr__( 'The description will be shown after the question.', 'torro-forms' ),
				'default'     => ''
			),
			'min_answers' => array(
				'title'       => esc_attr__( 'Minimum Answers', 'torro-forms' ),
				'type'        => 'text',
				'description' => esc_attr__( 'The minimum number of answers which have to be choosed.', 'torro-forms' ),
				'default'     => '1'
			),
			'max_answers' => array(
				'title'       => esc_attr__( 'Maximum Answers', 'torro-forms' ),
				'type'        => 'text',
				'description' => esc_attr__( 'The maximum number of answers which can be choosed.', 'torro-forms' ),
				'default'     => '3'
			),
		);
	}

	public function validate( $input )
	{

		$min_answers = $this->settings[ 'min_answers' ];
		$max_answers = $this->settings[ 'max_answers' ];

		$error = FALSE;

		if( !empty( $min_answers ) )
		{
			if( !is_array( $input ) || count( $input ) < $min_answers ):
				$this->validate_errors[] = esc_attr__( 'Too less choices.', 'torro-forms' ) . ' ' . sprintf( esc_attr__( 'You have to choose between %d and %d answers.', 'torro-forms' ), $min_answers, $max_answers );
				$error = TRUE;
			endif;
		}

		if( !empty( $max_answers ) )
		{
			if( is_array( $input ) && count( $input ) > $max_answers ):
				$this->validate_errors[] = esc_attr__( 'Too many choices.', 'torro-forms' ) . ' ' . sprintf( esc_attr__( 'You have to choose between %d and %d answers.', 'torro-forms' ), $min_answers, $max_answers );
				$error = TRUE;
			endif;
		}

		if( $error ):
			return FALSE;
		endif;

		return TRUE;
	}
}

torro_register_form_element( 'Torro_Form_Element_MultipleChoice' );
