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

class Questions_Core extends Questions_Component
{
	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->name = 'QuestionsCore';
		$this->title = __( 'Core', 'questions-locale' );
		$this->description = __( 'Core functions of the Questions Plugin', 'questions-locale' );
		$this->turn_off = FALSE;

		$this->slug = 'survey';

		add_action( 'init', array( $this,
		                           'custom_post_types'
		), 11 );
		add_filter( 'body_class', array( $this,
		                                 'add_body_class'
		) );

		parent::__construct();
	} // end constructor

	/**
	 * Creates Custom Post Types for surveys
	 *
	 * @since 1.0.0
	 */
	public function custom_post_types()
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
		                                                      'menu_name'         => __( 'Categories', 'questions-locale' ),
		                        ),
		                        'show_ui'           => TRUE,
		                        'query_var'         => TRUE,
		                        'rewrite'           => TRUE,
		);

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
		                                                       'not_found_in_trash' => __( 'No Form found in trash', 'questions-locale' )
								 ),
		                         'public'            => TRUE,
		                         'has_archive'       => TRUE,
		                         'supports'          => array( 'title' ),
		                         'show_in_menu'      => 'QuestionsAdmin',
		                         'show_in_nav_menus' => FALSE,
		                         'rewrite'           => array( 'slug'       => $this->slug,
		                                                       'with_front' => TRUE
		                         )

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
	public function add_body_class( $classes )
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
	public function includes()
	{
		// Base classes
		include( QUESTIONS_COMPONENTFOLDER . '/core/global.php' ); // Global Questions object $questions_global
		include( QUESTIONS_COMPONENTFOLDER . '/core/class-post.php' );

		// Functions
		include( QUESTIONS_COMPONENTFOLDER . '/core/form.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/core/form-loader.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/core/form-process.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/core/form-restrictions.php' );

		include( QUESTIONS_COMPONENTFOLDER . '/core/responses.php' );

		include( QUESTIONS_COMPONENTFOLDER . '/core/data-abstraction.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/core/export.php' );

		include( QUESTIONS_COMPONENTFOLDER . '/core/shortcodes.php' );
	}

}

$Questions_Core = new Questions_Core();