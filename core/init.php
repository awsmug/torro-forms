<?php
/**
 * Core Component
 *
 * This class initializes the core component.
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

class AF_Core
{
	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	public static function init()
	{
		self::includes();

		add_action( 'init', array( __CLASS__ , 'custom_post_types' ), 11 );
		add_filter( 'body_class', array( __CLASS__, 'add_body_class' ) );
		add_action( 'admin_print_styles', array( __CLASS__, 'register_admin_styles' ) );
		// add_settings_field( 'permalinks', 'Forms',  ); // @todo Add field to permalinks section
	}

	/**
	 * Creates Custom Post Types for Awesome Forms
	 *
	 * @since 1.0.0
	 */
	public static function custom_post_types()
	{
		/**
		 * Categories
		 */
		$args_taxonomy = array( 'show_in_nav_menus' => TRUE,
		                        'hierarchical'      => TRUE,
		                        'labels'            => array( 'name'              => _x( 'Categories', 'taxonomy general name', 'af-locale' ),
		                                                      'singular_name'     => _x( 'Category', 'taxonomy singular name', 'af-locale' ),
		                                                      'search_items'      => __( 'Search Categories', 'af-locale' ),
		                                                      'all_items'         => __( 'All Categories', 'af-locale' ),
		                                                      'parent_item'       => __( 'Parent Category', 'af-locale' ),
		                                                      'parent_item_colon' => __( 'Parent Category:', 'af-locale' ),
		                                                      'edit_item'         => __( 'Edit Category', 'af-locale' ),
		                                                      'update_item'       => __( 'Update Category', 'af-locale' ),
		                                                      'add_new_item'      => __( 'Add New Category', 'af-locale' ),
		                                                      'new_item_name'     => __( 'New Category', 'af-locale' ),
		                                                      'menu_name'         => __( 'Categories', 'af-locale' ), ),
		                        'show_ui'           => TRUE,
		                        'query_var'         => TRUE,
		                        'rewrite'           => TRUE, );

		register_taxonomy( 'questions-categories', array( 'questions' ), $args_taxonomy );

		/**
		 * Post Types
		 */
		$args_post_type = array( 'labels'            => array( 'name'               => __( 'Forms', 'af-locale' ),
		                                                       'singular_name'      => __( 'Form', 'af-locale' ),
		                                                       'all_items'          => __( 'All Forms', 'af-locale' ),
		                                                       'add_new_item'       => __( 'Add new Form', 'af-locale' ),
		                                                       'edit_item'          => __( 'Edit Form', 'af-locale' ),
		                                                       'new_item'           => __( 'Add new Form', 'af-locale' ),
		                                                       'view_item'          => __( 'View Form', 'af-locale' ),
		                                                       'search_items'       => __( 'Search Forms', 'af-locale' ),
		                                                       'not_found'          => __( 'No Form found', 'af-locale' ),
		                                                       'not_found_in_trash' => __( 'No Form found in trash', 'af-locale' ) ),
		                         'public'            => TRUE,
		                         'has_archive'       => TRUE,
		                         'supports'          => array( 'title' ),
		                         'show_in_menu'      => 'AF_Admin',
		                         'show_in_nav_menus' => FALSE,
		                         'rewrite'           => array( 'slug' => 'survey', 'with_front' => TRUE ) // @todo Change! Make variable! Doing in Permalink sections.

		);

		register_post_type( 'questions', $args_post_type );
	}

	/**
	 * Adding CSS Classes to body
	 *
	 * @param array $classes Classes for body
	 *
	 * @return array $classes Classes for body
	 */
	public static function add_body_class( $classes )
	{

		global $post;

		// Check if we are on the right place
		if( !is_object( $post ) || !property_exists( $post, 'post_type' ) || 'questions' != $post->post_type ){
			return $classes;
		}

		$classes[] = 'af-form';
		$classes[] = 'af-form-' . $post->ID;

		return $classes;
	}

	/**
	 * Including files of component
	 */
	public static function includes()
	{
		$core_folder = AF_FOLDER . 'core/';

		// Base classes
		include( $core_folder . 'global.php' ); // Global Awesome Forms object $af_global
		include( $core_folder . 'class-post.php' );

		// Abstract
		include( $core_folder . 'abstract/class-component.php' );
		include( $core_folder . 'abstract/class-element.php' );
		include( $core_folder . 'abstract/class-settings.php' );

		// Admin
		include( $core_folder . 'menu.php' );
		include( $core_folder . 'form-builder.php' );
		include( $core_folder . 'settings-page.php' );

		// Settings
		include( $core_folder . 'settings/class-settingshandler.php' );
		include( $core_folder . 'settings/base-settings/general.php' );

		// Form functions
		include( $core_folder . 'form.php' );
		include( $core_folder . 'form-loader.php' );
		include( $core_folder . 'form-process.php' );

		// Base elements
		include( $core_folder . 'elements/base-elements/text.php' );
		include( $core_folder . 'elements/base-elements/textarea.php' );
		include( $core_folder . 'elements/base-elements/onechoice.php' );
		include( $core_folder . 'elements/base-elements/multiplechoice.php' );
		include( $core_folder . 'elements/base-elements/dropdown.php' );
		include( $core_folder . 'elements/base-elements/separator.php' );
		include( $core_folder . 'elements/base-elements/splitter.php' );
		include( $core_folder . 'elements/base-elements/description.php' );

		// Template tags
		include( $core_folder . 'templatetags/class-templatetags.php' );
		include( $core_folder . 'templatetags/base-templatetags/global.php' );
		include( $core_folder . 'templatetags/base-templatetags/form.php' );

		// Shortcodes
		include( $core_folder . 'shortcodes.php' );
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 * @since 1.0.0
	 */
	public static function register_admin_styles()
	{
		wp_enqueue_style( 'af-admin-fonts', AF_URLPATH . 'core/includes/css/fonts.css' );
	}

}
AF_Core::init();