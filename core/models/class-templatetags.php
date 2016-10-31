<?php
/**
 * Core: Torro_TemplateTags class
 *
 * @package TorroForms
 * @subpackage CoreModels
 * @version 1.0.0-beta.7
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Template tags Class
 *
 * Handles template tags.
 *
 * @since 1.0.0-beta.1
 */
abstract class Torro_TemplateTags extends Torro_Base {
	/**
	 * Tags
	 *
	 * @since 1.0.0
	 */
	protected $tags = array();

	/**
	 * Add a tag to taglist
	 *
	 * @param       $description
	 * @param       $callback
	 * @param array $args
	 */
	final public function add_tag( $name, $display_name, $description, $callback, $args = array() ) {
		$this->tags[ $name ] = array(
			'description'	=> $description,
			'display_name'	=> $display_name,
			'callback'		=> $callback,
			'args'			=> $args,
		);
	}

	/**
	 * @return mixed
	 */
	public abstract function tags();
}

/**
 * Get all Templatetag collections
 *
 * @return array|bool
 */
function torro_get_templatetag_collections() {
	$templatetags = torro()->templatetags()->get_all_registered();
	if ( 0 === count( $templatetags ) ) {
		return false;
	}

	$templatetag_collections = array();
	foreach ( $templatetags as $templatetag_collection_name => $templatetag_collection ) {
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
	$templatetags = torro()->templatetags()->get_registered( $templatetag_collection );
	if ( ! $templatetags ) {
		return false;
	}

	return $templatetags->tags;
}

/**
 * Adds a Button for templatetags and binds it to an input field
 *
 * @return string
 */
function torro_template_tag_button( $input_name ) {
	$collections = torro_get_templatetag_collections();

	$html = '<div class="torro-templatetag-button">';
	$html .= '<input type="button" value="' . esc_attr__( '+', 'torro-forms' ) . '" class="button" data-input-id="' . $input_name . '" />';
	$html .= '<div class="torro-templatetag-list">';

	foreach ( $collections as $collection_name => $collection ) {
		$html .= '<div class="torro-templatetag-collection">';
		$html .= '<div class="torro-templatetag-collection-headline">' . esc_html( $collection->title ) . '</div>';

		$template_tags = torro_get_templatetags( $collection_name );

		foreach ( $template_tags as $tag_name => $template_tag ) {
			$html .= '<div class="torro-templatetag" data-input-id="' . $input_name . '" data-tagname="' . $tag_name . '">' . esc_html( $template_tag[ 'display_name' ] ) . '</div>';
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
