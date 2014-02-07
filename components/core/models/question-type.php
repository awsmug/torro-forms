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
	
	var $has_answers = TRUE;
	var $multiple_answers = FALSE;
	
	var $answers = array();
	
	var $answer_params = array();
	var $answer_syntax;
	
	var $create_answer_params = array();
	var $create_answer_syntax;

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
		
		if( '' == $this->multiple_answers )
			$this->multiple_answers = FALSE;
		
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
		
		if( FALSE == $this->multiple_answers && count( $this->answers ) > 0 )
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
		
		$i = 0;
		
		if( !$this->has_answers )
			return $html;
			
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
				
				if( $this->multiple_answers )
					$answer_classes = ' multiple_answer';
				
				$html.= '<div class="answer' . $answer_classes .'" id="answer_' . $answer['id'] . '">';
				$html.= call_user_func_array( 'sprintf', $param_arr );
				$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][answers][id_' . $answer['id'] . '][id]" value="' . $answer['id'] . '" />';
				$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][answers][id_' . $answer['id'] . '][sort]" value="' . $answer['sort'] . '" />';
				$html.= '</div>';
				
			endforeach;
			
			$html.= '</div>';
			
			if( $this->multiple_answers ):
				$html.= ' <a class="add-answer" rel="' . $widget_id . '">[+] ' . __( 'Add Answer', 'surveyval-locale' ). '</a>';
			endif;
			
		else:
			if( $this->has_answers ):
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
				
				if( $this->multiple_answers )
					$answer_classes = ' multiple_answer';
				
				$html.= '<div class="answers">';
				$html.= '<div class="answer ' . $answer_classes .'" id="answer_' . $temp_answer_id . '">';
				$html.= call_user_func_array( 'sprintf', $param_arr );
				$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][answers][' . $temp_answer_id . '][id]" value="" />';
				$html.= '<input type="hidden" name="surveyval[' . $widget_id . '][answers][' . $temp_answer_id . '][sort]" value="0" />';
				$html.= '</div>';
				$html.= '</div>';
				
				if( $this->multiple_answers )
					$html.= ' <a class="add-answer" rel="' . $widget_id . '">[+] ' . __( 'Add Answer', 'surveyval-locale' ). ' </a>';
				
			endif;
			
		endif;
		
		return $html;
	}
	
	public function get_html(){
		if( '' == $this->question )
			return FALSE;
		
		if( 0 == count( $this->answers )  && $this->has_answers == TRUE )
			return FALSE;
		
		$html = '<p>' . $this->question . '<p>';
		
		foreach( $this->answers AS $answer ):
			$html.= sprintf( $this->answer_syntax, $answer, $this->slug );
		endforeach;
		
		echo $html;
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