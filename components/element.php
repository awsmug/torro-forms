<?php

abstract class SurveyVal_SurveyElement{
	var $id = NULL;
	var $slug;
	var $title;
	var $description;
	var $icon;
	
	var $sort = 0;
	var $is_question = TRUE;
	var $splitter = FALSE;

	var $survey_id;
	
	var $question;
	var $response;
	var $error = FALSE;
	
	var $preset_of_answers = FALSE;
	var $preset_is_multiple = FALSE;
	var $answer_is_multiple = FALSE;
	
	var $answers = array();
	var $settings = array();
	
	var $answer_params = array();
	var $answer_syntax;
	var $answer_selected_syntax;
	
	var $validate_errors = array();
	
	var $create_answer_params = array();
	var $create_answer_syntax;
	
	var $settings_fields = array();

	var $initialized = FALSE;
	
	public function __construct( $id = null ){
		if( null != $id && '' != $id  )
			$this->populate( $id );
		
		$this->settings_fields();
	}	
	
	public function _register() {
		global $surveyval_global;
		
		if( TRUE == $this->initialized )
			return FALSE;
		
		if( !is_object( $surveyval_global ) )
			return FALSE;
		
		if( '' == $this->slug )
			$this->slug = get_class( $this );
		
		if( '' == $this->title )
			$this->title = ucwords( get_class( $this ) );
		
		if( '' == $this->description )
			$this->description =  __( 'This is a SurveyVal Survey Element.', 'surveyval-locale' );
		
		if( array_key_exists( $this->slug, $surveyval_global->element_types ) )
			return FALSE;
		
		if( !is_array( $surveyval_global->element_types ) )
			$surveyval_global->element_types = array();
		
		$this->initialized = TRUE;
		
		return $surveyval_global->add_survey_element( $this->slug, $this );
	}
	
	private function populate( $id ){
		global $wpdb, $surveyval_global;
		
		$this->reset();
		
		$sql = $wpdb->prepare( "SELECT * FROM {$surveyval_global->tables->questions} WHERE id = %s", $id );
		$row = $wpdb->get_row( $sql );
		
		$this->id = $id;
		$this->set_question( $row->question );
		$this->surveyval_id = $row->surveyval_id;
		
		$this->sort = $row->sort;
		
		$sql = $wpdb->prepare( "SELECT * FROM {$surveyval_global->tables->answers} WHERE question_id = %s ORDER BY sort ASC", $id );
		$results = $wpdb->get_results( $sql );
				
		if( is_array( $results ) ):
			foreach( $results AS $result ):
				$this->add_answer( $result->answer, $result->sort, $result->id, $result->section );
			endforeach;
		endif;
		
		
		$sql = $wpdb->prepare( "SELECT * FROM {$surveyval_global->tables->settings} WHERE question_id = %s", $id );
		$results = $wpdb->get_results( $sql );
				
		if( is_array( $results ) ):
			foreach( $results AS $result ):
				$this->add_settings( $result->name, $result->value );
			endforeach;
		endif;
	}
	
	private function set_question( $question, $order = null ){
		if( '' == $question )
			return FALSE;
		
		if( null != $order )
			$this->sort = $order;
		
		$this->question = $question;
		
		return TRUE;
	}
	
	private function add_answer( $text, $sort = FALSE, $id = null, $section = null ){ 
		if( '' == $text )
			return FALSE;
		
		if( FALSE == $this->preset_is_multiple && count( $this->answers ) > 0 )
			return FALSE;
		
		$this->answers[ $id ] = array(
			'id' => $id,
			'text' => $text,
			'sort' => $sort,
			'section' => $section
		);
	}
	
	private function add_settings( $name, $value ){ 
		$this->settings[ $name ] = $value;
	}
	
	public function before_question(){
		return $html;
	}
	
	public function after_question(){
		if( !empty( $this->settings[ 'description' ] ) ):
			$html = '<p class="surveyval-element-description">';
			$html.= $this->settings[ 'description' ];
			$html.= '</p>';
		endif;
		
		return $html;
	}
	
