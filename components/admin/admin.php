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
	var $notices = array();
	
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
			add_action( 'add_meta_boxes', array( $this, 'meta_boxes' ), 10 );
			add_action( 'save_post', array( $this, 'save_survey' ) );
			add_action( 'delete_post', array( $this, 'delete_survey' ) );
			add_action( 'wp_ajax_surveyval_add_members_standard', array( $this, 'filter_user_ajax' ) );
			add_action( 'wp_ajax_surveyval_invite_participiants', array( $this, 'invite_participiants' ) );
			add_action( 'init', array( $this, 'save_settings' ), 20 );
			add_action( 'init', array( $this, 'dublicate_survey' ) );
			add_action( 'admin_notices', array( $this, 'show_notices' ) );
		endif;
	} // end constructor
	
	/**
	 * Adds the Admin menu.
	 * @since 1.0.0
	 */	
	public function admin_menu(){
		add_menu_page( __( 'Surveys', 'surveyval-locale' ), __( 'Surveys', 'surveyval-locale' ), $this->capability, 'Component' . $this->name , array( $this, 'settings_page' ), '', 50 );
		add_submenu_page( 'Component' . $this->name, __( 'Create', 'surveyval-locale' ), __( 'Create', 'surveyval-locale' ), $this->capability, 'post-new.php?post_type=surveyval' );
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
			
				/* << INSIDE DRAG&DROP AREA >> */
				$survey = new SurveyVal_Survey( $post->ID );
				// Running each Element
				foreach( $survey->elements AS $element ):
					$html.=  $this->element( $element );
				endforeach;
				/* << INSIDE DRAG&DROP AREA >> */
				
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
	
	private function element( $element, $new = FALSE ){
		$id = $element->id;
		$title = empty( $element->question ) ? $element->title : $element->question;
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
					$html.= $this->element_form( $element, $new );
				$html.= '</div>';
			$html.= '</div>';
		$html.= '</div>';
		
		return $html;
	}
	
	public function element_form( $element, $new = FALSE ){
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
			
			$html = apply_filters( 'surveyval_element_tabs', $html, $element, $widget_id, $new );
			
		$html.= '</ul>';
		
		$html.= '<div class="clear tabs_underline"></div>';
		
		// Adding question HTML
		if( $element->is_question ):
			$html.= '<div id="tab_' . $jquery_widget_id . '_questions" class="tab_questions_content">';
				$html.= $this->question( $element, $widget_id, $new );
			$html.= '</div>'; 
		endif;
		
		// Adding settings HTML
		if( is_array( $element->settings_fields ) && count( $element->settings_fields ) > 0 ):
			$html.= '<div id="tab_' . $jquery_widget_id . '_settings" class="tab_settings_content">';
				$html.= $this->settings( $element, $widget_id, $new );
			$html.= '</div>';
		endif;
		
		// Adding action Buttons
		$bottom_buttons = apply_filters( 'sv_element_bottom_actions', array(
			'delete_survey_element' => array(
				'text' => __( 'Delete element', 'surveyval-locale' ),
				'classes' => 'delete_survey_element'
			)
		));
		
		$html = apply_filters( 'surveyval_element_tabs_content', $html, $element, $widget_id, $new  );
		
		$html.= '<ul class="survey-element-bottom">';
		foreach( $bottom_buttons AS $button ):
			$html.= '<li><a class="' . $button[ 'classes' ] . ' survey-element-bottom-action button">' . $button[ 'text' ] . '</a></li>';
		endforeach;
		$html.= '</ul>';
		
		// Adding hidden Values for element
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][id]" value="' . $element->id . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][sort]" value="' . $element->sort . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][type]" value="' . $element->slug . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][preset_is_multiple]" value="' . ( $element->preset_is_multiple ? 'yes' : 'no' ) . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][preset_of_answers]" value="' . ( $element->preset_of_answers ? 'yes' : 'no' ) . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][sections]" value="' . ( is_array( $element->sections ) && count( $element->sections ) > 0  ? 'yes' : 'no' ) . '" />';
	
		$html.= '</div>'; 
		
		return $html;
	}

	private function question( $element, $widget_id, $new = FALSE ){
		// Question
		$html = '<p><input type="text" name="surveyval[' . $widget_id . '][question]" value="' . $element->question . '" class="surveyval-question" /><p>';
		
		// Answers
		if( $element->preset_of_answers ):
			if( is_array( $element->sections ) && count( $element->sections ) > 0 ):
				foreach( $element->sections as $section_key => $section_name ):
					$html.= '<div class="surveyval-section" id="section_' . $section_key . '">';
					$html.= '<p>' . $section_name . '</p>';
					$html.= $this->answers( $element, $widget_id, $new, $section_key );
					$html.= '<input type="hidden" name="section_key" value="' . $section_key . '" />';
					$html.= '</div>';
				endforeach;
			else:
				$html.= '<p>' . __( 'Answer/s:', 'surveyval-locale' ) . '</p>';
				$html.= $this->answers( $element, $widget_id, $new );
			endif;
		
		endif;
		
		$html.= '<div class="clear"></div>';
		
		return $html;
	}

	public function answers( $element, $widget_id, $new = TRUE, $section = NULL ){
		
		if( is_array( $element->answers ) && !$new ):
			
			$html.= '<div class="answers">';
			
			foreach( $element->answers AS $answer ):
				
				// If there is a section
				if( NULL != $section )
					// Continue if answer is not of the section
					if( $answer['section'] != $section )
						continue;
						
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
				$html.= ' <input type="button" value="' . __( 'Delete', 'surveyval-locale' ) . '" class="delete_answer button answer_action">';
				$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][answers][id_' . $answer['id'] . '][id]" value="' . $answer['id'] . '" />';
				$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][answers][id_' . $answer['id'] . '][sort]" value="' . $answer['sort'] . '" />';
				
				if( NULL != $section )
					$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][answers][id_' . $answer['id'] . '][section]" value="' . $section . '" />';
				
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
				if( NULL != $section )
					$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][answers][' . $temp_answer_id . '][section]" value="' . $section . '" />';
				
				$html.= '</div>';
				$html.= '</div><div class="clear"></div>';
				
			endif;
			
		endif;
		
		if( $element->preset_is_multiple )
			$html.= '<a class="add-answer" rel="' . $widget_id . '">+ ' . __( 'Add Answer', 'surveyval-locale' ). ' </a>';
		
		return $html;
	}
	
	public function get_answer( $answer_id ){
			
	}
	
	private function settings( $element, $widget_id, $new ){
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
				
			case 'textarea':
				
				$input = '<textarea name="' . $name . '">' . $value . '</textarea>';
				break;
				
			case 'radio':
				
				$input = '';
				
				foreach( $field['values'] AS $field_key => $field_value ):
					$checked = '';
					
					if( $value == $field_key )
						$checked = ' checked="checked"';
					
					$input.= '<span class="settings-fieldset-input-radio"><input type="radio" name="' . $name . '" value="' . $field_key . '"' . $checked . ' /> ' . $field_value . '</span>';
				endforeach;
				
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
			echo $this->element( $element_type, TRUE );
			echo '</div>';
		endforeach;
	}
	
	public function meta_box_survey_participiants(){
		global $wpdb, $post, $surveyval_global;
		
		$survey_id = $post->ID;
		
		$options = apply_filters( 'surveyval_post_type_add_participiants_options', array(
			'all_members' => __( 'Add all actual Members', 'surveyval-locale' ),
		) );
		
		// If there is only one option
		// if( count( $options ) < 2 ) $disabled = ' disabled';
		
		$html = '<div id="surveyval_participiants_select">';
			$html.= '<select name="surveyval_participiants_select" id="surveyval-participiants-select"' . $disabled . '>';
			foreach( $options AS $key => $value ):
				// $selected = '';
				// if( $key == $surveyval_participiants ) $selected = ' selected="selected"';
				$html.= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
			endforeach;
			$html.= '</select>';
		$html.= '</div>';
		
		// If there is only one option
		// if( count( $options ) < 2 ) $html.= '<p>' . __( 'Get more options to select participiants by adding extra plugins for SurveyVal.<br /><a href="%s" target="_blank">Get it here</a>!', 'surveyval-locale' ) . '</p>';
		
		$html.= '<div id="surveyval-participiants-standard-options" class="surveyval-participiants-options-content">';
		$html.= '<div class="add"><input type="button" class="surveyval-add-participiants button" id="surveyval-add-members-standard" value="' . __( 'Add Participiants', 'surveyval-locale' ) . '" /><a href="#" class="surveyval-remove-all-participiants">' . __( 'Remove all Participiants', 'surveyval-locale' ) . '</a></div>';
		$html.= '</div>';
		
		
		ob_start();
		do_action( 'surveyval_post_type_participiants_content_top' );
		$html.= ob_get_clean();
		
		$sql = "SELECT user_id FROM {$surveyval_global->tables->participiants} WHERE survey_id = %s";
		$sql = $wpdb->prepare( $sql, $survey_id );
		$user_ids = $wpdb->get_col( $sql );
		
		if( is_array( $user_ids ) && count( $user_ids ) > 0 ):
			$users = get_users( array(
				'include' => $user_ids,
				'orderby' => 'ID'
			) );
		endif;
		
		$html.= '<div id="surveyval-participiants-status" class="surveyval-participiants-status">';
		// $html.= '<p>' . sprintf( _n( '%d participiant in list.', '%d participiants in list.', count( $users ), 'surveyval-locale' ), count( $users ) ) . '</p>';
		$html.= '<p>' . count( $users ) . ' ' . __( 'participiant/s', 'surveyval-locale' ) . '</p>';
		$html.= '</div>';
		
		$html.= '<div id="surveyval-participiants-list">';
			$html.= '<table class="wp-list-table widefat">';
				$html.= '<thead>';
					$html.= '<tr>';
						$html.= '<th>' . __( 'ID', 'surveyval-locale' ) . '</th>';
						$html.= '<th>' . __( 'User nicename', 'surveyval-locale' ) . '</th>';
						$html.= '<th>' . __( 'Display name', 'surveyval-locale' ) . '</th>';
						$html.= '<th>' . __( 'Email', 'surveyval-locale' ) . '</th>';
						$html.= '<th>' . __( 'Status', 'surveyval-locale' ) . '</th>';
						$html.= '<th>&nbsp</th>';
					$html.= '</tr>';
				$html.= '</thead>';
				
				
				$html.= '<tbody>';
				
				if( is_array( $users ) && count( $users ) > 0 ):
				
					foreach( $users AS $user ):
						if( sv_user_has_participated( $survey_id, $user->ID ) ):
							$user_css = ' finished';
							$user_text = __( 'finished', 'surveyval-locale' );
						else:
							$user_text = __( 'new', 'surveyval-locale' );
							$user_css = ' new';
						endif;
						
						$html.= '<tr class="participiant participiant-user-' . $user->ID . $user_css .'">';
							$html.= '<td>' . $user->ID . '</td>';
							$html.= '<td>' . $user->user_nicename . '</td>';
							$html.= '<td>' . $user->display_name . '</td>';
							$html.= '<td>' . $user->user_email . '</td>';
							$html.= '<td>' . $user_text . '</td>';
							$html.= '<td><a class="button surveyval-delete-participiant" rel="' . $user->ID . '">' . __( 'Delete', 'surveyval-locale' ) . '</a></th>';
						$html.= '</tr>';
					endforeach;
					
					$surveyval_participiants_value = implode( ',', $user_ids );
					
				endif;
				
				$html.= '</tbody>';
				
			$html.= '</table>';
			
			$html.= '<input type="hidden" id="surveyval-participiants" name="surveyval_participiants" value="' . $surveyval_participiants_value . '" />';
			$html.= '<input type="hidden" id="surveyval-participiants-count" name="surveyval-participiants-count" value="' . count( $users ) . '" />';
			
		$html.= '</div>';
		
		echo $html;
	}

	public function meta_box_survey_functions(){
		global $post;
		
		$surveyval_invitation_text_template = sv_get_mail_template_text( 'invitation' );
		$surveyval_reinvitation_text_template = sv_get_mail_template_text( 'reinvitation' );
		
		$html = '<div class="surveyval-function-element">';
			$html.= '<input id="surveyval-dublicate-survey" name="surveyval-dublicate-survey" type="submit" class="button" value="' . __( 'Dublicate Survey', 'surveyval-locale' ) . '" />';
		$html.= '</div>';

		if( 'publish' == $post->post_status  ):
			$html.= '<div class="surveyval-function-element">';
				$html.= '<textarea id="surveyval-invite-text" name="surveyval_invite_text">' . $surveyval_invitation_text_template . '</textarea>';
				$html.= '<input id="surveyval-invite-button" type="button" class="button" value="' . __( 'Invite Participiants', 'surveyval-locale' ) . '" /> ';
				$html.= '<input id="surveyval-invite-button-cancel" type="button" class="button" value="' . __( 'Cancel', 'surveyval-locale' ) . '" />';
			$html.= '</div>';
			
			$html.= '<div class="surveyval-function-element">';
				$html.= '<textarea id="surveyval-reinvite-text" name="surveyval_reinvite_text">' . $surveyval_reinvitation_text_template . '</textarea>';
				$html.= '<input id="surveyval-reinvite-button" type="button" class="button" value="' . __( 'Reinvite Participiants', 'surveyval-locale' ) . '" /> ';
				$html.= '<input id="surveyval-reinvite-button-cancel" type="button" class="button" value="' . __( 'Cancel', 'surveyval-locale' ) . '" />';
			$html.= '</div>';
		else:
			$html.= '<p>' . __( 'You can invite Participiants to this survey after the survey is published.', 'surveyval-locale' ) . '</p>';
		endif;
		
		echo $html;
	}
	
	public function meta_boxes( $post_type ){
		$post_types = array( 'surveyval' );
		
		if( in_array( $post_type, $post_types )):
			add_meta_box(
	            'survey-invites',
	            __( 'Survey Functions', 'surveyval-locale' ),
	            array( $this, 'meta_box_survey_functions' ),
	            'surveyval',
	            'side'
	        );
			add_meta_box(
	            'survey-elements',
	            __( 'Elements', 'surveyval-locale' ),
	            array( $this, 'meta_box_survey_elements' ),
	            'surveyval',
	            'side',
	            'high'
	        );
	        add_meta_box(
	            'survey-participiants',
	            __( 'Participiants list', 'surveyval-locale' ),
	            array( $this, 'meta_box_survey_participiants' ),
	            'surveyval',
	            'normal',
	            'high'
	        );
		endif;
	}
	
	public function save_survey( $post_id ){
		
		if ( wp_is_post_revision( $post_id ) )
			return;
		
		if( !array_key_exists( 'post_type', $_POST ) )
			return;
		
		if ( 'surveyval' != $_POST['post_type'] )
			return;
		
		$this->save_survey_postdata( $post_id );
		
		// Preventing dublicate saving
		remove_action( 'save_post', array( $this, 'save_survey' ), 50 );
	}

	public function save_survey_postdata( $post_id ){
		global $surveyval_global, $wpdb;
		
		$survey_elements = $_POST['surveyval'];
		$survey_deleted_surveyelements = $_POST['surveyval_deleted_surveyelements'];
		$survey_deleted_answers = $_POST['surveyval_deleted_answers'];
		$surveyval_participiants = $_POST['surveyval_participiants'];
		
		// mail( 'sven@deinhilden.de', 'Test', print_r( $_POST, TRUE ) . print_r( $surveyval_global, TRUE ) );
		
		$survey_deleted_surveyelements = explode( ',', $survey_deleted_surveyelements );
		
		/*
		 * Deleting deleted answers
		 */
		if( is_array( $survey_deleted_surveyelements ) && count( $survey_deleted_surveyelements ) > 0 ):
			foreach( $survey_deleted_surveyelements AS $deleted_question ):
				$wpdb->delete( 
					$surveyval_global->tables->questions, 
					array( 'id' => $deleted_question ) 
				);
				$wpdb->delete( 
					$surveyval_global->tables->answers, 
					array( 'question_id' => $deleted_question ) 
				);
			endforeach;
		endif;
		
		$survey_deleted_answers = explode( ',', $survey_deleted_answers );
		
		/*
		 * Deleting deleted answers
		 */
		if( is_array( $survey_deleted_answers ) && count( $survey_deleted_answers ) > 0 ):
			foreach( $survey_deleted_answers AS $deleted_answer ):
				$wpdb->delete( 
					$surveyval_global->tables->answers, 
					array( 'id' => $deleted_answer ) 
				);
			endforeach;
		endif;
		
		/*
		 * Saving elements
		 */
		foreach( $survey_elements AS $key => $survey_question ):
			if( 'widget_surveyelement_##nr##' == $key )
				continue;
			
			$question_id = $survey_question['id'];
			$question = $survey_question['question'];
			$sort = $survey_question['sort'];
			$type = $survey_question['type'];
			
			$answers = array();
			$settings = array();
			
			$new_question = FALSE;
			
			if( array_key_exists( 'answers', $survey_question ) )
				$answers = $survey_question['answers'];
			
			if( array_key_exists( 'settings', $survey_question ) )
				$settings = $survey_question['settings'];
			
			// Saving question
			if( '' != $question_id ):
				// Updating if question already exists
				$wpdb->update(
					$surveyval_global->tables->questions,
					array(
						'question' => $question,
						'sort' => $sort,
						'type' => $type
					),
					array(
						'id' => $question_id
					)
				);
			else:

				// Adding new question
				$wpdb->insert(
					$surveyval_global->tables->questions,
					array(
						'surveyval_id' => $post_id,
						'question' => $question,
						'sort' => $sort,
						'type' => $type  )
				);
				
				$new_question = TRUE;
				$question_id = $wpdb->insert_id;
			endif;
			
			/*
			 * Saving answers
			 */
			if( is_array( $answers )  && count( $answers ) >  0 ):
				foreach( $answers AS $answer ):
					$answer_id = $answer['id'];
					$answer_text = $answer['answer'];
					$answer_sort = $answer['sort'];
					$answer_section = $answer['section'];
					
					if( '' != $answer_id ):
						$wpdb->update(
							$surveyval_global->tables->answers,
							array( 
								'answer' => $answer_text,
								'section' => $answer_section,
								'sort' => $answer_sort
							),
							array(
								'id' => $answer_id
							)
						);
					else:
						$wpdb->insert(
							$surveyval_global->tables->answers,
							array(
								'question_id' => $question_id,
								'answer' => $answer_text,
								'section' => $answer_section,
								'sort' => $answer_sort
							)
						);
					endif;
				endforeach;
			endif;
			
			/*
			 * Saving answers
			 */
			if( is_array( $settings )  && count( $settings ) >  0 ):
				foreach( $settings AS $name => $setting ):
					$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$surveyval_global->tables->settings} WHERE question_id = %d AND name = %s", $question_id, $name );
					$count = $wpdb->get_var( $sql );
					
					if( $count > 0 ):
						$wpdb->update(
							$surveyval_global->tables->settings,
							array( 
								'value' => $settings[ $name ]
							),
							array(
								'question_id' => $question_id,
								'name' => $name
							)
						);
					else:
						$wpdb->insert(
							$surveyval_global->tables->settings,
							array(
								'name' => $name,
								'question_id' => $question_id,
								'value' => $settings[ $name ]
							)
						);
						
					endif;
				endforeach;
			endif;

		endforeach;
		
		$surveyval_participiant_ids = explode( ',', $surveyval_participiants );
		
		$sql = "DELETE FROM {$surveyval_global->tables->participiants} WHERE survey_id = %d";
		$sql = $wpdb->prepare( $sql, $post_id );
		$wpdb->query( $sql );
		
		if( is_array( $surveyval_participiant_ids ) && count( $surveyval_participiant_ids ) > 0 ):
			foreach( $surveyval_participiant_ids AS $user_id ):
				$wpdb->insert(
					$surveyval_global->tables->participiants,
					array(
						'survey_id' => $post_id,
						'user_id' => $user_id
					)
				);
			endforeach;
		endif;
		
		// mail( 'sven@deinhilden.de', 'Check Participiants', print_r( $surveyval_participiant_ids, TRUE ) . print_r( $wpdb, TRUE ) );
		
		do_action( 'save_surveyval', $post_id );
		
		return TRUE;
	}

	public function delete_survey( $post_id ){
		global $wpdb, $surveyval_global;
		
		$sql = $wpdb->prepare( "SELECT id FROM {$surveyval_global->tables->questions} WHERE surveyval_id=%d", $post_id );
		
		$elements = $wpdb->get_col( $sql );
		
		$wpdb->delete( 
			$surveyval_global->tables->questions, 
			array( 'surveyval_id' => $post_id ) 
		);
		
		if( is_array( $elements ) && count( $elements ) > 0 ):
			foreach( $elements AS $question ):
				$wpdb->delete( 
					$surveyval_global->tables->answers,
					array( 'question_id' => $question ) 
				);
			endforeach;
		endif;
	}
	
	public function filter_user_ajax(){
		global $wpdb, $bp;
		
		$users = get_users( array(
			'orderby' => 'ID'
		) );
		
		$return_array = array();
		
		foreach( $users AS $user ):
			$return_array[] = array(
				'id' => $user->ID,
				'user_nicename' => $user->user_nicename,
				'display_name' => $user->display_name,
				'user_email' => $user->user_email,
			);
		endforeach;
		
		echo json_encode( $return_array );

		die();
	}
	
	public function invite_participiants(){
		global $wpdb, $surveyval_global;
		
		$return_array = array(
			'sent' => FALSE
		);
		
		$survey_id = $_POST['survey_id'];
		$text_template = $_POST['text_template'];
		
		$sql = "SELECT user_id FROM {$surveyval_global->tables->participiants} WHERE survey_id = %d";
		$sql = $wpdb->prepare( $sql, $survey_id );
		$user_ids = $wpdb->get_col( $sql );
		
		$subject =  __( 'Survey Invitation', 'surveyval-content' );
		
		if( 'reinvite' == $_POST['invitation_type'] ):
			if( is_array( $user_ids ) && count( $user_ids ) > 0 ):
				foreach( $user_ids AS $user_id ):
					if( !sv_user_has_participated( $survey_id, $user_id ) ):
						$user_ids_new[] = $user_id;
					endif;
			endforeach;
			endif;
			$user_ids = $user_ids_new;
			$subject =  __( 'Survey Reinvitation', 'surveyval-content' );
		endif;
		
		$post = get_post( $survey_id );
		
		if( is_array( $user_ids ) && count( $user_ids ) > 0 ):
			$users = get_users( array(
				'include' => $user_ids,
				'orderby' => 'ID'
			) );
			
			$content = str_replace( '%site_name%', get_bloginfo( 'name' ), $text_template );
			$content = str_replace( '%survey_title%', $post->post_title, $content );
			$content = str_replace( '%survey_url%', get_permalink( $post->ID ), $content );
			
			foreach( $users AS $user ):
				$content = str_replace( '%displayname%', $user->display_name, $content );
				$content = str_replace( '%username%', $user->user_nicename, $content );
				wp_mail( $user->user_email, $subject, stripslashes( $content ) );
			endforeach;
		
			$return_array = array(
				'sent' => TRUE
			);
		endif;
		
		echo json_encode( $return_array );

		die();
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
	
	public function save_settings(){
		if( !array_key_exists( 'surveyval_settings_save', $_POST ) )
			return;
			
		if ( !isset( $_POST['surveyval_save_settings_field'] ) || !wp_verify_nonce( $_POST['surveyval_save_settings_field'], 'surveyval_save_settings' ) )
			return;
			
		update_option( 'surveyval_invitation_text_template', $_POST['surveyval_invitation_text_template'] );
		update_option( 'surveyval_reinvitation_text_template', $_POST['surveyval_reinvitation_text_template'] );
	}

	public function dublicate_survey(){
		$post_id =  $_REQUEST['post_ID'];
		$post = get_post( $post_id );
		
		if( 'surveyval' != $post->post_type )
			return;
		
		if( !array_key_exists( 'surveyval-dublicate-survey', $_REQUEST ) )
			return;
		
		$survey = new SurveyVal_PostSurvey( $post_id );
		$survey->dublicate();
		
		$this->notice( __( 'Dublicated Survey!', 'surveyval-locale' ) );
	}
	
	public function notice( $message, $type = 'updated' ){
		$this->notices[] = array( 
			'message' => $message,
			'type' => $type
		);
	}
	
	public function show_notices(){
		
		if( is_array( $this->notices ) && count( $notices ) > 0 ):
			foreach( $notices AS $notice ):
				echo '<div class="' . $notice[ 'type' ] . '">';
				echo '<p>' . $notice[ 'message' ] . '</p>';
				echo '</div>';
			endforeach;
		endif;
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
			'no' => __( 'No', 'surveyval-locale' ),
			'just_added' => __( 'just added', 'surveyval-locale' ),
			'invitations_sent_successfully' => __( 'Invitations sent successfully!', 'surveyval-locale' ),
			'invitations_not_sent_successfully' => __( 'Invitations could not be sent!', 'surveyval-locale' ),
			'reinvitations_sent_successfully' => __( 'Renvitations sent successfully!', 'surveyval-locale' ),
			'reinvitations_not_sent_successfully' => __( 'Renvitations could not be sent!', 'surveyval-locale' ),
			'added_participiants' => __( 'participiant/s', 'surveyval-locale' )
		);
		
		wp_enqueue_script( 'admin-surveyval-post-type', SURVEYVAL_URLPATH . '/components/admin/includes/js/admin-surveyval-post-type.js' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'admin-widgets' );
		wp_enqueue_script( 'wpdialogs-popup' );
		
    	wp_localize_script( 'admin-surveyval-post-type', 'translation_admin', $translation_admin );
		
		if ( wp_is_mobile() )
			wp_enqueue_script( 'jquery-touch-punch' );
	}
}

function test_alert(){
	echo '<div class="updated">';
	echo '<p>Test</p>';
	echo '</div>';
}
$SurveyVal_Admin = new SurveyVal_Admin();
