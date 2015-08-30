<?php
/**
 * Description Form Element
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

class AF_FormElement_Description extends AF_FormElement
{

	public function __construct( $id = NULL )
	{

		$this->name = 'Description';
		$this->title = esc_attr__( 'Description', 'af-locale' );
		$this->description = esc_attr__( 'Adds a text to the form.', 'af-locale' );
		$this->icon_url = AF_URLPATH . '/assets/images/icon-text.png';

		$this->is_question = FALSE;

		parent::__construct( $id );
	}

	public function input_html()
	{
	}

	public function settings_fields()
	{

		$this->settings_fields = array(
			'description' => array(
				'title'       => esc_attr__( 'Text to show', 'af-locale' ),
				'type'        => 'textarea',
				'description' => esc_attr__( 'The text which will be shown in the form.', 'af-locale' ),
				'default'     => '' ) );
	}
}

af_register_survey_element( 'AF_FormElement_Description' );
