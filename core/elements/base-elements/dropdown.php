<?php
/**
 * Dropdown Form Element
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

class AF_FormElement_Dropdown extends AF_FormElement
{

	public function __construct( $id = NULL )
	{

		$this->name = 'Dropdown';
		$this->title = esc_attr__( 'Dropdown', 'af-locale' );
		$this->description = esc_attr__( 'Add an Element which can be answered within a dropdown field.', 'af-locale' );
		$this->icon_url = AF_URLPATH . '/assets/images/icon-dropdown.png';

		$this->has_answers = TRUE;
		$this->answer_is_multiple = FALSE;
		$this->is_analyzable = TRUE;

		parent::__construct( $id );
	}

	public function input_html()
	{
		$html  = '<label for="' . $this->get_input_name() . '">' . $this->label . '</label>';

		$html .= '<select name="' . $this->get_input_name() . '">';
		$html .= '<option value="please-select"> - ' . esc_attr__( 'Please select', 'af-locale' ) . ' -</option>';

		foreach( $this->answers AS $answer ):
			$checked = '';

			if( $this->response == $answer[ 'text' ] )
			{
				$checked = ' selected="selected"';
			}

			$html .= '<option value="' . $answer[ 'text' ] . '" ' . $checked . '/> ' . $answer[ 'text' ] . '</option>';
		endforeach;

		$html .= '</select>';

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
		);
	}

	public function validate( $input )
	{

		$error = FALSE;

		if( 'please-select' == $input ):
			$this->validate_errors[] = sprintf( esc_attr__( 'Please select a value.', 'af-locale' ) );
			$error = TRUE;
		endif;

		if( $error ):
			return FALSE;
		endif;

		return TRUE;
	}

}

af_register_form_element( 'AF_FormElement_Dropdown' );






