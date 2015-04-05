<?php
/**
* @package WordPress
* @subpackage Questions
* @author Sven Wagener
* @copyright 2015, awesome.ug
* @link http://awesome.ug
* @license http://www.opensource.org/licenses/gpl-2.0.php GPL License
*/

// No direct access is allowed
if( ! defined( 'ABSPATH' ) ) exit;

class Questions_SurveyElement_OneChoice extends Questions_SurveyElement{
	
	public function __construct( $id = NULL ){
		$this->slug = 'OneChoice';
		$this->title = __( 'One Choice', 'questions-locale' );
		$this->description = __( 'Add a question which can be answered by selecting one of the given answers.', 'questions-locale' );
		$this->icon = QUESTIONS_URLPATH . '/assets/images/icon-onechoice.png';
		
		$this->preset_of_answers = TRUE;
		$this->preset_is_multiple = TRUE;
		$this->answer_is_multiple = FALSE;
		$this->is_displayable = TRUE;
		
		$this->create_answer_syntax = '<p><input type="text" name="%s" value="%s" /></p>';
		$this->create_answer_params = array( 'name', 'answer' );
		
		parent::__construct( $id );
	}
	
	public function input_html(){
		if( !is_array( $this->answers )  && count( $this->answers ) == 0 )
			return '<p>' . __( 'You don´t entered any answers. Please add some to display answers here.', 'questions-locale' ) . '</p>';
		
		$html = '';
		foreach( $this->answers AS $answer ):
			$checked = '';
			if( $this->response == $answer[ 'text' ] )
				$checked = ' checked="checked"';
				
			$html.= '<p><input type="radio" name="' . $this->get_input_name() . '" value="' . $answer[ 'text' ] . '" ' . $checked . '/> ' . $answer[ 'text' ] .'</p>';
		endforeach;
		
		return $html;
	}
	
	public function settings_fields(){
		$this->settings_fields = array(
			'description' => array(
				'title'			=> __( 'Description', 'questions-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'The description will be shown after the question.', 'questions-locale' ),
				'default'		=> ''
			)
		);
	}

	public function validate( $input ){
		$error = FALSE;
		
		if( empty( $input ) ):
			$this->validate_errors[] = sprintf( __( 'Please select a value.', 'questions-locale' ) );
			$error = TRUE;
		endif;
		
		if( $error ):
			return FALSE;
		endif;
		
		return TRUE;
	}
}
qu_register_survey_element( 'Questions_SurveyElement_OneChoice' );