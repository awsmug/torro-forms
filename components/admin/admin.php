<?php
/*
 * Display Admin Class
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
		endif;
	} // end constructor
	
	/**
	 * Adds the Admin menu.
	 * @since 1.0.0
	 */	
	public function admin_menu(){
		add_menu_page( __( 'SurveyVal', 'surveyval-locale' ), __( 'SurveyVal', 'surveyval-locale' ), $this->capability, 'Component' . $this->name , array( $this, 'settings_page' ), '', 50 );
		add_submenu_page( 'Component' . $this->name, __( 'Add Survey', 'surveyval-locale' ), __( 'Add Survey', 'surveyval-locale' ), $this->capability, 'post-new.php?post_type=surveyval' );
		add_submenu_page( 'Component' . $this->name, __( 'Categories', 'surveyval-locale' ), __( 'Categories', 'surveyval-locale' ), $this->capability, 'edit-tags.php?taxonomy=surveyval-categories' );
		add_submenu_page( 'Component' . $this->name, __( 'Settings', 'surveyval-locale' ), __( 'Settings', 'surveyval-locale' ), $this->capability, 'Component' . $this->name, array( $this, 'settings_page' ) );
	}
	
	// Fix for getting correct menu and display
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
		global $post, $surveyval_global;
		
		if( !$this->is_surveyval_post_type() )
			return;
		
		$html = '<div id="surveyval-content" class="drag-drop">';
			$html.= '<div id="drag-drop-area" class="widgets-holder-wrap">';
				
				$survey = new SurveyVal_Survey( $post->ID );

				foreach( $survey->elements AS $element ):
					$html.=  $this->get_widget_html( $element );
				endforeach;
				
				$html.= '<div class="drag-drop-inside">';
					$html.= '<p class="drag-drop-info">';
						$html.= __( 'Drop your Element here.', 'surveyval-locale' );
					$html.= '</p>';
				$html.= '</div>';
			$html.= '</div>';
		$html.= '</div>';
		$html.= '<div id="delete_surveyelement_dialog">' . __( 'Do you really want to delete this element?', 'surveyval-locale' ). '</div>';
		$html.= '<div id="delete_answer_dialog">' . __( 'Do you really want to delete this answer?', 'surveyval-locale' ). '</div>';
		$html.= '<input type="hidden" id="deleted_surveyelements" name="surveyval_deleted_surveyelements" value="">';
		$html.= '<input type="hidden" id="deleted_answers" name="surveyval_deleted_answers" value="">';
		
		echo $html;
	}
	
	private function get_widget_html( $element, $new = FALSE ){
		$id = $element->id;
		$title = empty( $element->question ) ? $element->title : $element->question;
		$content = $this->get_settings_html( $element, $new );
		$icon = $element->icon;
		
		if( null != $id && '' != $id )
			$id_name = ' id="widget_surveyelement_' . $id . '"';
		else
			$id_name = ' id="widget_surveyelement_##nr##"';
			
		/*
		 * Widget
		 */
		$html = '<div class="widget surveyelement"' . $id_name . '>';
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
	
	public function get_settings_html( $element, $new = FALSE ){
		$id = $element->id;
		
		if( !$new )
			$widget_id = 'widget_surveyelement_' . $id;
		else
			$widget_id = 'widget_surveyelement_##nr##';
		
		$jquery_widget_id = str_replace( '#', '', $widget_id );
		
		
		/*
		 * Tab content
		 */
		$html ='<div class="survey_element_tabs">';
		
		$html.= '<ul class="tabs">';
			if( $element->is_question )
				$html.= '<li><a href="#tab_' . $jquery_widget_id . '_questions">' . __( 'Question', 'surveyval-locale' ) . '</a></li>';
			
			if( is_array( $element->settings_fields ) && count( $element->settings_fields ) > 0 ):
				$html.= '<li><a href="#tab_' . $jquery_widget_id . '_settings">' . __( 'Settings', 'surveyval-locale' ) . '</a></li>';
			endif;
		$html.= '</ul>';
		
		$html.= '<div class="clear tabs_underline"></div>';
		
		if( $element->is_question ):
			$html.= '<div id="tab_' . $jquery_widget_id . '_questions" class="tab_questions_content">';
				$html.= $this->get_admin_question_tab_html( $element, $widget_id, $new );
			$html.= '</div>'; 
		endif;
		
		if( is_array( $element->settings_fields ) && count( $element->settings_fields ) > 0 ):
			$html.= '<div id="tab_' . $jquery_widget_id . '_settings" class="tab_settings_content">';
				$html.= $this->get_admin_settings_tab_html( $element, $widget_id, $new );
			$html.= '</div>';
		endif;
		
		$bottom_buttons = apply_filters( 'sv_element_bottom_actions', array(
			'delete_survey_element' => array(
				'text' => __( 'Delete element', 'surveyval-locale' ),
				'classes' => 'delete_survey_element'
			)
		));
		
		$html.= '<ul class="survey-element-bottom">';
		foreach( $bottom_buttons AS $button ):
			$html.= '<li><a class="' . $button[ 'classes' ] . ' survey-element-bottom-action button">' . $button[ 'text' ] . '</a></li>';
		endforeach;
		$html.= '</ul>';
		
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][id]" value="' . $element->id . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][sort]" value="' . $element->sort . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][type]" value="' . $element->slug . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][preset_is_multiple]" value="' . ( $element->preset_is_multiple ? 'yes' : 'no' ) . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][preset_of_answers]" value="' . ( $element->preset_of_answers ? 'yes' : 'no' ) . '" />';
	
		$html.= '</div>'; 
		
		return $html;
	}

	private function get_admin_question_tab_html( $element, $widget_id, $new = FALSE ){
		$html = '<p><input type="text" name="surveyval[' . $widget_id . '][question]" value="' . $element->question . '" class="surveyval-question" /><p>';
	
		$i = 0;
		
		if( $element->preset_of_answers ):
			
			$html.= '<p>' . __( 'Answer/s:', 'surveyval-locale' ) . '</p>';
			
			if( is_array( $element->answers ) && !$new ):
				
				$html.= '<div class="answers">';
				
				foreach( $element->answers AS $answer ):
					$param_arr = array();
					$param_arr[] = $element->create_answer_syntax;
					
					foreach ( $element->create_answer_params AS $param ):
						
						switch( $param ){
							case 'name':
								$param_value = 'surveyval[' . $widget_id . '][answers][id_' . $answer['id'] . '][answer]';
								break;
								
							case 'value':
								$param_value = $value;
								break;
								
							case 'answer';
								$param_value = $answer['text'];
								break;
						}
						$param_arr[] = $param_value;
					endforeach;
					
					if( $element->preset_is_multiple )
						$answer_classes = ' preset_is_multiple';
					
					$html.= '<div class="answer' . $answer_classes .'" id="answer_' . $answer['id'] . '">';
					$html.= call_user_func_array( 'sprintf', $param_arr );
					$html.= ' <input type="button" value="' . __( 'Delete', 's
					urveyval-locale' ) . '" class="delete_answer button answer_action">';
					$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][answers][id_' . $answer['id'] . '][id]" value="' . $answer['id'] . '" />';
					$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][answers][id_' . $answer['id'] . '][sort]" value="' . $answer['sort'] . '" />';
					$html.= '</div>';
					
				endforeach;
				
				$html.= '</div><div class="clear"></div>';
				
			else:
				if( $element->preset_of_answers ):
					$param_arr[] = $element->create_answer_syntax;
					$temp_answer_id = 'id_' . time() * rand();
						
					foreach ( $element->create_answer_params AS $param ):
						switch( $param ){
							case 'name':
								$param_value = 'surveyval[' . $widget_id . '][answers][' . $temp_answer_id . '][answer]';
								break;
								
							case 'value':
								$param_value = '';
								break;
								
							case 'answer';
								$param_value = '';
								break;
						}
						$param_arr[] = $param_value;
					endforeach;
					
					if( $element->preset_is_multiple )
						$answer_classes = ' preset_is_multiple';
					
					$html.= '<div class="answers">';
					$html.= '<div class="answer ' . $answer_classes .'" id="answer_' . $temp_answer_id . '">';
					$html.= call_user_func_array( 'sprintf', $param_arr );
					$html.= ' <input type="button" value="' . __( 'Delete', 'surveyval-locale' ) . '" class="delete_answer button answer_action">';
					$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][answers][' . $temp_answer_id . '][id]" value="" />';
					$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][answers][' . $temp_answer_id . '][sort]" value="0" />';
					$html.= '</div>';
					$html.= '</div><div class="clear"></div>';
					
				endif;
				
			endif;
			
			if( $element->preset_is_multiple )
				$html.= '<a class="add-answer" rel="' . $widget_id . '">+ ' . __( 'Add Answer', 'surveyval-locale' ). ' </a>';
		
		endif;
		
		$html.= '<div class="clear"></div>';
		
		return $html;
	}

	private function get_admin_settings_tab_html( $element, $widget_id, $new ){
		$html = '';
		
		foreach( $element->settings_fields AS $name => $field ):
			$html.= $this->get_settings_field_html( $element, $name, $field, $widget_id );
		endforeach;
		
		return $html;
	}
	
	private function get_settings_field_html( $element, $name, $field, $widget_id ){
		global $wpdb, $surveyval_global;
		
		$sql = $wpdb->prepare( "SELECT value FROM {$surveyval_global->tables->settings} WHERE question_id = %d AND name = %s", $element->id, $name );
		$value = $wpdb->get_var( $sql );
		
		if( empty( $value ) )
			$value = $field['default'];
			
		
		$name = 'surveyval[' . $widget_id . '][settings][' . $name . ']';
		switch( $field['type'] ){
			case 'text':
				$input = '<input type="text" name="' . $name . '" value="' . $value . '" />';
				break;
		}
		
		$html = '<div class="settings-fieldset">';
		
			$html.= '<div class="settings-fieldset-title">';
				$html.= '<label for="' . $name . '">' . $field['title'] . '</label>';
			$html.= '</div>';
			
			$html.= '<div class="settings-fieldset-input">';
				$html.= $input . '<br />';
				$html.= '<small>' . $field['description'] . '</small>';
			$html.= '</div>';
			
			$html.= '<div class="clear"></div>';
			
		$html.= '</div>';
		
		return $html;
	}

	public function meta_box_survey_elements(){
		global $surveyval_global;
		
		foreach( $surveyval_global->element_types AS $element_type ):
			echo '<div class="surveyval-draggable">';
			echo $this->get_widget_html( $element_type, TRUE );
			echo '</div>';
		endforeach;
	}
	
	public function meta_boxes( $post_type ){
		$post_types = array( 'surveyval' );
		
		if( in_array( $post_type, $post_types )):
			add_meta_box(
	            'survey-elements',
	            __( 'Elements', 'surveyval-locale' ),
	            array( $this, 'meta_box_survey_elements' ),
	            'surveyval',
	            'side',
	            'high'
	        );
		endif;
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
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'admin-widgets' );
		wp_enqueue_script( 'wpdialogs-popup' );
		
    	wp_localize_script( 'admin-surveyval-post-type', 'translation_admin', $translation_admin );
		
		if ( wp_is_mobile() )
			wp_enqueue_script( 'jquery-touch-punch' );
	}
}

$SurveyVal_Admin = new SurveyVal_Admin();
