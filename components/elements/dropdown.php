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

class SurveyVal_SurveyElement_Dropdown extends SurveyVal_SurveyElement{
	
	public function __construct( $id = null ){
		$this->slug = 'Dropdown';
		$this->title = __( 'Dropdown', 'surveyval-locale' );
		$this->description = __( 'Add a question which can be answered within a dropdown field.', 'surveyval-locale' );
		$this->icon = SURVEYVAL_URLPATH . '/assets/images/icon-dropdown.png';
		
		$this->preset_of_answers = TRUE;
		$this->preset_is_multiple = TRUE;
		$this->answer_is_multiple = FALSE;
		
		$this->answer_syntax = '<option value="%s" /> %s</option>';
		$this->answer_selected_syntax = '<option value="%s" selected="selected" /> %s</option>';
		$this->answer_params = array( 'value', 'answer' );
		
		$this->create_answer_syntax = '<p><input type="text" name="%s" value="%s" /></p>';
		$this->create_answer_params = array( 'name', 'answer' );
		
		parent::__construct( $id );
	}
	
	public function settings_fields(){
		$this->settings_fields = array(
			'description' => array(
				'title'			=> __( 'Description', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'The description will be shown after the question.', 'surveyval-locale' ),
				'default'		=> ''
			),
		);
	}
	
	public function before_answers(){
		$html = '<select name="surveyval_response[' . $this->id . ']">';
		$html.= '<option value="please-select"> - ' . __( 'Please select', 'surveyval-locale' ) . ' -</option>';
		return $html;
	}
	
	public function after_answers(){
		$html = '</select>';
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


	public function validate( $input ){
		$error = FALSE;
		
		if( 'please-select' == $input ):
			$this->validate_errors[] = sprintf( __( 'Please select a value.', 'surveyval-locale' ) );
			$error = TRUE;
		endif;
		
		if( $error ):
			return FALSE;
		endif;
		
		return TRUE;
	}

	
}
sv_register_survey_element( 'SurveyVal_SurveyElement_Dropdown' );






