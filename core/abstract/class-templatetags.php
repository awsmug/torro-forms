<?php
/**
 * Handling templatetags
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Torro_TemplateTags {
	/**
	 * Name of templatetags collection
	 *
	 * @since 1.0.0
	 */
	var $name;

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
	var $initialized = false;

	/**
	 * Add a tag to taglist
	 *
	 * @param       $description
	 * @param       $callback
	 * @param array $args
	 */
	final function add_tag( $name, $display_name, $description, $callback, $args = array() ) {
		$this->tags[ $name ] = array(
			'description'	=> $description,
			'display_name'	=> $display_name,
			'callback'		=> $callback,
			'args'			=> $args,
		);
	}

	/**
	 * Function to register element in Torro Forms
	 *
	 * After registerung was successfull the new element will be shown in the elements list.
	 *
	 * @return boolean $is_registered Returns true if registering was succesfull, false if not
	 * @since 1.0.0
	 */
	public function _register() {
		global $torro_global;

		if ( true == $this->initialized ) {
			return false;
		}

		if ( ! is_object( $torro_global ) ) {
			return false;
		}

		if ( '' === $this->name ) {
			$this->name = get_class( $this );
		}

		if ( '' === $this->title ) {
			$this->title = ucwords( get_class( $this ) );
		}

		if ( '' === $this->description ) {
			$this->description = __( 'This is a Torro Forms Templatetag collection.', 'torro-forms' );
		}

		if ( array_key_exists( $this->name, $torro_global->restrictions ) ) {
			return false;
		}

		if ( ! is_array( $torro_global->templatetags ) ) {
			$torro_global->templatetags = array();
		}

		$this->tags(); // Getting Tags

		$this->initialized = true;

		return $torro_global->add_templatetags( $this->name, $this );
	}

	/**
	 * @return mixed
	 */
	abstract function tags();
}

/**
 * Register a new Templatetags collection
 *
 * @param $templatetags_class name of the templatetags collection
 *
 * @return bool|null Returns false on failure, otherwise null.
 */
function torro_register_templatetags( $templatetags_class ) {
	if ( class_exists( $templatetags_class ) ) {
		$templatetags = new $templatetags_class();

		return $templatetags->_register();
	}

	return false;
}

/**
 * Get all Templatetag collections
 *
 * @return array|bool
 */
function torro_get_templatetag_collections() {
	global $torro_global;

	if ( ! property_exists( $torro_global, 'templatetags' ) ) {
		return false;
	}

	if ( 0 === count( $torro_global->templatetags ) ) {
		return false;
	}

	$templatetag_collections = array();
	foreach ( $torro_global->templatetags as $templatetag_collection_name => $templatetag_collection ) {
		$templatetag_collections[ $templatetag_collection_name ] = new stdClass();
		$templatetag_collections[ $templatetag_collection_name ]->title = $templatetag_collection->title;
		$templatetag_collections[ $templatetag_collection_name ]->description = $templatetag_collection->description;
	}

	return $templatetag_collections;
}

/**
 * Getting all Templatetags of a collection
 *
 * @param $templatetag_collection
 */
function torro_get_templatetags( $templatetag_collection ) {
	global $torro_global;

	if ( ! property_exists( $torro_global, 'templatetags' ) ) {
		return false;
	}

	if ( 0 === count( $torro_global->templatetags ) ) {
		return false;
	}

	if ( ! array_key_exists( $templatetag_collection, $torro_global->templatetags ) ) {
		return false;
	}

	return $torro_global->templatetags[ $templatetag_collection ]->tags;
}

/**
 * Adds a Button for templatetags and binds it to an input field
 *
 * @return string
 */
function torro_template_tag_button( $input_name ) {
	$collections = torro_get_templatetag_collections();

	$html = '<div class="torro-templatetag-button">';
	$html .= '<input type="button" value="' . esc_attr__( '+', 'torro-forms' ) . '" class="button" rel="' . $input_name . '" />';
	$html .= '<div class="torro-templatetag-list">';

	foreach ( $collections as $collection_name => $collection ) {
		$html .= '<div class="torro-templatetag-collection">';
		$html .= '<div class="torro-templatetag-collection-headline">' . esc_html( $collection->title ) . '</div>';

		$template_tags = torro_get_templatetags( $collection_name );

		foreach ( $template_tags as $tag_name => $template_tag ) {
			$html .= '<div class="torro-templatetag" rel="' . $input_name . '" data-tagname="' . $tag_name . '">' . esc_html( $template_tag[ 'display_name' ] ) . '</div>';
		}
		$html .= '</div>';
	}
	$html .= '</div>';
	$html .= '</div>';

	return $html;
}

/**
 * Filtering templatetags from content
 *
 * @param $content
 *
 * @return mixed
 */
function torro_filter_templatetags( $content ) {
	global $torro_global;

	$collections = torro_get_templatetag_collections();

	foreach ( $collections as $collection_name => $collection ) {
		$template_tags = torro_get_templatetags( $collection_name );

		foreach ( $template_tags as $tag_name => $template_tag ) {
			$template_content = call_user_func_array( $template_tag[ 'callback' ], $template_tag[ 'args' ] );
			$content = str_replace( '{' . $tag_name . '}', $template_content, $content );
		}
	}

	return $content;
}
