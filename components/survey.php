<?php

class SurveyVal_Survey{
	public $id;
	public $title;
	
	public $elements = array();
	public $response_errors = array();
	public $splitter_count = 0;
	
	public function __construct( $id = null ){
		if( null != $id )
			$this->populate( $id );
	}
	
	private function populate( $id ){
		global $wpdb, $surveyval_global;
		
		$this->reset();
		
		$survey = get_post( $id );
		
		$this->id = $id;
		$this->title = $survey->post_title;
		
		$this->elements = $this->get_elements( $id );
	}
	
	private function get_elements( $id = null ){
		global $surveyval_global, $wpdb;
		
		if( null == $id )
			$id = $this->id;
		
		if( '' == $id )
			return FALSE;
		
		$sql = $wpdb->prepare( "SELECT * FROM {$surveyval_global->tables->questions} WHERE surveyval_id = %s ORDER BY sort ASC", $id );
		$results = $wpdb->get_results( $sql );
		
		$elements = array();
		
		if( is_array( $results ) ):
			foreach( $results AS $result ):
				if( class_exists( 'SurveyVal_SurveyElement_' . $result->type ) ):
					$class = 'SurveyVal_SurveyElement_' . $result->type;
					$object = new $class( $result->id );
					$elements[] = $object;
					
					if( $object->splitter ):
						$this->splitter_count++;
					endif;
				else:
					// If class do not exist -> Put in Error message here				
				endif;
			endforeach;
		endif;
		
		return $elements;
	}
	
	private function add_element( $element, $element_type, $order = null ){
		global $surveyval_global;
		
		if( !array_key_exists( $element_type, $surveyval_global->element_types ) )
			return FALSE;
		
		$class = 'SurveyVal_SurveyElement_' . $element_type;
		
		if( null == $element_id )
			$object = new $class();
		else
			$object = new $class( $element_id );
		
		$object->element( $element, $order );
		
		if( count( $answers ) > 0 )
			foreach( $answers AS $answer )
				$object->answer( $answer['text'], $answer['order'], $answer['id'] );
			
		
		if( !$this->add_element_obj( $object, $order ) ):
			return FALSE;
		else:
			
		endif;
	}
	
	private function add_element_obj( $element_object, $order = null ){
		if( !is_object( $element_object ) || 'SurveyVal_SurveyElement' != get_parent_class( $element_object ) )
			return FALSE;
		
		if( null == $order )
			$order = count( $this->elements );
		
		$this->elements[$order] = $element_object;
		
		return TRUE;
	}
	
	// Need to be here?
	public function participated_polls( $user_id = NULL ){
		global $wpdb, $current_user, $surveyval_global;
		
		if( '' == $user_id ):
			get_currentuserinfo();
			$user_id = $user_id = $current_user->ID;
		endif;
		
		$sql = $wpdb->prepare( "SELECT id FROM {$surveyval_global->tables->responds} WHERE  user_id=%s", $user_id );
		return $wpdb->get_col( $sql );
	}
	
	private function reset(){
		$this->elements = array();
	}
}