	public function before_answers(){
		return $html;
	}
	
	public function after_answers(){
		return $html;
	}
	
	public function before_answer(){
		return $html;
	}
	
	public function after_answer(){
		return $html;
	}

	public function settings_fields(){
	}

	public function validate( $input ){
		return TRUE;
	}
	
	public function draw(){
		global $surveyval_response_errors;
		
		if( '' == $this->question && $this->is_question )
			return FALSE;
		
		if( 0 == count( $this->answers )  && $this->preset_of_answers == TRUE )
			return FALSE;
		
		$errors = $surveyval_response_errors[ $this->id ];
		
		$html = '';
		
		// Echo Errors
		if( count( $errors ) > 0 ):
			$html.= '<div class="surveyval-element-error">';
			$html.= '<div class="surveyval-element-error-message">';
			$html.= '<ul class="surveyval-error-messages">';
			foreach( $errors AS $error ):
				$html.= '<li>' . $error . '</li>';
			endforeach;
			$html.= '</ul>';
			
			$html.= '</div>';
		endif;
		
		$html.= '<div class="survey-element survey-element-' . $this->id . '">';
		
		if( !empty( $this->question ) ):
			$html.= $this->before_question();
			$html.= '<h5>' . $this->question . '</h5>';
			$html.= $this->after_question();
		endif;
		
		$this->get_response();
		
		$html.= '<div class="answer">';
		$html.= $this->before_answers();
		$html.= $this->before_answer();
		
		$html.= $this->input_html();
		
		$html.= $this->after_answer();
		$html.= $this->after_answers();
		$html.= '</div>';
				
		$html.= '</div>';
		
		// End Echo Errors
		if( count( $errors ) > 0 ):
			$html.= '</div>';
		endif;
		
		return $html;
	}

	public function input_html(){
		return '<p>' . __( 'No input HTML given. Please check element sourcecode.', 'surveyval-locale' ) . '</p>';
	}

	public function draw_admin(){
		
		// Getting id string
		if( NULL == $this->id ): 
			// New Element
			$id_name = ' id="widget_surveyelement_##nr##"';
		else: 
			// Existing Element
			$id_name = ' id="widget_surveyelement_' . $this->id . '"';
		endif;
		
		$jquery_widget_id = str_replace( '#', '', $widget_id );
			
		/*
		 * Widget
		 */
		$html = '<div class="widget surveyelement"' . $id_name . '>';
		$html.= $this->admin_widget_head();
		$html.= $this->admin_widget_inside();
		$html.= '</div>';
		
		return $html;
	}

	private function admin_widget_head(){
		// Getting basic values for elements
		$title = empty( $this->question ) ? $this->title : $this->question;
		
		// Widget Head
		$html.= '<div class="widget-top surveyval-admin-qu-text">';
			$html.= '<div class="widget-title-action"><a class="widget-action hide-if-no-js"></a></div>';
			$html.= '<div class="widget-title">';
			
				if( '' != $this->icon ):
					$html.= '<img class="surveyval-widget-icon" src ="' . $this->icon . '" />';
				endif;
				$html.= '<h4>' . $title . '</h4>';
				
			$html.= '</div>';
		$html.= '</div>';
		
		return $html;
	}
	
	private function admin_get_widget_id(){
		// Getting Widget ID
		if( NULL == $this->id ): 
			// New Element
			$widget_id = 'widget_surveyelement_##nr##';
		else: 
			// Existing Element
			$widget_id = 'widget_surveyelement_' . $this->id;
		endif;
		
		return $widget_id;
	}

