<?php
/**
 * Question Form Builder Restrictions extension
 *
 * This class adds question post type functions.
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

if( !defined( 'ABSPATH' ) ){
	exit;
}

class AF_FormBuilder_RestrictionsExtension
{
	/**
	 * Init in WordPress, run on constructor
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public static function init()
	{
		if( !is_admin() ){
			return NULL;
		}

		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ), 15 );
		add_action( 'questions_save_form', array( __CLASS__, 'save' ), 10, 1 );

		add_action( 'admin_print_styles', array( __CLASS__, 'register_admin_styles' ) );
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
				'form-restrictions',
				esc_attr__( 'Restrictions', 'af-locale' ),
				array( __CLASS__, 'meta_box_restrictions' ),
				'questions',
				'normal',
				'low' );
		endif;
	}

	/**
	 * Form Restrictions box
	 *
	 * @since 1.0.0
	 */
	public static function meta_box_restrictions()
	{
		global $wpdb, $post, $af_global;

		$form_id = $post->ID;
		$restrictions = $af_global->restrictions;

		if( !is_array( $restrictions ) || count( $restrictions ) == 0 ){
			return;
		}

		/**
		 * Select field for Restriction
		 */
		$restrictions_option = get_post_meta( $form_id, 'restrictions_option', TRUE );

		if( '' == $restrictions_option ): // If there was selected nothing before
			$restrictions_option = 'allvisitors';
		endif;

		ob_start();
		do_action( 'form_restrictions_content_top' );
		$html = ob_get_clean();

		$html .= '<div id="questions-restrictions-options">';
		$html .= '<label for"form_restrictions_option">' . esc_attr( 'Who has access to this form?', 'af-locale' ) . '';
		$html .= '<select name="form_restrictions_option" id="questions-restrictions-option">';
		foreach( $restrictions AS $name => $restriction ){
			if( !$restriction->has_option() ){
				continue;
			}
			$selected = '';
			if( $name == $restrictions_option ){
				$selected = ' selected="selected"';
			}
			$html .= '<option value="' . $name . '"' . $selected . '>' . $restriction->option_name . '</option>';
		}
		$html .= '</select></label>';
		$html .= '</div>';

		/**
		 * Option content
		 */
		foreach( $restrictions AS $name => $restriction ){
			$option_content = $restriction->option_content();
			if( !$restriction->has_option() || !$option_content ){
				continue;
			}
			$html .= '<div id="questions-restrictions-content-' . $restriction->name . '" class="questions-restrictions-content questions-restrictions-content-' . $restriction->name . '">' . $option_content . '</div>';
		}

		ob_start();
		do_action( 'form_restrictions_content_bottom' );
		$html .= ob_get_clean();

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
		if( !af_is_questions_formbuilder() )
			return;

		wp_enqueue_style( 'questions-restrictions-form-builder-extension-styles', QUESTIONS_URLPATH . '/components/restrictions/includes/css/form-builder-extension.css' );
	}
}
AF_FormBuilder_RestrictionsExtension::init();