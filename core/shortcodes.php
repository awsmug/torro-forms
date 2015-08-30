<?php
/**
 * Awesome Forms Core Shortcodes
 *
 * This should be used as parent class for Question-Answers.
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

if( !defined( 'ABSPATH' ) ){
	exit;
}

class AF_ShortCodes
{
	var $tables;
	var $components = array();
	var $question_types = array();

	/**
	 * Loading all shortcodes
	 */
	public static function init()
	{
		add_shortcode( 'survey', array( __CLASS__, 'form' ) ); // @todo: Delete later, because it's deprecated
		add_shortcode( 'form', array( __CLASS__, 'form' ) );

		add_action( 'edit_form_advanced', array( __CLASS__, 'show_form_shortcode' ), 15 );
	}

	public static function form( $atts )
	{
		global $questions_form_id;

		$atts = shortcode_atts( array( 'id' => '', 'title' => __( 'Survey', 'af-locale' ) ), $atts );
		$questions_form_id = $atts[ 'id' ];

		if( '' == $questions_form_id ){
			return esc_attr( 'Please enter an id in the form shortcode!', 'af-locale' );
		}

		if( !af_form_exists( $questions_form_id ) ){
			return esc_attr( 'Form not found. Please enter another ID in your shortcode.', 'af-locale' );
		}

		return AF_FormLoader::get_form( $questions_form_id );
	}

	public static function show_form_shortcode()
	{
		global $post;

		if( !af_is_questions_formbuilder() )
			return;

		$html = '<div class="questions-options shortcode">';
		$html .= '<label for="form_shortcode">' . __( 'Form Shortcode:', 'af-locale' ) . '</label><br />';
		$html .= '<input type="text" id="form_shortcode" value="[form id=' . $post->ID . ']" />';
		$html .= '</div>';

		echo $html;
	}
}

AF_ShortCodes::init();