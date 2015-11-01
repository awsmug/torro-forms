<?php
/**
 * Awesome Forms Restrictions Component Form Builder Extension
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

class AF_FormBuilder_ResponseHandlerExtension
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

		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ) );
		add_action( 'af_save_form', array( __CLASS__, 'save' ) );

		add_action( 'admin_print_styles', array( __CLASS__, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );
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
		$post_types = array( 'af-forms' );

		if( in_array( $post_type, $post_types ) ):
			add_meta_box(
				'form-response-handlers',
				esc_attr__( 'Response Handling', 'af-locale' ),
				array( __CLASS__, 'meta_box_response_handlers' ),
				'af-forms',
				'normal',
				'high' );
		endif;
	}

	/**
	 * Response Handlers box
	 *
	 * @since 1.0.0
	 */
	public static function meta_box_response_handlers()
	{
		global $post, $af_global;

		$form_id = $post->ID;
		$response_handlers = $af_global->response_handlers;

		if( !is_array( $response_handlers ) || count( $response_handlers ) == 0 ){
			return;
		}

		$html = '<div id="form-response-handlers-tabs" class="section form_element_tabs">';

			$html.= '<ul>';
			foreach( $response_handlers AS $response_handler ){
				if( !$response_handler->has_option() ){
					continue;
				}
				$html .= '<li><a href="#' . $response_handler->name . '">' . $response_handler->title . '</a></option>';
			}
			$html .= '</ul>';

			$html .= '<div class="clear"></div>';

			foreach( $response_handlers AS $response_handler ){
				if( ! $response_handler->has_option() ){
					continue;
				}
				$html .= '<div id="' . $response_handler->name . '">' . $response_handler->option_content . '</div>';
			}

		$html .= '</div>';

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
		// $restrictions_option = $_POST[ 'form_restrictions_option' ];
		// update_post_meta( $form_id, 'restrictions_option', $restrictions_option );
	}

	/**
	 * Enqueue admin scripts
	 */
	public static function enqueue_admin_scripts()
	{
		if( !af_is_formbuilder() )
			return;

		wp_enqueue_script( 'af-response-handlers-form-builder-extension', AF_URLPATH . '/components/response-handlers/includes/js/form-builder-extension.js' );
	}

	/**
	 * Enqueue admin styles
	 */
	public static function enqueue_admin_styles()
	{
		if( !af_is_formbuilder() )
			return;

		wp_enqueue_style( 'af-response-handlers-form-builder-extension-styles', AF_URLPATH . '/components/response-handlers/includes/css/form-builder-extension.css' );
	}
}
AF_FormBuilder_ResponseHandlerExtension::init();