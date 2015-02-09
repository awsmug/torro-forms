<?php
/*
 * Display Admin Class
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

class Questions_Admin extends Questions_Component{
	var $notices = array();
	
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	function __construct() {
		$this->name = 'QuestionsAdmin';
		$this->title = __( 'Admin', 'questions-locale' );
		$this->description = __( 'Setting up Questions in WordPress Admin.', 'questions-locale' );
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
			add_action( 'wp_ajax_questions_add_members_standard', array( $this, 'filter_user_ajax' ) );
			add_action( 'wp_ajax_questions_invite_participiants', array( $this, 'invite_participiants' ) );
			add_action( 'wp_ajax_questions_dublicate_survey', array( $this, 'dublicate_survey' ) );
			
			add_action( 'init', array( $this, 'save_settings' ), 20 );
			add_action( 'admin_notices', array( $this, 'show_notices' ) );
		endif;
	} // end constructor
	
	/**
	 * Adds the Admin menu.
	 * @since 1.0.0
	 */	
	public function admin_menu(){
		add_menu_page( __( 'Surveys', 'questions-locale' ), __( 'Surveys', 'questions-locale' ), $this->capability, 'Component' . $this->name , array( $this, 'settings_page' ), '', 50 );
		add_submenu_page( 'Component' . $this->name, __( 'Create', 'questions-locale' ), __( 'Create', 'questions-locale' ), $this->capability, 'post-new.php?post_type=questions' );
		add_submenu_page( 'Component' . $this->name, __( 'Categories', 'questions-locale' ), __( 'Categories', 'questions-locale' ), $this->capability, 'edit-tags.php?taxonomy=questions-categories' );
		add_submenu_page( 'Component' . $this->name, __( 'Settings', 'questions-locale' ), __( 'Settings', 'questions-locale' ), $this->capability, 'Component' . $this->name, array( $this, 'settings_page' ) );
	}
	
	// Fix for getting correct menu and display
	public function tax_menu_correction( $parent_file ) {
		global $current_screen;
		$taxonomy = $current_screen->taxonomy;
			
		if ( $taxonomy == 'questions-categories' )
			$parent_file = 'Component' . $this->name;
		
		return $parent_file;
	}
	
	/**
	 * Content of the settings page.
	 * @since 1.0.0
	 */
	public function settings_page(){
		include( QUESTIONS_COMPONENTFOLDER . '/admin/pages/settings.php' );
	}

	public function droppable_area(){
		global $post, $questions_global;
		
		if( !$this->is_questions_post_type() )
			return;
		
		$html = '<div id="questions-content" class="drag-drop">';
			$html.= '<div id="drag-drop-area" class="widgets-holder-wrap">';
			
				/* << INSIDE DRAG&DROP AREA >> */
				$survey = new Questions_Survey( $post->ID );
				// Running each Element
				foreach( $survey->elements AS $element ):
					$html.= $element->draw_admin();
				endforeach;
				/* << INSIDE DRAG&DROP AREA >> */
				
				$html.= '<div class="drag-drop-inside">';
					$html.= '<p class="drag-drop-info">';
						$html.= __( 'Drop your Element here.', 'questions-locale' );
					$html.= '</p>';
				$html.= '</div>';
			$html.= '</div>';
		$html.= '</div>';
		$html.= '<div id="delete_surveyelement_dialog">' . __( 'Do you really want to delete this element?', 'questions-locale' ). '</div>';
		$html.= '<div id="delete_answer_dialog">' . __( 'Do you really want to delete this answer?', 'questions-locale' ). '</div>';
		$html.= '<input type="hidden" id="deleted_surveyelements" name="questions_deleted_surveyelements" value="">';
		$html.= '<input type="hidden" id="deleted_answers" name="questions_deleted_answers" value="">';
		
		echo $html;
	}
	
	public function meta_box_survey_elements(){
		global $questions_global;
		
		$html = '';
		
		foreach( $questions_global->element_types AS $element ):
			$html.= '<div class="questions-draggable">';
			$html.= $element->draw_admin();
			$html.= '</div>';
		endforeach;
		
		echo $html;
	}
	
	public function meta_box_survey_participiants(){
		global $wpdb, $post, $questions_global;
		
		$survey_id = $post->ID;
		
		$sql = $wpdb->prepare( "SELECT user_id FROM {$questions_global->tables->participiants} WHERE survey_id = %s", $survey_id );
		$user_ids = $wpdb->get_col( $sql );
		
		$users = array();
		
		if( is_array( $user_ids ) && count( $user_ids ) > 0 ):
			$users = get_users( array(
				'include' => $user_ids,
				'orderby' => 'ID'
			) );
		endif;
		
		$disabled = '';
		$selected = '';
		
		$participiant_restrictions = get_post_meta( $survey_id, 'participiant_restrictions', TRUE ); 
		
		$restrictions = apply_filters( 'questions_post_type_participiant_restrictions', array(
			'all_visitors' => __( 'All visitors of the site can participate the poll', 'questions-locale' ),
			'all_members' => __( 'All members of the site can participate the poll', 'questions-locale' ),
			'selected_members' => __( 'Only selected members can participate the poll ', 'questions-locale' ),
		) );
		
		if( '' == $participiant_restrictions && count( $users ) > 0 ): // If there are participiants and nothing was selected before
			$participiant_restrictions = 'selected_members';
		elseif( '' == $participiant_restrictions ): // If there was selected nothing before
			$participiant_restrictions = 'all_visitors';
		endif;
			
		$html = '<div id="questions_participiants_select_restrictions">';
			$html.= '<select name="questions_participiants_restrictions_select" id="questions-participiants-restrictions-select"' . $disabled . '>';
			foreach( $restrictions AS $key => $value ):
				$selected = '';
				if( $key == $participiant_restrictions ) $selected = ' selected="selected"';
				$html.= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
			endforeach;
			$html.= '</select>';
		$html.= '</div>';
		
		$options = apply_filters( 'questions_post_type_add_participiants_options', array(
			'all_members' => __( 'Add all actual Members', 'questions-locale' ),
		) );
		
		/*
		 * Selected Members section
		 */
		$html.= '<div id="questions_selected_members">';
		
			$disabled = '';
			$selected = '';
			
			$html.= '<div id="questions_participiants_select">';
				$html.= '<select name="questions_participiants_select" id="questions-participiants-select"' . $disabled . '>';
				foreach( $options AS $key => $value ):
					$html.= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
				endforeach;
				$html.= '</select>';
			$html.= '</div>';
			
			$html.= '<div id="questions-participiants-standard-options" class="questions-participiants-options-content">';
			$html.= '<div class="add"><input type="button" class="questions-add-participiants button" id="questions-add-members-standard" value="' . __( 'Add Participiants', 'questions-locale' ) . '" /><a href="#" class="questions-remove-all-participiants">' . __( 'Remove all Participiants', 'questions-locale' ) . '</a></div>';
			$html.= '</div>';
			
			ob_start();
			do_action( 'questions_post_type_participiants_content_top' );
			$html.= ob_get_clean();
			
			$html.= '<div id="questions-participiants-status" class="questions-participiants-status">';
			$html.= '<p>' . count( $users ) . ' ' . __( 'participiant/s', 'questions-locale' ) . '</p>';
			$html.= '</div>';
			
			$html.= '<div id="questions-participiants-list">';
				$html.= '<table class="wp-list-table widefat">';
					$html.= '<thead>';
						$html.= '<tr>';
							$html.= '<th>' . __( 'ID', 'questions-locale' ) . '</th>';
							$html.= '<th>' . __( 'User nicename', 'questions-locale' ) . '</th>';
							$html.= '<th>' . __( 'Display name', 'questions-locale' ) . '</th>';
							$html.= '<th>' . __( 'Email', 'questions-locale' ) . '</th>';
							$html.= '<th>' . __( 'Status', 'questions-locale' ) . '</th>';
							$html.= '<th>&nbsp</th>';
						$html.= '</tr>';
					$html.= '</thead>';
					
					
					$html.= '<tbody>';
					
					$questions_participiants_value = '';
					
					if( is_array( $users ) && count( $users ) > 0 ):
					
						foreach( $users AS $user ):
							if( sv_user_has_participated( $survey_id, $user->ID ) ):
								$user_css = ' finished';
								$user_text = __( 'finished', 'questions-locale' );
							else:
								$user_text = __( 'new', 'questions-locale' );
								$user_css = ' new';
							endif;
							
							$html.= '<tr class="participiant participiant-user-' . $user->ID . $user_css .'">';
								$html.= '<td>' . $user->ID . '</td>';
								$html.= '<td>' . $user->user_nicename . '</td>';
								$html.= '<td>' . $user->display_name . '</td>';
								$html.= '<td>' . $user->user_email . '</td>';
								$html.= '<td>' . $user_text . '</td>';
								$html.= '<td><a class="button questions-delete-participiant" rel="' . $user->ID . '">' . __( 'Delete', 'questions-locale' ) . '</a></th>';
							$html.= '</tr>';
						endforeach;
						
						$questions_participiants_value = implode( ',', $user_ids );
						
					endif;
					
					$html.= '</tbody>';
					
				$html.= '</table>';
				
				$html.= '<input type="hidden" id="questions-participiants" name="questions_participiants" value="' . $questions_participiants_value . '" />';
				$html.= '<input type="hidden" id="questions-participiants-count" name="questions-participiants-count" value="' . count( $users ) . '" />';
			
			$html.= '</div>';
			
		$html.= '</div>';
		
		echo $html;
	}
	
	public function meta_box_survey_options(){
		global $post;
		
		$survey_id = $post->ID;
		$show_results = get_post_meta( $survey_id, 'show_results', TRUE ); 
		
		if( '' == $show_results )
			$show_results = 'no';
		
		$checked_no = '';
		$checked_yes = '';
		
		if( 'no' == $show_results )
			$checked_no = ' checked="checked"';
		else
			$checked_yes = ' checked="checked"';
		
		$html = '<div class="questions-option-element">';
			$html.= '<div class="questions-option-element">';
				$html.= '<p><label for="show_results">' . __( 'Show Results', 'questions-locale' ) . '</label></p>';
				$html.= '<input type="radio" name="show_results" value="yes"' . $checked_yes .'>' . __( 'Yes') . '<br>';
				$html.= '<input type="radio" name="show_results" value="no"' . $checked_no .'>' . __( 'No') . '<br>';
			$html.= '</div>';
		$html.= '</div>';
		
		echo $html;
	}

	public function meta_box_survey_functions(){
		global $post;
		
		$questions_invitation_text_template = qu_get_mail_template_text( 'invitation' );
		$questions_reinvitation_text_template = qu_get_mail_template_text( 'reinvitation' );
		
		$questions_invitation_subject_template = qu_get_mail_template_subject( 'invitation' );
		$questions_reinvitation_subject_template = qu_get_mail_template_subject( 'reinvitation' );
		
		$html = '<div class="questions-function-element">';
			$html.= '<input id="questions-dublicate-survey" name="questions-dublicate-survey" type="button" class="button" value="' . __( 'Dublicate Survey', 'questions-locale' ) . '" />';
		$html.= '</div>';

		if( 'publish' == $post->post_status  ):
			$html.= '<div class="questions-function-element">';
				$html.= '<input id="questions-invite-subject" type="text" name="questions_invite_subject" value="' . $questions_invitation_subject_template . '" />';
				$html.= '<textarea id="questions-invite-text" name="questions_invite_text">' . $questions_invitation_text_template . '</textarea>';
				$html.= '<input id="questions-invite-button" type="button" class="button" value="' . __( 'Invite Participiants', 'questions-locale' ) . '" /> ';
				$html.= '<input id="questions-invite-button-cancel" type="button" class="button" value="' . __( 'Cancel', 'questions-locale' ) . '" />';
			$html.= '</div>';
			
			$html.= '<div class="questions-function-element">';
				$html.= '<input id="questions-reinvite-subject" type="text" name="questions_invite_subject" value="' . $questions_reinvitation_subject_template . '" />';
				$html.= '<textarea id="questions-reinvite-text" name="questions_reinvite_text">' . $questions_reinvitation_text_template . '</textarea>';
				$html.= '<input id="questions-reinvite-button" type="button" class="button" value="' . __( 'Reinvite Participiants', 'questions-locale' ) . '" /> ';
				$html.= '<input id="questions-reinvite-button-cancel" type="button" class="button" value="' . __( 'Cancel', 'questions-locale' ) . '" />';
			$html.= '</div>';
		else:
			$html.= '<p>' . __( 'You can invite Participiants to this survey after the survey is published.', 'questions-locale' ) . '</p>';
		endif;
		
		echo $html;
	}
	
	public function meta_boxes( $post_type ){
		$post_types = array( 'questions' );
		
		if( in_array( $post_type, $post_types )):
			add_meta_box(
	            'survey-options',
	            __( 'Survey Options', 'questions-locale' ),
	            array( $this, 'meta_box_survey_options' ),
	            'questions',
	            'side'
	        );
			add_meta_box(
	            'survey-invites',
	            __( 'Survey Functions', 'questions-locale' ),
	            array( $this, 'meta_box_survey_functions' ),
	            'questions',
	            'side'
	        );
			add_meta_box(
	            'survey-elements',
	            __( 'Elements', 'questions-locale' ),
	            array( $this, 'meta_box_survey_elements' ),
	            'questions',
	            'side',
	            'high'
	        );
	        add_meta_box(
	            'survey-participiants',
	            __( 'Participiants list', 'questions-locale' ),
	            array( $this, 'meta_box_survey_participiants' ),
	            'questions',
	            'normal',
	            'high'
	        );
		endif;
	}
	
	public function save_survey( $post_id ){
		if( array_key_exists( 'questions-dublicate-survey', $_REQUEST ) )
			return;
		
		if ( wp_is_post_revision( $post_id ) )
			return;
		
		if( !array_key_exists( 'post_type', $_POST ) )
			return;
		
		if ( 'questions' != $_POST['post_type'] )
			return;
		
		$this->save_survey_postdata( $post_id );
		
		do_action( 'questions_save_survey', $post_id );
		
		// Preventing dublicate saving
		remove_action( 'save_post', array( $this, 'save_survey' ), 50 );
	}

	public function save_survey_postdata( $post_id ){
		global $questions_global, $wpdb;
		
		$survey_elements = $_POST['questions'];
		$survey_deleted_surveyelements = $_POST['questions_deleted_surveyelements'];
		$survey_deleted_answers = $_POST['questions_deleted_answers'];
		$survey_participiant_restrictions = $_POST['questions_participiants_restrictions_select'];
		$survey_show_results = $_POST['show_results'];
		$questions_participiants = $_POST['questions_participiants'];
		
		/*
		 * Saving Restrictions
		 */
		update_post_meta( $post_id, 'participiant_restrictions', $survey_participiant_restrictions );
		
		/*
		 * Saving if results have to be shown after participating
		 */
		update_post_meta( $post_id, 'show_results', $survey_show_results );
		 
		$survey_deleted_surveyelements = explode( ',', $survey_deleted_surveyelements );
		
		/*
		 * Deleting deleted answers
		 */
		if( is_array( $survey_deleted_surveyelements ) && count( $survey_deleted_surveyelements ) > 0 ):
			foreach( $survey_deleted_surveyelements AS $deleted_question ):
				$wpdb->delete( 
					$questions_global->tables->questions, 
					array( 'id' => $deleted_question ) 
				);
				$wpdb->delete( 
					$questions_global->tables->answers, 
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
					$questions_global->tables->answers, 
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
					$questions_global->tables->questions,
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
					$questions_global->tables->questions,
					array(
						'questions_id' => $post_id,
						'question' => $question,
						'sort' => $sort,
						'type' => $type  )
				);
				
				$new_question = TRUE;
				$question_id = $wpdb->insert_id;
			endif;
			
			do_action( 'questions_save_survey_after_saving_question', $survey_question, $question_id );
			
			/*
			 * Saving answers
			 */
			if( is_array( $answers )  && count( $answers ) >  0 ):
				foreach( $answers AS $answer ):
					$answer_id = $answer['id'];
					$answer_text = $answer['answer'];
					$answer_sort = $answer['sort'];
					
					$answer_section = '';
					if( array_key_exists( 'section', $answer ) )
						$answer_section = $answer['section'];
					
					if( '' != $answer_id ):
						$wpdb->update(
							$questions_global->tables->answers,
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
							$questions_global->tables->answers,
							array(
								'question_id' => $question_id,
								'answer' => $answer_text,
								'section' => $answer_section,
								'sort' => $answer_sort
							)
						);
						$answer_id = $wpdb->insert_id;
					endif;
					
					do_action( 'questions_save_survey_after_saving_answer', $survey_question, $answer_id );
				endforeach;
			endif;
			
			/*
			 * Saving question settings
			 */
			if( is_array( $settings )  && count( $settings ) >  0 ):
				foreach( $settings AS $name => $setting ):
					$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$questions_global->tables->settings} WHERE question_id = %d AND name = %s", $question_id, $name );
					$count = $wpdb->get_var( $sql );
					
					if( $count > 0 ):
						$wpdb->update(
							$questions_global->tables->settings,
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
							$questions_global->tables->settings,
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
		
		$questions_participiant_ids = explode( ',', $questions_participiants );
		
		$sql = "DELETE FROM {$questions_global->tables->participiants} WHERE survey_id = %d";
		$sql = $wpdb->prepare( $sql, $post_id );
		$wpdb->query( $sql );
		
		if( is_array( $questions_participiant_ids ) && count( $questions_participiant_ids ) > 0 ):
			foreach( $questions_participiant_ids AS $user_id ):
				$wpdb->insert(
					$questions_global->tables->participiants,
					array(
						'survey_id' => $post_id,
						'user_id' => $user_id
					)
				);
			endforeach;
		endif;
		
		do_action( 'save_questions', $post_id );
		
		return TRUE;
	}

	public function delete_survey( $survey_id ){
		global $wpdb, $questions_global;
		
		$sql = $wpdb->prepare( "SELECT id FROM {$questions_global->tables->questions} WHERE questions_id=%d", $survey_id );
		$elements = $wpdb->get_col( $sql );
		
		/*
		 * Answers & Settings
		 */
		if( is_array( $elements ) && count( $elements ) > 0 ):
			foreach( $elements AS $question_id ):
				$wpdb->delete( 
					$questions_global->tables->answers,
					array( 'question_id' => $question_id ) 
				);
				
				$wpdb->delete( 
					$questions_global->tables->settings,
					array( 'question_id' => $question_id ) 
				);
				
			do_action( 'questions_delete_element', $question_id, $survey_id );
			endforeach;
		endif;
		
		/*
		 * Questions
		 */
		$wpdb->delete( 
			$questions_global->tables->questions, 
			array( 'questions_id' => $survey_id ) 
		);
		
		do_action( 'questions_delete_survey', $survey_id );
		
		/*
		 * Response Answers
		 */
		$sql = $wpdb->prepare( "SELECT id FROM {$questions_global->tables->respond_answers} WHERE questions_id=%d", $survey_id );
		$responses = $wpdb->get_col( $sql );
		
		if( is_array( $responses ) && count( $responses ) > 0 ):
			foreach( $responses AS $respond_id ):
				$wpdb->delete( 
					$questions_global->tables->respond_answers,
					array( 'respond_id' => $respond_id ) 
				);
				
			do_action( 'questions_delete_responds', $respond_id, $survey_id );
			endforeach;
		endif;
		
		/*
		 * Responds
		 */
		$wpdb->delete( 
			$questions_global->tables->responds, 
			array( 'questions_id' => $survey_id ) 
		);
		
		/*
		 * Participiants
		 */
		$wpdb->delete( 
			$questions_global->tables->participiants, 
			array( 'survey_id' => $survey_id ) 
		);
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
		global $wpdb, $questions_global;
		
		$return_array = array(
			'sent' => FALSE
		);
		
		$survey_id = $_POST['survey_id'];
		$subject_template = $_POST['subject_template'];
		$text_template = $_POST['text_template'];
		
		$sql = "SELECT user_id FROM {$questions_global->tables->participiants} WHERE survey_id = %d";
		$sql = $wpdb->prepare( $sql, $survey_id );
		$user_ids = $wpdb->get_col( $sql );
		
		$subject =  __( 'Survey Invitation', 'questions-content' );
		
		if( 'reinvite' == $_POST['invitation_type'] ):
			if( is_array( $user_ids ) && count( $user_ids ) > 0 ):
				foreach( $user_ids AS $user_id ):
					if( !sv_user_has_participated( $survey_id, $user_id ) ):
						$user_ids_new[] = $user_id;
					endif;
			endforeach;
			endif;
			$user_ids = $user_ids_new;
			$subject =  __( 'Survey Reinvitation', 'questions-content' );
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
			
			$subject = str_replace( '%site_name%', get_bloginfo( 'name' ), $subject_template );
			$subject = str_replace( '%survey_title%', $post->post_title, $subject );
			$subject = str_replace( '%survey_url%', get_permalink( $post->ID ), $subject );
			
			foreach( $users AS $user ):
				if( '' != $user->data->display_name  )
					$display_name = $user->data->display_name;
				else
					$display_name = $user->data->user_nicename;
				
				$user_nicename = $user->data->user_nicename;
				$user_email = $user->data->user_email;

				$subject_user = str_replace( '%displayname%', $display_name, $subject );
				$subject_user = str_replace( '%username%', $user_nicename, $subject_user );
				
				$content_user = str_replace( '%displayname%', $display_name, $content );
				$content_user = str_replace( '%username%', $user_nicename, $content_user );
				
				sv_mail( $user_email, $subject_user, stripslashes( $content_user ) );
			endforeach;
		
			$return_array = array(
				'sent' => TRUE
			);
		endif;
		
		echo json_encode( $return_array );

		die();
	}

	private function is_questions_post_type(){
		global $post;
		
		// If there is no post > stop adding scripts	
		if( !isset( $post ) )
			return FALSE;
		
		// If post type is wrong > stop adding scripts
		if( 'questions' != $post->post_type )
			return FALSE;
			
		return TRUE;
	}
	
	public function save_settings(){
		
		if( !array_key_exists( 'questions_settings_save', $_POST ) )
			return;
			
		if ( !isset( $_POST['questions_save_settings_field'] ) || !wp_verify_nonce( $_POST['questions_save_settings_field'], 'questions_save_settings' ) )
			return;
		
		update_option( 'questions_thankyou_participating_subject_template', $_POST['questions_thankyou_participating_subject_template'] );
		update_option( 'questions_invitation_subject_template', $_POST['questions_invitation_subject_template'] );
		update_option( 'questions_reinvitation_subject_template', $_POST['questions_reinvitation_subject_template'] );
		
		update_option( 'questions_thankyou_participating_text_template', $_POST['questions_thankyou_participating_text_template'] );
		update_option( 'questions_invitation_text_template', $_POST['questions_invitation_text_template'] );
		update_option( 'questions_reinvitation_text_template', $_POST['questions_reinvitation_text_template'] );
		
		update_option( 'questions_mail_from_name', $_POST['questions_mail_from_name'] );
		update_option( 'questions_mail_from_email', $_POST['questions_mail_from_email'] );
	}

	public function dublicate_survey(){
		$survey_id =  $_REQUEST['survey_id'];
		$survey = get_post( $survey_id );
		
		if( 'questions' != $survey->post_type )
			return;
		
		$survey = new questions_PostSurvey( $survey_id );
		$new_survey_id = $survey->dublicate( TRUE, FALSE, TRUE, TRUE, TRUE, TRUE );
		
		$post = get_post( $new_survey_id );
		
		$response =  array( 
			'survey_id' => $new_survey_id,
			'post_title' => $post->post_title,
			'admin_url' => site_url( '/wp-admin/post.php?post=' . $new_survey_id . '&action=edit' )
		);
		
		echo json_encode( $response );
		
		die();
	}
	
	public function notice( $message, $type = 'updated' ){
		$this->notices[] = array( 
			'message' => $message,
			'type' => $type
		);
	}
	
	public function show_notices(){
		if( is_array( $this->notices ) && count( $this->notices ) > 0 ):
			foreach( $this->notices AS $notice ):
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
		if( !$this->is_questions_post_type() )
			return;
		
		$translation_admin = array( 
			'delete' => __( 'Delete', 'questions-locale' ),
			'yes' => __( 'Yes', 'questions-locale' ),
			'no' => __( 'No', 'questions-locale' ),
			'just_added' => __( 'just added', 'questions-locale' ),
			'invitations_sent_successfully' => __( 'Invitations sent successfully!', 'questions-locale' ),
			'invitations_not_sent_successfully' => __( 'Invitations could not be sent!', 'questions-locale' ),
			'reinvitations_sent_successfully' => __( 'Renvitations sent successfully!', 'questions-locale' ),
			'reinvitations_not_sent_successfully' => __( 'Renvitations could not be sent!', 'questions-locale' ),
			'dublicate_survey_successfully' => __( 'Survey dublicated successfully!', 'questions-locale' ),
			'edit_survey' => __( 'Edit Survey', 'questions-locale' ),
			'added_participiants' => __( 'participiant/s', 'questions-locale' )
		);
		
		wp_enqueue_script( 'admin-questions-post-type', QUESTIONS_URLPATH . '/components/admin/includes/js/admin-questions-post-type.js' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'admin-widgets' );
		wp_enqueue_script( 'wpdialogs-popup' );
		
    	wp_localize_script( 'admin-questions-post-type', 'translation_admin', $translation_admin );
		
		if ( wp_is_mobile() )
			wp_enqueue_script( 'jquery-touch-punch' );
	}
}

$Questions_Admin = new Questions_Admin();