	private function admin_widget_inside(){
		$widget_id = $this->admin_get_widget_id();
		
		// Widget Inside
		$html.= '<div class="widget-inside">';
			$html.= '<div class="widget-content">';
				$html.='<div class="survey_element_tabs">';
				
					/*
					 * Tab Navi
					 */
					$html.= '<ul class="tabs">';
						// If Element is Question > Show question tab
						if( $this->is_question )
							$html.= '<li><a href="#tab_' . $jquery_widget_id . '_questions">' . __( 'Question', 'surveyval-locale' ) . '</a></li>';
						
						// If Element has settings > Show settings tab
						if( is_array( $this->settings_fields ) && count( $this->settings_fields ) > 0 )
							$html.= '<li><a href="#tab_' . $jquery_widget_id . '_settings">' . __( 'Settings', 'surveyval-locale' ) . '</a></li>';
						
						// Adding further tabs
						ob_start();
						do_action( 'surveyval_element_tabs', $html, $this );
						$html.= ob_get_clean();
					
					$html.= '</ul>';
					
					$html.= '<div class="clear tabs_underline"></div>'; // Underline of tabs
					
					/*
					 * Content of Tabs
					 */
					 
					 // Adding question HTML
					if( $this->is_question ):
						$html.= '<div id="tab_' . $jquery_widget_id . '_questions" class="tab_questions_content">';
							$html.= $this->admin_widget_question_tab();
						$html.= '</div>'; 
					endif;
					
					// Adding settings HTML
					if( is_array( $this->settings_fields ) && count( $this->settings_fields ) > 0 ):
						$html.= '<div id="tab_' . $jquery_widget_id . '_settings" class="tab_settings_content">';
							$html.= $this->admin_widget_settings_tab();
						$html.= '</div>';
					endif;
					
					// Adding action Buttons
					$bottom_buttons = apply_filters( 'sv_element_bottom_actions', array(
						'delete_survey_element' => array(
							'text' => __( 'Delete element', 'surveyval-locale' ),
							'classes' => 'delete_survey_element'
						)
					));
					
					// Adding further content
					ob_start();
					do_action( 'surveyval_element_tabs_content', $html, $this );
					$html.= ob_get_clean();
					
					$html.= $this->admin_widget_action_buttons();
					$html.= $this->admin_widget_hidden_fields();
				
				$html.= '</div>';
			$html.= '</div>';
		$html.= '</div>';
		
		return $html;
	}

	private function admin_widget_question_tab(){
		$widget_id = $this->admin_get_widget_id();
		
		// Question
		$html = '<p><input type="text" name="surveyval[' . $widget_id . '][question]" value="' . $this->question . '" class="surveyval-question" /><p>';
		
		// Answers
		if( $this->preset_of_answers ):
		
			// Answers have sections
			if( is_array( $this->sections ) && count( $this->sections ) > 0 ):
				foreach( $this->sections as $section_key => $section_name ):
					$html.= '<div class="surveyval-section" id="section_' . $section_key . '">';
					$html.= '<p>' . $section_name . '</p>';
					$html.= $this->admin_widget_question_tab_answers( $section_key );
					$html.= '<input type="hidden" name="section_key" value="' . $section_key . '" />';
					$html.= '</div>';
				endforeach;
			// Answers without sections
			else:
				$html.= '<p>' . __( 'Answer/s:', 'surveyval-locale' ) . '</p>';
				$html.= $this->admin_widget_question_tab_answers();
			endif;
		
		endif;
		
		$html.= '<div class="clear"></div>';
		
		return $html;
	}
	
	private function admin_widget_question_tab_answers( $section = NULL ){
		$widget_id = $this->admin_get_widget_id();
		
		$html = '';
		
		if( is_array( $this->answers ) ):
			
			$html.= '<div class="answers">';
			
			foreach( $this->answers AS $answer ):
				
				// If there is a section
				if( NULL != $section )
					if( $answer['section'] != $section ) // Continue if answer is not of the section
						continue;
						
				$param_arr = array();
				$param_arr[] = $this->create_answer_syntax;
				
				foreach ( $this->create_answer_params AS $param ):
					
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
				
				if( $this->preset_is_multiple )
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
			if( $this->preset_of_answers ):
				
				$param_arr[] = $this->create_answer_syntax;
				$temp_answer_id = 'id_' . time() * rand();
					
				foreach ( $this->create_answer_params AS $param ):
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
				
				if( $this->preset_is_multiple )
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
		
		if( $this->preset_is_multiple )
			$html.= '<a class="add-answer" rel="' . $widget_id . '">+ ' . __( 'Add Answer', 'surveyval-locale' ). ' </a>';
		
		return $html;
	}
	
