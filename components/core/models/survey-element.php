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
	
	public function get_element(){
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