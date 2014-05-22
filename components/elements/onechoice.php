<?php
/**
* @package WordPress
* @subpackage SurveyVal
* @author Sven Wagener
* @copyright 2014, Rheinschmiede
* @link http://rheinschmiede.de
* @license http://www.opensource.org/licenses/gpl-2.0.php GPL License
*/

// No direct access is allowed
if( ! defined( 'ABSPATH' ) ) exit;

class SurveyVal_SurveyElement_OneChoice extends SurveyVal_SurveyElement{
	
	public function __construct( $id = NULL ){
		$this->slug = 'OneChoice';
		$this->title = __( 'One Choice', 'surveyval-locale' );
		$this->description = __( 'Add a question which can be answered by selecting one of the given answers.', 'surveyval-locale' );
		$this->icon = SURVEYVAL_URLPATH . '/assets/images/icon-onechoice.png';
		
		$this->preset_of_answers = TRUE;
		$this->preset_is_multiple = TRUE;
		$this->answer_is_multiple = FALSE;
		
		$this->create_answer_syntax = '<p><input type="text" name="%s" value="%s" /></p>';
		$this->create_answer_params = array( 'name', 'answer' );
		
		parent::__construct( $id );
	}
	
	public function input_html(){
		if( !is_array( $this->answers )  && count( $this->answers ) == 0 )
			return '<p>' . __( 'You don´t entered any answers. Please add some to display answers here.', 'surveyval-locale' ) . '</p>';
		
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
				'title'			=> __( 'Description', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'The description will be shown after the question.', 'surveyval-locale' ),
				'default'		=> ''
			)
		);
	}
}
sv_register_survey_element( 'SurveyVal_SurveyElement_OneChoice' );