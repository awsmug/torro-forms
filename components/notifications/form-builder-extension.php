<?php
/**
 * Question Form Builder Notifications extension
 *
 * This class adds question post type functions.
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
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

class Questions_FormBuilder_NotificationsExtension
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
				'form-notifications',
				esc_attr__( 'Notifications', 'questions-locale' ),
				array( __CLASS__, 'meta_box_notifications' ),
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
	public static function meta_box_notifications()
	{
		// echo $html;
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
		$restrictions_option = $_POST[ 'questions_restrictions_option' ];
		update_post_meta( $form_id, 'restrictions_option', $restrictions_option );
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public static function register_admin_styles()
	{
		wp_enqueue_style( 'questions-notifications-form-builder-extension-styles', QUESTIONS_URLPATH . '/components/notifications/includes/css/form-builder-extension.css' );
	}
}

Questions_FormBuilder_NotificationsExtension::init();