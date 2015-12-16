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

class AF_Formbuilder_ChartsExtension
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
		add_action( 'af_formbuilder_save', array( __CLASS__, 'save' ), 10, 1 );
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
		$post_types = array( 'af-forms' );

		if( in_array( $post_type, $post_types ) ):
			add_meta_box(
				'form-results',
				esc_attr__( 'Results', 'af-locale' ),
				array( __CLASS__, 'meta_box_results' ),
				'af-forms',
				'normal',
				'high' );
		endif;
	}

	/**
	 * Form Restrictions box
	 *
	 * @since 1.0.0
	 */
	public static function meta_box_results()
	{
		global $post, $af_global;

		$form_id = $post->ID;

		$form_results = new AF_Form_Results( $form_id );
		$results = $form_results->results();

		$result_handlers = $af_global->result_handlers;

		if( !is_array( $result_handlers ) || count( $result_handlers ) == 0 ){
			return;
		}

		$html = '<div id="form-result-handlers-tabs" class="section form_element_tabs">';

		$html.= '<ul>';
		foreach( $result_handlers AS $result_handler ){
			if( ! $result_handler->has_option() ){
				continue;
			}
			$html .= '<li><a href="#' . $result_handler->name . '">' . $result_handler->title . '</a></option>';
		}
		$html .= '</ul>';

		$html .= '<div class="clear"></div>';

		foreach( $result_handlers AS $result_handler ){
			if( ! $result_handler->has_option() ){
				continue;
			}
			$html .= '<div id="' . $result_handler->name . '">' . $result_handler->option_content . '</div>';
		}

		$html .= '</div>';

		$html .= '<div class="section general-settings">';

		$delete_results_disabled = ' disabled="disabled"';

		if( $form_results->count() > 0 )
		{
			$delete_results_disabled = '';
		}

		$html .= '<input' . $delete_results_disabled . ' id="form-delete-results" name="form-delete-results" type="button" class="button" value="' . esc_attr__( 'Delete Results', 'af-locale' ) . '" />';

		ob_start();
		do_action( 'af_results_general_settings' );
		$html .= ob_get_clean();

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

		wp_enqueue_style( 'af-results', AF_URLPATH . 'assets/css/results.css' );
	}
}

AF_Formbuilder_ChartsExtension::init();
