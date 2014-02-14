<?php

abstract class SurveyVal_QuestionType{
	var $id;
	var $slug;
	var $title;
	var $description;
	var $icon;
	var $sort = 0;

	var $survey_id;
	
	var $question;
	var $response;
	var $error = FALSE;
	
	var $preset_of_answers = FALSE;
	var $preset_is_multiple = FALSE;
	var $answer_is_multiple = FALSE;
	
	var $answers = array();
	
	var $answer_params = array();
	var $answer_syntax;
	var $answer_selected_syntax;
	
	var $validate_errors = array();
	
	var $create_answer_params = array();
	var $create_answer_syntax;
	
	var $settings_fields = array();

	var $initialized = FALSE;
	
	public function __construct( $id = null ){
		if( null != $id && '' != $id )
			$this->populate( $id );
	}	
	
	public function _register() {
		global $surveyval;
		
		if( TRUE == $this->initialized )
			return FALSE;
		
		if( !is_object( $surveyval ) )
			return FALSE;
		
		if( '' == $this->slug )
			$this->slug = get_class( $this );
		
		if( '' == $this->title )
			$this->title = ucwords( get_class( $this ) );
		
		if( '' == $this->description )
			$this->description =  __( 'This is a SurveyVal Question-Type.', 'surveyval-locale' );
		
		if( array_key_exists( $this->slug, $surveyval->question_types ) )
			return FALSE;
		
		if( !is_array( $surveyval->question_types ) )
			$surveyval->question_types = array();
		
		$this->initialized = TRUE;
		
		return $surveyval->add_question_type( $this->slug, $this );
	}
	
	private function populate( $id ){
		global $wpdb, $surveyval;
		
		$this->reset();
		
		$sql = $wpdb->prepare( "SELECT * FROM {$surveyval->tables->questions} WHERE id = %s", $id );
		$row = $wpdb->get_row( $sql );
		
		$this->id = $id;
		$this->set_question( $row->question );
		$this->surveyval_id = $row->surveyval_id;
		
		$this->sort = $row->sort;
		
		$sql = $wpdb->prepare( "SELECT * FROM {$surveyval->tables->answers} WHERE question_id = %s ORDER BY sort ASC", $id );
		$results = $wpdb->get_results( $sql );
				
		if( is_array( $results ) ):
			foreach( $results AS $result ):
				$this->add_answer( $result->answer, $result->sort, $result->id );
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
	
	public function get_settings_html( $new = FALSE ){
		if( !$new )
			$widget_id = 'widget_question_' . $this->id;
		else
			$widget_id = 'widget_question_##nr##';
		
		$html = '<p>' . __( 'Your Question:', 'surveyval-locale' ) . '</p><p><input type="text" name="surveyval[' . $widget_id . '][question]" value="' . $this->question . '" class="surveyval-question" /><p>';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][id]" value="' . $this->id . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][sort]" value="' . $this->sort . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][type]" value="' . $this->slug . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][preset_is_multiple]" value="' . ( $this->preset_is_multiple ? 'yes' : 'no' ) . '" />';
		$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][preset_of_answers]" value="' . ( $this->preset_of_answers ? 'yes' : 'no' ) . '" />';
		
		$i = 0;
		
		if( $this->preset_of_answers ):
			
			$html.= '<p>' . __( 'Your Answer/s:', 'surveyval-locale' ) . '</p>';
			
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
					$html.= ' <input type="button" value="' . __( 'Delete', 'surveyval-locale' ) . '" class="delete_answer button answer_action">';
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
		
		$bottom_buttons = apply_filters( 'sv_question_bottom_actions', array(
			'delete_question' => array(
				'text' => __( 'Delete question', 'surveyval-locale' ),
				'classes' => 'delete_question'
			)
		));
		
		$html.= '<ul class="question-bottom">';
		foreach( $bottom_buttons AS $button ):
			$html.= '<li><a class="' . $button[ 'classes' ] . ' question-bottom-action button">' . $button[ 'text' ] . '</a></li>';
		endforeach;
		$html.= '</ul>';
		
		$html.= '<div class="clear"></div>';
		
		$html = apply_filters( 'sv_question_html', $html );
		
		return $html;
	}
	
	public function get_html(){
		if( '' == $this->question )
			return FALSE;
		
		if( 0 == count( $this->answers )  && $this->preset_of_answers == TRUE )
			return FALSE;
		
		if( $this->error )
			$error_css = ' question_error';
		
		$html = '<div class="question question_' . $this->id . $error_css . '">';
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
			
			$html.= '<div class="answer">' . call_user_func_array( 'sprintf', $param_arr ) . '</div>';
			
		else:
			/*
			 * With preset of answers
			 */
			foreach( $this->answers AS $answer ):
				$param_arr = array();
				
				// Is answer selected choose right syntax
				if( $this->answer_is_multiple ):
					if( in_array( $answer['text'], $this->response ) ):
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
				
				$html.= '<div class="answer">' . call_user_func_array( 'sprintf', $param_arr ) . '</div>';
					
				// $html.= '<pre>' . print_r( $answer, TRUE ) . '</pre>';
				// $html.= sprintf( $this->answer_syntax, $answer, $this->slug );
			endforeach;
		endif;
		
		$html.= '</div>';
		
		return $html;
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
function sv_register_question_type( $question_type_class = '' ) {
	if ( ! class_exists( $question_type_class ) )
		return false;
	
	// Register the group extension on the bp_init action so we have access
	// to all plugins.
	add_action( 'init', create_function( '', '
		$extension = new ' . $question_type_class . ';
		add_action( "init", array( &$extension, "_register" ), 2 );
	' ), 1 );
}