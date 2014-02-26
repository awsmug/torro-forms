<?php

abstract class SurveyVal_SurveyElement{
	var $id;
	var $slug;
	var $title;
	var $description;
	var $icon;
	var $sort = 0;
	var $is_question = TRUE;

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
		
		if( $this->is_question ):
			add_filter( 'surveyval_before_answer_' . $this->slug, array( $this, 'before_answer' ), 10, 3 );
			add_filter( 'surveyval_after_answer_' . $this->slug, array( $this, 'after_answer' ), 10, 3 );
		endif;
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
		
		if( array_key_exists( $this->slug, $surveyval_global->question_types ) )
			return FALSE;
		
		if( !is_array( $surveyval_global->question_types ) )
			$surveyval_global->question_types = array();
		
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
				$this->add_answer( $result->answer, $result->sort, $result->id );
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
	
	private function add_answer( $text, $sort = FALSE, $id = null ){ 
		if( '' == $text )
			return FALSE;
		
		if( FALSE == $this->preset_is_multiple && count( $this->answers ) > 0 )
			return FALSE;
		
		$this->answers[ $id ] = array(
			'id' => $id,
			'text' => $text,
			'sort' => $sort
		);
	}
	
	private function add_settings( $name, $value ){ 
		$this->settings[ $name ] = $value;
	}
	
