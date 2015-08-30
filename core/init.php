<?php
/**
 * Core Component
 *
 * This class initializes the core component.
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
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

class Questions_Core
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
	}

	/**
	 * Creates Custom Post Types for surveys
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
		                        'labels'            => array( 'name'              => _x( 'Categories', 'taxonomy general name', 'questions-locale' ),
		                                                      'singular_name'     => _x( 'Category', 'taxonomy singular name', 'questions-locale' ),
		                                                      'search_items'      => __( 'Search Categories', 'questions-locale' ),
		                                                      'all_items'         => __( 'All Categories', 'questions-locale' ),
		                                                      'parent_item'       => __( 'Parent Category', 'questions-locale' ),
		                                                      'parent_item_colon' => __( 'Parent Category:', 'questions-locale' ),
		                                                      'edit_item'         => __( 'Edit Category', 'questions-locale' ),
		                                                      'update_item'       => __( 'Update Category', 'questions-locale' ),
		                                                      'add_new_item'      => __( 'Add New Category', 'questions-locale' ),
		                                                      'new_item_name'     => __( 'New Category', 'questions-locale' ),
		                                                      'menu_name'         => __( 'Categories', 'questions-locale' ), ),
		                        'show_ui'           => TRUE,
		                        'query_var'         => TRUE,
		                        'rewrite'           => TRUE, );

		register_taxonomy( 'questions-categories', array( 'questions' ), $args_taxonomy );

		/**
		 * Post Types
		 */
		$args_post_type = array( 'labels'            => array( 'name'               => __( 'Forms', 'questions-locale' ),
		                                                       'singular_name'      => __( 'Form', 'questions-locale' ),
		                                                       'all_items'          => __( 'All Forms', 'questions-locale' ),
		                                                       'add_new_item'       => __( 'Add new Form', 'questions-locale' ),
		                                                       'edit_item'          => __( 'Edit Form', 'questions-locale' ),
		                                                       'new_item'           => __( 'Add new Form', 'questions-locale' ),
		                                                       'view_item'          => __( 'View Form', 'questions-locale' ),
		                                                       'search_items'       => __( 'Search Forms', 'questions-locale' ),
		                                                       'not_found'          => __( 'No Form found', 'questions-locale' ),
		                                                       'not_found_in_trash' => __( 'No Form found in trash', 'questions-locale' ) ),
		                         'public'            => TRUE,
		                         'has_archive'       => TRUE,
		                         'supports'          => array( 'title' ),
		                         'show_in_menu'      => 'QuestionsAdmin',
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

		$classes[] = 'questions';
		$classes[] = 'question-' . $post->ID;

		return $classes;
	}

	/**
	 * Including files of component
	 */
	public static function includes()
	{
		$core_folder = QUESTIONS_FOLDER . 'core/';

		// Base classes
		include( $core_folder . 'global.php' ); // Global Questions object $questions_global
		include( $core_folder . 'class-post.php' );

		// Admin
		include( $core_folder . 'menu.php' );
		include( $core_folder . 'form-builder.php' );
		include( $core_folder . 'settings.php' );

		// Settings
		include( $core_folder . 'settings/class-settings.php' );
		include( $core_folder . 'settings/class-settings-handler.php' );
		include( $core_folder . 'settings/base-settings/general.php' );

		// Form functions
		include( $core_folder . 'form.php' );
		include( $core_folder . 'form-loader.php' );
		include( $core_folder . 'form-process.php' );
		include( $core_folder . 'responses.php' );

		// Elements
		include( $core_folder . 'elements/class-element.php' );

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

		// Helper functions
		include( $core_folder . 'data-abstraction.php' );
		include( $core_folder . 'export.php' );
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 * @since 1.0.0
	 */
	public static function register_admin_styles()
	{
		wp_enqueue_style( 'questions-admin-fonts', QUESTIONS_URLPATH . 'core/includes/css/fonts.css' );
	}

}
Questions_Core::init();