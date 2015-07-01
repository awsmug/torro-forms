<?php
/*
 * Core Component
 *
 * This class initializes the component.
 *
 * @author awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2

  Copyright 2015 awesome.ug (support@awesome.ug)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

if ( !defined( 'ABSPATH' ) ) exit;

class Questions_Core extends Questions_Component{
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->name = 'QuestionsCore';
		$this->title = __( 'Core', 'questions-locale' );
		$this->description = __( 'Core functions of the Questions Plugin', 'questions-locale' );
		$this->turn_off = FALSE;
		
		$this->slug = 'survey';
		
		add_action( 'init', array( $this, 'custom_post_types' ), 11 );
		
		parent::__construct();
		
	} // end constructor
	
	/**
	 * Creates Custom Post Types for surveys
	 * @since 1.0.0
	 */	
	public function custom_post_types(){
		/**
		 * Categories
		 */
		$args_taxonomy = array(
			'show_in_nav_menus' => TRUE,
		    'hierarchical' => TRUE,
		    'labels' => array(
				'name' => _x( 'Categories', 'taxonomy general name', 'questions-locale' ),
			    'singular_name' => _x( 'Category', 'taxonomy singular name', 'questions-locale' ),
			    'search_items' =>  __( 'Search Categories', 'questions-locale' ),
			    'all_items' => __( 'All Categories', 'questions-locale' ),
			    'parent_item' => __( 'Parent Category', 'questions-locale' ),
			    'parent_item_colon' => __( 'Parent Category:', 'questions-locale' ),
			    'edit_item' => __( 'Edit Category', 'questions-locale' ), 
			    'update_item' => __( 'Update Category', 'questions-locale' ),
			    'add_new_item' => __( 'Add New Category', 'questions-locale' ),
			    'new_item_name' => __( 'New Category', 'questions-locale' ),
			    'menu_name' => __( 'Categories', 'questions-locale' ),
			),
		    'show_ui' => TRUE,
		    'query_var' => TRUE,
		    'rewrite' => TRUE,
		);
		
		register_taxonomy( 'questions-categories', array( 'questions' ), $args_taxonomy );
		
		/**
		 * Post Types
		 */
		$args_post_type = array(
			'labels' => array(
				'name' => __( 'Surveys', 'questions-locale' ),
				'singular_name' => __( 'Survey', 'questions-locale' ),
				'all_items' => __( 'All Surveys', 'questions-locale' ),
				'add_new_item' => __( 'Add new Survey', 'questions-locale' ),
				'edit_item' => __( 'Edit Survey', 'questions-locale' ),
				'new_item' => __( 'Add new Survey', 'questions-locale' ),
				'view_item' => __( 'View Survey', 'questions-locale' ),
				'search_items' => __( 'Search Survey', 'questions-locale' ),
				'not_found' => __( 'No Survey available', 'questions-locale' ),
				'not_found_in_trash' => __( 'No Survey available', 'questions-locale' )
			),
			'public' => TRUE,
			'has_archive' => TRUE,
			'supports' => array( 'title' ),
			'show_in_menu'  => 'ComponentQuestionsAdmin',
			'show_in_nav_menus' => FALSE,
			'rewrite' => array(
	            'slug' => $this->slug,
	            'with_front' => TRUE
            )
				
		); 
		
		register_post_type( 'questions', $args_post_type );
	}

    /**
     * Including files of component
     */
	public function includes(){
        // Base classes
        include( QUESTIONS_COMPONENTFOLDER . '/core/class-post.php' );
        include( QUESTIONS_COMPONENTFOLDER . '/core/class-survey.php' );
        include( QUESTIONS_COMPONENTFOLDER . '/core/global-questions.php' ); // Global Questions object $questions_global

        // Functions
        include( QUESTIONS_COMPONENTFOLDER . '/core/survey.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/core/shortcodes.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/core/process-response.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/core/export.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/core/data-abstraction.php' );
	}
	
}
$Questions_Core = new Questions_Core();