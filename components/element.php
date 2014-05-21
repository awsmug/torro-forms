<?php

abstract class SurveyVal_SurveyElement{
	var $id;
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
		$html.= $this->before_question();
		$html.= '<h5>' . $this->question . '</h5>';
		$html.= $this->after_question();
		
		$this->get_response();
		
		if( !$this->preset_of_answers ):
			/*
			 * On simple input
			 */
			$html.= '<div class="answer">';
			$html.= $this->before_answers();
			$html.= $this->before_answer();
			
			$html.= $this->input_html();
			
			$html.= $this->after_answer();
			$html.= $this->after_answers();
			$html.= '</div>';
				
		else:
			/*
			 * With preset of answers
			 */
			 
			$html.= $this->before_answers();
			 
			foreach( $this->answers AS $answer ):
				$html.= $this->before_answer();
				
				$html.= $this->input_html();
				
				$html.= $this->after_answer();
			endforeach;
			
			$html.= $this->after_answers();
		endif;
		
		$html.= '</div>';
		
		// End Echo Errors
		if( count( $errors ) > 0 ):
			$html.= '</div>';
		endif;
		
		return $html;
	}

	public function get_response(){
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

	public function input_html(){
		return __( 'No input HTML given. Please check element source.', 'surveyval-locale' );
	}
	
	public function get_element(){
		return FALSE;
	}
	
	public function show(){
		return FALSE;
	}
	
	public function save(){
		return FALSE;
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