	public function get_settings_html( $new = FALSE ){
		if( !$new )
			$widget_id = 'widget_surveyelement_' . $this->id;
		else
			$widget_id = 'widget_surveyelement_##nr##';
		
		$jquery_widget_id = str_replace( '#', '', $widget_id );
		
		$html ='<div class="survey_element_tabs">';
		
		$html.= '<ul class="tabs">';
			if( $this->is_question )
				$html.= '<li><a href="#tab_' . $jquery_widget_id . '_questions">' . __( 'Question', 'surveyval-locale' ) . '</a></li>';
			
			if( is_array( $this->settings_fields ) && count( $this->settings_fields ) > 0 ):
				$html.= '<li><a href="#tab_' . $jquery_widget_id . '_settings">' . __( 'Settings', 'surveyval-locale' ) . '</a></li>';
			endif;
		$html.= '</ul>';
		
		$html.= '<div class="clear tabs_underline"></div>';
		
		if( $this->is_question ):
			$html.= '<div id="tab_' . $jquery_widget_id . '_questions" class="tab_questions_content">';
				$html.= $this->get_admin_question_tab_html( $widget_id, $new );
			$html.= '</div>'; 
		endif;
		
		if( is_array( $this->settings_fields ) && count( $this->settings_fields ) > 0 ):
			$html.= '<div id="tab_' . $jquery_widget_id . '_settings" class="tab_settings_content">';
				$html.= $this->get_admin_settings_tab_html( $widget_id, $new );
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
		
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][id]" value="' . $this->id . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][sort]" value="' . $this->sort . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][type]" value="' . $this->slug . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][preset_is_multiple]" value="' . ( $this->preset_is_multiple ? 'yes' : 'no' ) . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][preset_of_answers]" value="' . ( $this->preset_of_answers ? 'yes' : 'no' ) . '" />';
	
		$html.= '</div>'; 
		
		return $html;
	}

	private function get_admin_question_tab_html( $widget_id, $new ){
		$html = '<p><input type="text" name="surveyval[' . $widget_id . '][question]" value="' . $this->question . '" class="surveyval-question" /><p>';
	
		$i = 0;
		
		if( $this->preset_of_answers ):
			
			$html.= '<p>' . __( 'Answer/s:', 'surveyval-locale' ) . '</p>';
			
			if( is_array( $this->answers ) && !$new ):
				
				$html.= '<div class="answers">';
				
				foreach( $this->answers AS $answer ):
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
					$html.= ' <input type="button" value="' . __( 'Delete', 's
					urveyval-locale' ) . '" class="delete_answer button answer_action">';
					$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][answers][id_' . $answer['id'] . '][id]" value="' . $answer['id'] . '" />';
					$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][answers][id_' . $answer['id'] . '][sort]" value="' . $answer['sort'] . '" />';
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
					$html.= '</div>';
					$html.= '</div><div class="clear"></div>';
					
				endif;
				
			endif;
			
			if( $this->preset_is_multiple )
				$html.= '<a class="add-answer" rel="' . $widget_id . '">+ ' . __( 'Add Answer', 'surveyval-locale' ). ' </a>';
		
		endif;
		
		$html.= '<div class="clear"></div>';
		
		return $html;
	}

	private function get_admin_settings_tab_html( $widget_id, $new ){
		$html = '';
		foreach( $this->settings_fields AS $name => $field ):
			$html.=$this->get_settings_field_html( $name, $field, $widget_id );
		endforeach;
		
		return $html;
	}
	
	private function get_settings_field_html( $name, $field, $widget_id ){
		global $wpdb, $surveyval_global;
		
		$sql = $wpdb->prepare( "SELECT value FROM {$surveyval_global->tables->settings} WHERE question_id = %d AND name = %s", $this->id, $name );
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
	
	public function get_html(){
		if( '' == $this->question && $this->is_question )
			return FALSE;
		
		if( 0 == count( $this->answers )  && $this->preset_of_answers == TRUE )
			return FALSE;
		
		$error_css = '';
		
		if( $this->error )
			$error_css = ' survey-element-error';
		
		$html = '<div class="survey-element survey-element-' . $this->id . $error_css . '">';
		$html.= '<h5>' . $this->question . '</h5>';
		
		if( !$this->preset_of_answers ):
			/*
			 * On simple input
			 */
			$param_arr = array();
			$param_arr[] = $this->answer_syntax;
				
			foreach( $this->answer_params AS $param ):
				switch( $param ){
					case 'name':
						$param_value = 'surveyval_response[' . $this->id . ']';
						break;
						
					case 'value':
						$param_value = $this->response;
						break;
						
					case 'answer';
						$param_value = $answer['text'];
						break;
				}
				$param_arr[] = $param_value;			
			endforeach;
			
			$html.= '<div class="answer">';
			$html = apply_filters( 'surveyval_before_answer_' . $this->slug, $html, $this->slug, $this->id );
			$html.= call_user_func_array( 'sprintf', $param_arr );
			$html = apply_filters( 'surveyval_after_answer_' . $this->slug, $html, $this->slug, $this->id );
			$html.= '</div>';
			
		else:
			/*
			 * With preset of answers
			 */
			foreach( $this->answers AS $answer ):
				$param_arr = array();
				
				// Is answer selected choose right syntax
				if( $this->answer_is_multiple ):
					
					if( is_array( $this->response ) && in_array( $answer['text'], $this->response ) ):
						$param_arr[] = $this->answer_selected_syntax;
					else:
						$param_arr[] = $this->answer_syntax;
					endif;
					
				else:
					
					if( $this->response == $answer['text'] && !empty( $this->answer_selected_syntax ) ):
						$param_arr[] = $this->answer_selected_syntax;
					else:
						$param_arr[] = $this->answer_syntax;
					endif;
					
				endif;
				
				// Running every parameter for later calling
				foreach( $this->answer_params AS $param ):
					switch( $param ){
						
						case 'name':
							if( $this->answer_is_multiple )
								$param_value = 'surveyval_response[' . $this->id . '][]';
							else
								$param_value = 'surveyval_response[' . $this->id . ']';
								
							break;
							
						case 'value':
							$param_value = $answer['text'];
							break;
							
						case 'answer';
							$param_value = $answer['text'];
							break;
					}
					$param_arr[] = $param_value;			
				endforeach;
				
				$html.= '<div class="answer">';
				$html = apply_filters( 'surveyval_before_answer', $html, $this->slug, $this->id );
				$html.= call_user_func_array( 'sprintf', $param_arr );
				$html = apply_filters( 'surveyval_after_answer', $html, $this->slug, $this->id );
				$html.= '</div>';
					
				// $html.= '<pre>' . print_r( $answer, TRUE ) . '</pre>';
				// $html.= sprintf( $this->answer_syntax, $answer, $this->slug );
			endforeach;
		endif;
		
		$html.= '</div>';
		
		return $html;
	}

	public function before_answer( $html, $question_slug, $question_id ){
		return $html;
	}
	
	public function after_answer( $html, $question_slug, $question_id ){
		return $html;
	}

	public function settings_fields(){
	}

	public function validate( $input ){
		return TRUE;
	}
	
	private function reset(){
		$this->question = '';
		$this->answers = array();
	}
}

/**
 * Register a new Group Extension.
 *
 * @param string Name of the Question type class.
 * @return bool|null Returns false on failure, otherwise null.
 */
function sv_register_survey_element( $question_type_class ) {
	if ( ! class_exists( $question_type_class ) )
		return false;
	
	// Register the group extension on the bp_init action so we have access
	// to all plugins.
	add_action( 'init', create_function( '', '
		$extension = new ' . $question_type_class . ';
		add_action( "init", array( &$extension, "_register" ), 2 );
	' ), 1 );
}