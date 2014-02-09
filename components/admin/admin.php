<?php
/*
 * ComponentName Core Class
 *
 * This class initializes the component.
 *
 * @author rheinschmiede.de, Author <kontakt@rheinschmiede.de>
 * @package PluginName/Admin
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2
 * 

  Copyright 2013 (kontakt@rheinschmiede.de)

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

class SurveyVal_Admin extends SurveyVal_Component{
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	function __construct() {
		$this->name = 'SurveyValAdmin';
		$this->title = __( 'Admin', 'surveyval-locale' );
		$this->description = __( 'Setting up SurveyVal in WordPress Admin.', 'surveyval-locale' );
		$this->required = TRUE;
		$this->capability = 'edit_posts';
		
	    // Functions in Admin
	    if( is_admin() ):
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'parent_file', array( $this, 'tax_menu_correction' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'edit_form_after_title', array( $this, 'droppable_area' ) );
			add_action( 'add_meta_boxes', array( $this, 'meta_boxes' ) );
			add_action( 'save_post', array( $this, 'save_survey' ), 50 );
		endif;
	} // end constructor
	
	/**
	 * Adds the Admin menu.
	 * @since 1.0.0
	 */	
	public function admin_menu(){
		add_menu_page( __( 'SurveyVal', 'surveyval-locale' ), __( 'SurveyVal', 'surveyval-locale' ), $this->capability, 'Component' . $this->name , array( $this, 'settings_page' ), '', 50 );
		add_submenu_page( 'Component' . $this->name, __( 'Categories', 'surveyval-locale' ), __( 'Categories', 'surveyval-locale' ), $this->capability, 'edit-tags.php?taxonomy=surveyval-categories' );
		add_submenu_page( 'Component' . $this->name, __( 'Settings', 'surveyval-locale' ), __( 'Settings', 'surveyval-locale' ), $this->capability, 'Component' . $this->name, array( $this, 'settings_page' ) );
	}
	
	// highlight the proper top level menu
	public function tax_menu_correction( $parent_file ) {
		global $current_screen;
		$taxonomy = $current_screen->taxonomy;
			
		if ( $taxonomy == 'surveyval-categories' )
			$parent_file = 'Component' . $this->name;
		
		return $parent_file;
	}
	
	/**
	 * Content of the settings page.
	 * @since 1.0.0
	 */
	public function settings_page(){
		include( SURVEYVAL_COMPONENTFOLDER . '/admin/pages/settings.php' );
	}

	public function droppable_area(){
		global $post, $surveyval;
		
		if( !$this->is_surveyval_post_type() )
			return;
		
		echo '<div id="surveyval-content" class="drag-drop">';
				echo '<div id="drag-drop-area" class="widgets-holder-wrap">';
					
					$survey = new SurveyVal_Survey( $post->ID );
	
					foreach( $survey->questions AS $question ):
						echo  $this->get_widget_html( $question->question, $question->get_settings_html(), $question->icon, $question->id );
					endforeach;
					
					echo '<div class="drag-drop-inside">';
						echo '<p class="drag-drop-info">';
							echo __( 'Drop your Question/Answer here.', 'surveyval-locale' );
						echo '</p>';
					echo '</div>';
				echo '</div>';
		echo '</div>';
		echo '<div id="delete_question_dialog">' . __( 'Do you really want to delete this question?', 'surveyval-locale' ). '</div>';
		echo '<div id="delete_answer_dialog">' . __( 'Do you really want to delete this answer?', 'surveyval-locale' ). '</div>';
		echo '<input type="hidden" id="deleted_questions" name="surveyval_deleted_questions" value="">';
		echo '<input type="hidden" id="deleted_answers" name="surveyval_deleted_answers" value="">';
	}

	private function get_widget_html( $title, $content, $icon = '', $id = null, $new = FALSE ){
		if( $id != null && '' != $id )
			$id_name = ' id="widget_question_' . $id . '"';
		else
			$id_name = ' id="widget_question_##nr##"';
		
		$html = '<div class="widget question"' . $id_name . '>';
			$html.= '<div class="widget-top surveyval-admin-qu-text">';
				$html.= '<div class="widget-title-action"><a class="widget-action hide-if-no-js"></a></div>';
				$html.= '<div class="widget-title">';
					if( '' != $icon )
						$html.= '<img class="surveyval-widget-icon" src ="' . $icon . '" />';
					$html.= '<h4>' . $title . '</h4>';
				$html.= '</div>';
			$html.= '</div>';
			$html.= '<div class="widget-inside">';
				$html.= '<div class="widget-content">';
					$html.= $content;
				$html.= '</div>';
			$html.= '</div>';
		$html.= '</div>';
		
		return $html;
	}

	public function meta_box_questions_answers(){
		global $surveyval;
		
		foreach( $surveyval->question_types AS $question_type ):
			echo '<div class="surveyval-draggable">';
			echo $this->get_widget_html( $question_type->title, $question_type->get_settings_html( TRUE ), $question_type->icon );
			echo '</div>';
		endforeach;
	}
	
	public function meta_boxes( $post_type ){
		$post_types = array( 'surveyval' );
		
		if( in_array( $post_type, $post_types )):
			add_meta_box(
	            'surveyval-questions-answers',
	            __( 'Question/Answers', 'surveyval-locale' ),
	            array( $this, 'meta_box_questions_answers' ),
	            'surveyval',
	            'side',
	            'high'
	        );
		endif;
	}

	public function save_survey( $post_id ){
		if ( wp_is_post_revision( $post_id ) )
			return;
		
		if ( 'surveyval' != $_POST['post_type'] )
			return;
		
		sv_save_by_postdata( $post_id );
		
		// Preventing dublicate saving
		remove_action( 'save_post', array( $this, 'save_survey' ), 50 );
	}
	
	private function is_surveyval_post_type(){
		global $post;
		
		// If there is no post > stop adding scripts	
		if( !isset( $post ) )
			return FALSE;
		
		// If post type is wrong > stop adding scripts
		if( 'surveyval' != $post->post_type )
			return FALSE;
			
		return TRUE;
	}
	
	/**
	 * Enqueue admin scripts
	 * @since 1.0.0
	 */
	public function enqueue_scripts(){
		if( !$this->is_surveyval_post_type() )
			return;
		
		$translation_admin = array( 
			'delete' => __( 'Delete', 'surveyval-locale' ),
			'yes' => __( 'Yes', 'surveyval-locale' ),
			'no' => __( 'No', 'surveyval-locale' )
		);
		
		wp_enqueue_script( 'admin-surveyval-post-type', SURVEYVAL_URLPATH . '/components/admin/includes/js/admin-surveyval-post-type.js' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'admin-widgets' );
		wp_enqueue_script( 'wpdialogs-popup' );
		
    	wp_localize_script( 'admin-surveyval-post-type', 'translation_admin', $translation_admin );
		
		if ( wp_is_mobile() )
			wp_enqueue_script( 'jquery-touch-punch' );
	}
}

$SurveyVal_Admin = new SurveyVal_Admin();
