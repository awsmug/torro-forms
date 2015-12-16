<?php
/**
 * Separator Form Element
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

class AF_Form_Element_Separator extends AF_Form_Element
{

	public function init()
	{
		$this->name = 'separator';
		$this->title = esc_attr__( 'Separator', 'af-locale' );
		$this->description = esc_attr__( 'Adds a optical separator (<hr>) between elements.', 'af-locale' );
		$this->icon_url = AF_URLPATH . 'assets/img/icon-separator.png';

		$this->has_content = FALSE;
		$this->is_answerable = FALSE;
	}

	public function input_html()
	{
		$html = '<hr />';

		return $html;
	}
}

af_register_form_element( 'AF_Form_Element_Separator' );
