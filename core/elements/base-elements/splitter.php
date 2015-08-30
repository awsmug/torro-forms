<?php
/**
 * Splitter Form Element
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
if( !defined( 'ABSPATH' ) ){
	exit;
}

class AF_FormElement_Splitter extends AF_FormElement
{

	public function __construct( $id = NULL )
	{
		$this->name = 'Splitter';
		$this->title = esc_attr__( 'Split Form', 'af-locale' );
		$this->description = esc_attr__( 'Splits a form into several steps', 'af-locale' );
		$this->icon_url = AF_URLPATH . '/assets/images/icon-split-form.png';

		$this->is_question = FALSE;
		$this->splits_form = TRUE;

		parent::__construct( $id );
	}
}

af_register_survey_element( 'AF_FormElement_Splitter' );






