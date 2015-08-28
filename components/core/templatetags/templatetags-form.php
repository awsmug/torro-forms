<?php

/**
 * Adds form Templatetags
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
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

class Questions_FormTemplateTags extends Questions_TemplateTags
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->title = __( 'Form', 'wcsc-locale' );
		$this->slug = 'formtags';
		$this->description = __( 'Form Templatetags', 'wcsc-locale' );
	}

	/**
	 * Adding all tags of class
	 */
	public function tags()
	{
		$this->add_tag( 'formtitle', esc_attr( 'Form Title', 'questions-locale' ), esc_attr( 'Adds the Form Title', 'questions-locale' ), array( __CLASS__ , 'formtitle' ) );
	}

	/**
	 * %sitename%
	 */
	public static function formtitle()
	{
		global $questions_form_id;

		$form_id = $questions_form_id;

	}

	/**
	 * Adding Element on the fly to taglist
	 * @param $element_id
	 * @param $element_name
	 */
	public function add_element( $element_id, $element_name ){
		$this->add_tag( $element_name . ':' . $element_id,
		                $element_name,
		                esc_attr( 'Adds the Element Content', 'questions-locale' ),
		                array( __CLASS__ , 'element_content' ),
		                array( 'element_id' => $element_id )
		);
	}

	/**
	 * Shows the Element content
	 * @param $element_id
	 */
	public static function element_content( $element_id ){
		global $questions_response_id, $questions_response;

		if( !isset( $questions_response[ $element_id ] ) )
			return;

		return $questions_response[ $element_id ];
	}
}
qu_register_templatetags( 'Questions_FormTemplateTags' );

function qu_add_element_templatetag( $element_id, $element_name ){
	global $questions_global;

	if( !property_exists( $questions_global, 'templatetags' ) )
	{
		return FALSE;
	}

	if( count( $questions_global->templatetags ) == 0 )
	{
		return FALSE;
	}

	if( !array_key_exists( 'formtags', $questions_global->templatetags ) )
	{
		return FALSE;
	}

	$questions_global->templatetags[ 'formtags' ]->add_element( $element_id, $element_name );
}