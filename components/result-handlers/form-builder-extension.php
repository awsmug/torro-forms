<?php
/**
 * Awesome Forms Charts Form Builder Extension
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Core
 * @version 2015-04-16
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

class AF_FormBuilder_ChartsExtension
{
	/**
	 * Init in WordPress, run on constructor
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public static function init()
	{
		if( !is_admin() )
		{
			return NULL;
		}

		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ), 15 );
		add_action( 'af_save_form', array( __CLASS__, 'save' ), 10, 1 );
		// add_action( 'admin_print_styles', array( __CLASS__, 'register_admin_styles' ) );
	}

	/**
	 * Adding meta boxes
	 *
	 * @param string $post_type Actual post type
	 *
	 * @since 1.0.0
	 */
	public static function meta_boxes( $post_type )
	{
		$post_types = array( 'questions' );

		if( in_array( $post_type, $post_types ) ):
			add_meta_box(
				'form-charts',
				esc_attr__( 'Charts', 'af-locale' ),
				array( __CLASS__, 'meta_box_charts' ),
				'questions',
				'normal',
				'high' );
		endif;
	}

	/**
	 * Form Restrictions box
	 *
	 * @since 1.0.0
	 */
	public static function meta_box_charts()
	{
		global $post;

		$form_id = $post->ID;

		global $post;

		$form_id = $post->ID;
		$show_results = get_post_meta( $form_id, 'show_results', TRUE );

		if( '' == $show_results )
		{
			$show_results = 'no';
		}

		$checked_no = '';
		$checked_yes = '';

		if( 'no' == $show_results )
		{
			$checked_no = ' checked="checked"';
		}
		else
		{
			$checked_yes = ' checked="checked"';
		}

		$html = '<div class="form-options">';
		$html .= '<p><label for="show_results">' . esc_attr__( 'Show results after finishing form:', 'af-locale' ) . '</label></p>';
		$html .= '<input type="radio" name="show_results" value="yes"' . $checked_yes . '>' . esc_attr__( 'Yes' ) . ' ';
		$html .= '<input type="radio" name="show_results" value="no"' . $checked_no . '>' . esc_attr__( 'No' ) . '<br>';
		$html .= '</div>';

		$html .= do_shortcode( '[form_results id="' . $form_id . '"]' );

		echo $html;
	}

	/**
	 * Saving data
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public static function save( $form_id )
	{
		/**
		 * Saving restriction options
		 */
		$restrictions_option = $_POST[ 'form_restrictions_option' ];
		update_post_meta( $form_id, 'restrictions_option', $restrictions_option );
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public static function register_admin_styles()
	{
		if( !af_is_formbuilder() )
		{
			return;
		}

		wp_enqueue_style( 'form-restrictions-form-builder-extension-styles', AF_URLPATH . '/components/restrictions/includes/css/form-builder-extension.css' );
	}
}

AF_FormBuilder_ChartsExtension::init();