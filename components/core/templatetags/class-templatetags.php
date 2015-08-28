<?php
/**
 * Handling templatetags
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

if( !defined( 'ABSPATH' ) ){
	exit;
}

abstract class Questions_TemplateTags{
	/**
	 * Slug of templatetags collection
	 *
	 * @since 1.0.0
	 */
	var $slug;

	/**
	 * Title of templatetags collection
	 *
	 * @since 1.0.0
	 */
	var $title;

	/**
	 * Description of templatetags collection
	 *
	 * @since 1.0.0
	 */
	var $description;

	/**
	 * Tags
	 *
	 * @since 1.0.0
	 */
	var $tags = array();

	/**
	 * Already initialized?
	 *
	 * @since 1.0.0
	 */
	var $initialized = FALSE;

	/**
	 * Add a tag to taglist
	 * @param       $description
	 * @param       $callback
	 * @param array $args
	 */
	final function add_tag( $name, $display_name, $description, $callback, $args = array() )
	{
		$this->tags[ $name ] = array( 'description' => $description, 'display_name' => $display_name, 'callback' => $callback, 'args' => $args );
	}

	/**
	 * @return mixed
	 */
	abstract function tags();

	/**
	 * Function to register element in Questions
	 *
	 * After registerung was successfull the new element will be shown in the elements list.
	 *
	 * @return boolean $is_registered Returns TRUE if registering was succesfull, FALSE if not
	 * @since 1.0.0
	 */
	public function _register()
	{
		global $questions_global;

		if( TRUE == $this->initialized ){
			return FALSE;
		}

		if( !is_object( $questions_global ) ){
			return FALSE;
		}

		if( '' == $this->slug ){
			$this->slug = get_class( $this );
		}

		if( '' == $this->title ){
			$this->title = ucwords( get_class( $this ) );
		}

		if( '' == $this->description ){
			$this->description = esc_attr__( 'This is a Questions Templatetag collection.', 'questions-locale' );
		}

		if( array_key_exists( $this->slug, $questions_global->restrictions ) ){
			return FALSE;
		}

		if( !is_array( $questions_global->templatetags ) ){
			$questions_global->templatetags = array();
		}

		$this->tags(); // Getting Tags

		$this->initialized = TRUE;

		return $questions_global->add_templatetags( $this->slug, $this );
	}
}

/**
 * Register a new Templatetags collection
 *
 * @param $element_type_class name of the templatetags collection
 *
 * @return bool|null Returns false on failure, otherwise null.
 */
function qu_register_templatetags( $restriction_class )
{

	if( !class_exists( $restriction_class ) ){
		return FALSE;
	}

	add_action( 'init', create_function( '', '$extension = new ' . $restriction_class . ';
			add_action( "init", array( &$extension, "_register" ), 2 ); ' ), 1 );
}

/**
 * Get all Templatetag collections
 * @return array|bool
 */
function qu_get_templatetag_collections()
{
	global $questions_global;

	if( !property_exists( $questions_global, 'templatetags' ) )
	{
		return FALSE;
	}

	if( count( $questions_global->templatetags ) == 0 )
	{
		return FALSE;
	}

	$templatetag_collections = array();
	foreach( $questions_global->templatetags AS $templatetag_collection_slug => $templatetag_collection )
	{
		$templatetag_collections[ $templatetag_collection_slug ] = new stdClass();
		$templatetag_collections[ $templatetag_collection_slug ]->title = $templatetag_collection->title;
		$templatetag_collections[ $templatetag_collection_slug ]->description = $templatetag_collection->description;
	}
	return $templatetag_collections;
}

/**
 * Getting all Templatetags of a collection
 * @param $templatetag_collection
 */
function qu_get_get_templatetags( $templatetag_collection )
{
	global $questions_global;

	if( !property_exists( $questions_global, 'templatetags' ) )
	{
		return FALSE;
	}

	if( count( $questions_global->templatetags ) == 0 )
	{
		return FALSE;
	}

	if( !array_key_exists( $templatetag_collection, $questions_global->templatetags ) )
	{
		return FALSE;
	}

	return $questions_global->templatetags[ $templatetag_collection ]->tags;
}

/**
 * Adds a Button for templatetags and binds it to an input field
 * @return string
 */
function qu_template_tag_button( $input_name ){
	$collections = qu_get_templatetag_collections();

	$html = '<div class="questions-templatetag-button">';
		$html.= '<input type="button" value="' . esc_attr( '+', 'questions-locale' ) . '" class="button" rel="' . $input_name . '" />';
		$html.= '<div class="questions-templatetag-list">';
		foreach( $collections AS $collection_slug => $collection )
		{
			$html.= '<div class="questions-templatetag-collection">';
			$html.= '<div class="questions-templatetag-collection-headline">' . $collection->title . '</div>';

			$template_tags = qu_get_get_templatetags( $collection_slug );

			foreach( $template_tags AS $tag_name => $template_tag )
			{
				// p( $template_tag );

				$html.= '<div class="questions-templatetag" rel="' . $input_name. '" data-tagname="' . $tag_name. '">' . $template_tag[ 'display_name' ] . '</div>';
			}
			$html.= '</div>';
		}
		$html.= '</div>';
	$html.= '</div>';

	return $html;
}