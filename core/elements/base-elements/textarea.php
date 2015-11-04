<?php
/**
 * Textarea Form Element
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

class AF_FormElement_Textarea extends AF_FormElement
{

	public function init()
	{
		$this->name = 'Textarea';
		$this->title = esc_attr__( 'Textarea', 'af-locale' );
		$this->description = esc_attr__( 'Add an Element which can be answered within a text area.', 'af-locale' );
		$this->icon_url = AF_URLPATH . '/assets/images/icon-textarea.png';
	}

	public function input_html()
	{
		$html  = '<label for="' . $this->get_input_name() . '">' . $this->label . '</label>';

		$html .= '<textarea name="' . $this->get_input_name() . '" maxlength="' . $this->settings[ 'max_length' ] . '" rows="' . $this->settings[ 'rows' ] . '" cols="' . $this->settings[ 'cols' ] . '">' . $this->response . '</textarea>';

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
				'description' => esc_attr__( 'The description will be shown after the field.', 'af-locale' ),
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
				'default'     => '1000'
			),
			'rows'        => array(
				'title'       => esc_attr__( 'Rows', 'af-locale' ),
				'type'        => 'text',
				'description' => esc_attr__( 'Number of rows for typing in  (can be overwritten by CSS).', 'af-locale' ),
				'default'     => '10'
			),
			'cols'        => array(
				'title'       => esc_attr__( 'Columns', 'af-locale' ),
				'type'        => 'text',
				'description' => esc_attr__( 'Number of columns for typing in (can be overwritten by CSS).', 'af-locale' ),
				'default'     => '75'
			),
		);
	}

	public function validate( $input )
	{
		$min_length = $this->settings[ 'min_length' ];
		$max_length = $this->settings[ 'max_length' ];

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

		if( $error ):
			return FALSE;
		endif;

		return TRUE;
	}

	public function after_element()
	{
		$html = '';

		if( !empty( $this->settings[ 'description' ] ) ):
			$html = '<p class="form-element-description">';
			$html .= $this->settings[ 'description' ];
			$html .= '</p>';
		endif;

		return $html;
	}
}

af_register_form_element( 'AF_FormElement_Textarea' );