	private function admin_widget_settings_tab(){
		$html = '';
		
		foreach( $this->settings_fields AS $name => $field ):
			$html.= $this->admin_widget_settings_tab_field( $name, $field );
		endforeach;
		
		return $html;
	}
	
	private function admin_widget_settings_tab_field( $name, $field ){
		global $wpdb, $surveyval_global;
		
		$widget_id = $this->admin_get_widget_id();
		
		$sql = $wpdb->prepare( "SELECT value FROM {$surveyval_global->tables->settings} WHERE question_id = %d AND name = %s", $this->id, $name );
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
	
	private function admin_widget_action_buttons(){
		// Adding action Buttons
		$bottom_buttons = apply_filters( 'sv_element_bottom_actions', array(
			'delete_survey_element' => array(
				'text' => __( 'Delete element', 'surveyval-locale' ),
				'classes' => 'delete_survey_element'
			)
		));
		
		$html = '<ul class="survey-element-bottom">';
		foreach( $bottom_buttons AS $button ):
			$html.= '<li><a class="' . $button[ 'classes' ] . ' survey-element-bottom-action button">' . $button[ 'text' ] . '</a></li>';
		endforeach;
		$html.= '</ul>';
		
		return $html;
	}
	
	private function admin_widget_hidden_fields(){
		$widget_id = $this->admin_get_widget_id();
		
		// Adding hidden Values for element
		$html = '<input type="hidden" name="surveyval[' . $widget_id . '][id]" value="' . $this->id . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][sort]" value="' . $this->sort . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][type]" value="' . $this->slug . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][preset_is_multiple]" value="' . ( $this->preset_is_multiple ? 'yes' : 'no' ) . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][preset_of_answers]" value="' . ( $this->preset_of_answers ? 'yes' : 'no' ) . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][sections]" value="' . ( is_array( $this->sections ) && count( $this->sections ) > 0  ? 'yes' : 'no' ) . '" />';
		
		return $html;		
	}

	private function get_response(){
		global $surveyval_survey_id;
		
		$this->response = FALSE;
		
		// Getting Session Data
		if( !isset( $_SESSION ) )
			return;
		
		// Getting value/s
		if( !empty( $surveyval_survey_id ) ):
			if( isset( $_SESSION[ 'surveyval_response' ] ) ):
				if( isset( $_SESSION[ 'surveyval_response' ][ $surveyval_survey_id ] ) ):
					if( isset( $_SESSION[ 'surveyval_response' ][ $surveyval_survey_id ][ $this->id ] ) ):
						$this->response = $_SESSION[ 'surveyval_response' ][ $surveyval_survey_id ][ $this->id ];
					endif;
				endif;
			endif;
		endif;
		
		return $this->response;
	}
	
	public function get_input_name(){
		return 'surveyval_response[' . $this->id . ']';
	}

	private function reset(){
		$this->question = '';
		$this->answers = array();
	}
}

/**
 * Register a new Group Extension.
 *
 * @param string Name of the element type class.
 * @return bool|null Returns false on failure, otherwise null.
 */
function sv_register_survey_element( $element_type_class ) {
	if ( ! class_exists( $element_type_class ) )
		return false;
	
	// Register the group extension on the bp_init action so we have access
	// to all plugins.
	add_action( 'init', create_function( '', '
		$extension = new ' . $element_type_class . ';
		add_action( "init", array( &$extension, "_register" ), 2 );
	' ), 1 );
}