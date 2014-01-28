<?php

abstract class SurveyVal_QuestionType{
	var $slug;
	var $title;
	var $description;
	var $icon;
	
	var $question;
	
	var $has_answers = TRUE;
	var $multiple_answers = FALSE;
	
	var $answers = array();
	var $answer_order = 0;
	
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
	
	public function populate( $id ){
		global $wpdb, $surveyval;
		
		$this->reset();
		
		$sql = $wpdb->prepare( "SELECT * FROM {$surveyval->tables->questions} WHERE id = %s", $id );
		$row = $wpdb->get_row( $sql );
		
		$this->question = $row->question;
		
		$sql = $wpdb->prepare( "SELECT * FROM {$surveyval->tables->answers} WHERE question_id = %s", $id );
		$results = $wpdb->get_results( $sql );
				
		if( is_array( $results ) ):
			foreach( $results AS $result ):
				$this->answers[] = $result->answer;
			endforeach;
		endif;
	}
	
	public function add_question( $question ){
		if( '' == $question )
			return FALSE;
		
		if( '' != $this->question )
			return FALSE;
		
		$this->question = $question;
		
		return TRUE;
	}
	
	public function add_answer( $text, $order = FALSE ){ 
		if( '' == $text )
			return FALSE;
		
		if( FALSE == $this->multiple_answers && count( $this->answers ) > 0 )
			return FALSE;
		
		if( FALSE == $order )
			$order = $this->answer_order++;
		
		$this->answers[ $order ] = $text;
	}
	
	public function settings_html(){
		$html = '<p>' . __( 'Your Question:', 'surveyval-locale' ) . '</p><p><input type="text" name="surveyval[][question]" value="' . $this->question . '" class="surveyval-question" /><p>';
		$i = 0;
		
		if( TRUE != $this->has_answers )
			return $html;
			
		$html.= '<p>' . __( '', 'surveyval-locale' );
		
		if( is_array( $this->answers ) ):
			foreach( $this->answers AS $answer ):
				$param_arr = array();
				$param_arr[] = $this->create_answer_syntax;
				
				foreach ( $this->create_answer_params AS $param ):
					
					switch( $param ){
						case 'name':
							if( $this->multiple_answers )
								$param_value = 'surveyval[]';
							else
								$param_value = 'surveyval[][' . $i++ . ']';
							break;
							
						case 'value':
							$param_value = $value;
							break;
							
						case 'answer';
							$param_value = $answer;
							break;
					}
					$param_arr[] = $param_value;
				endforeach;
				
				$html.= call_user_func_array( 'sprintf', $param_arr );
				
			endforeach;
		endif;
		
		return $html;
	}
	
	public function html(){
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
	
	public function reset(){
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