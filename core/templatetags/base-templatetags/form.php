<?php

/**
 * Adds form Templatetags
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Core
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

class AF_FormTemplateTags extends AF_TemplateTags
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->title = __( 'Form', 'af-locale' );
		$this->name = 'formtags';
		$this->description = __( 'Form Templatetags', 'af-locale' );
	}

	/**
	 * Adding all tags of class
	 */
	public function tags()
	{
		$this->add_tag( 'formtitle', esc_attr( 'Form Title', 'af-locale' ), esc_attr( 'Shows the Form Title', 'af-locale' ), array( __CLASS__ , 'formtitle' ) );
		$this->add_tag( 'allelements', esc_attr( 'All Elements', 'af-locale' ), esc_attr( 'Shows all Answers', 'af-locale' ), array( __CLASS__ , 'allelements' ) );
	}

	/**
	 * %sitename%
	 */
	public static function formtitle()
	{
		global $questions_form_id;

		$form = new AF_Form( $questions_form_id );
		return $form->title;
	}

	/**
	 * Adding Element on the fly to taglist
	 * @param $element_id
	 * @param $element_name
	 */
	public function add_element( $element_id, $element_name ){
		$this->add_tag( $element_name . ':' . $element_id,
		                $element_name,
		                esc_attr( 'Adds the Element Content', 'af-locale' ),
		                array( __CLASS__ , 'element_content' ),
		                array( 'element_id' => $element_id )
		);
	}

	/**
	 * Shows the Element content
	 * @param $element_id
	 */
	public static function element_content( $element_id )
	{
		global $questions_response;

		if( !isset( $questions_response[ $element_id ] ) )
			return;

		$element = af_get_element( $element_id );

		/**
		 * Displaying elements
		 */
		if( count( $element->sections ) > 0 )
		{
			/**
			 * Elements with sections
			 */
			// @todo Checking if element had sections and giving them HTML > Try with Matrix

		}elseif( is_array( $questions_response[ $element_id ] ) )
		{
			/**
			 * Elements with multiple answers
			 */
			$html = '<ul>';
			foreach( $questions_response[ $element_id ] AS $response ){
				$html.= '<li>' . $response . '</li>';
			}
			$html.= '</ul>';

			return $html;
		}else
		{
			/**
			 * Elements with string response value
			 */
			return $questions_response[ $element_id ];
		}
	}

	/**
	 * Shows the Element content
	 * @param $element_id
	 */
	public static function allelements(){
		global $questions_form_id, $questions_response;

		$form = new AF_Form( $questions_form_id );

		$html = '<table style="width:100%;">';
		foreach( $form->get_elements() AS $element ){
			$html.= '<tr>';
				$html.= '<td>' . $element->question . '</td>';
				$html.= '<td>' . self::element_content( $element->id ) . '</td>';
			$html.= '</tr>';
		}
		$html.= '</table>';

		return $html;
	}
}
af_register_templatetags( 'AF_FormTemplateTags' );

/**
 * Live registering element templatetags
 * @param $element_id
 * @param $element_name
 *
 * @return bool
 */
function af_add_element_templatetag( $element_id, $element_name ){
	global $af_global;

	if( !property_exists( $af_global, 'templatetags' ) )
	{
		return FALSE;
	}

	if( count( $af_global->templatetags ) == 0 )
	{
		return FALSE;
	}

	if( !array_key_exists( 'formtags', $af_global->templatetags ) )
	{
		return FALSE;
	}

	$af_global->templatetags[ 'formtags' ]->add_element( $element_id, $element_name );
}