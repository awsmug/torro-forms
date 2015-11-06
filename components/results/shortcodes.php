<?php
/**
 * Awesome Forms Chart Shortcodes
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Charts
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

if( !defined( 'ABSPATH' ) )
{
	exit;
}

class AF_ChartsShortCodes
{

	/**
	 * Adding Shortcodes and Actionhooks
	 */
	public static function init()
	{
		add_shortcode( 'survey_results', array( __CLASS__, 'survey_results' ) ); // @todo Delete later, because it's deprecated
		add_shortcode( 'form_results', array( __CLASS__, 'form_results' ) );

		add_shortcode( 'question_results', array( __CLASS__, 'element_results' ) ); // @todo Delete later, because it's deprecated
		add_shortcode( 'element_results', array( __CLASS__, 'element_results' ) );

		add_action( 'af_result_charts_postbox_bottom', array( __CLASS__, 'show_form_result_shortcode' ) );
		add_action( 'af_result_charts_postbox_element', array( __CLASS__, 'show_element_result_shortcode' ) );
	}

	/**
	 * Showing all results of a form
	 *
	 * @param $atts
	 *
	 * @return string|void
	 */
	public static function survey_results( $atts )
	{
		_deprecated_function( 'Shortcode [survey_results]', '1.0.0 beta 20', '[form_results]' );

		return self::form_results( $atts );
	}

	/**
	 * Showing all results of a form
	 *
	 * @param $atts
	 *
	 * @return string|void
	 */
	public static function form_results( $atts )
	{
		$atts = shortcode_atts( array( 'id' => '' ), $atts );
		$form_id = $atts[ 'id' ];

		if( '' == $form_id || !af_form_exists( $form_id ) )
		{
			return esc_attr( 'Please enter a valid form id into the shortcode!', 'af-locale' );
		}

		$results = new AF_Form_Results( $form_id );

		/*
		$ordered_data = AF_AbstractData::order_for_charting( $results->get_results() );

		$html = '';

		$count_bars = 0;
		foreach( $ordered_data[ 'questions' ] as $element_id => $question ):
			if( !array_key_exists( $element_id, $ordered_data[ 'data' ] ) )
			{
				continue;
			}

			$html .= AF_Chart_Creator_Dimple::show_bars( $question, $ordered_data[ 'data' ][ $element_id ] );
			$count_bars++;
		endforeach;

		if( 0 == $count_bars )
		{
			$html .= esc_attr( 'There are no results to show.', 'af-locale' );
		}

		return $html;
		*/
	}

	/**
	 * Showing all results of a form
	 *
	 * @param $atts
	 *
	 * @return string|void
	 */
	public static function question_results( $atts )
	{
		_deprecated_function( 'Shortcode [question_results]', '1.0.0 beta 20', '[element_results]' );

		return self::element_results( $atts );
	}

	/**
	 * Showing results of an Element
	 *
	 * @param array $atts Arguments which can be added to the shortcode
	 *
	 * @return string $html HTML of results
	 */
	public static function element_results( $atts )
	{
		global $wpdb, $af_global;

		$atts = shortcode_atts( array( 'id' => '', ), $atts );
		$element_id = $atts[ 'id' ];

		$sql = $wpdb->prepare( "SELECT id FROM {$af_global->tables->elements} WHERE id = %d", $element_id );
		$element_id = $wpdb->get_var( $sql );

		if( '' == $element_id )
		{
			return esc_attr( 'Please enter a valid element id into the shortcode!', 'af-locale' );
		}

		$sql = $wpdb->prepare( "SELECT form_id FROM {$af_global->tables->elements} WHERE id = %d", $element_id );
		$form_id = $wpdb->get_var( $sql );

		$results = new AF_Form_Results( $form_id );

		$html = '';

		/*
		foreach( $ordered_data[ 'questions' ] as $element_id => $question )
		{
			$html .= AF_Chart_Creator_Dimple::show_bars( $question, $ordered_data[ 'data' ][ $element_id ] );
		}*/

		return $html;
	}

	/**
	 * Showing Form result Shortcodes in Admin for copy&paste
	 *
	 * @param int $form_id Id of the form
	 *
	 * @return string $html HTML for shortcode summary in admon
	 */
	public static function show_form_result_shortcode()
	{
		global $post;

		if( !af_is_formbuilder() )
		{
			return;
		}

		$html  = '<div class="in-postbox-one-third">' . af_clipboard_field( __( 'Charts Shortcode', 'af-locale' ), '[form_results id=' . $post->ID . ']' ) . '</div>';

		echo $html;
	}

	/**
	 * Showing Element result schortcodes in admin area for copy&paste
	 *
	 * @param object $object Object element
	 *
	 * @return string $html HTML for shortcode summary in admin
	 */
	public static function show_element_result_shortcode( $object )
	{
		if( $object->id != '' && $object->is_analyzable )
		{
			echo af_clipboard_field( __( 'Element Charts Shortcode', 'af-locale' ), '[element_results id=' .  $object->id . ']' );
		}
	}
}

AF_ChartsShortCodes::init();