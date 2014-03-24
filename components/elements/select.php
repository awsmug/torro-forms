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

class SurveyVal_SurveyElement_Select extends SurveyVal_SurveyElement{
	
	public function __construct( $id = null ){
		$this->slug = 'Select';
		$this->title = __( 'Select', 'surveyval-locale' );
		$this->description = __( 'Add a question which can be answered within a select field.', 'surveyval-locale' );
		$this->icon = SURVEYVAL_URLPATH . '/assets/images/icon-select.png';
		
		$this->preset_of_answers = TRUE;
		$this->preset_is_multiple = TRUE;
		$this->answer_is_multiple = TRUE;
		
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
	
	public function get_element(){
		$html = '<div class="question question_' . $this->id . $error_css . '">';
		$html.= $this->before_question();
		$html.= '<h5>' . $this->question . '</h5>';
		$html.= $this->after_question();
		
		echo '<pre>';
		print_r( $this );
		echo '</pre>';
		
		$html.= '<select name="surveyval_response[' . $this->id . ']">';
		
		if( is_array( $this->answers ) && count( $this->answers ) > 0 ):
			foreach( $this->answers AS $value ):
				$html.= '<option value="' . $value['id'] . '">' .  $value['text'] . '</option>';
			endforeach;
		endif;
		
		$html.= '</select>';
		
		$html.= '</div>';
		
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
}
sv_register_survey_element( 'SurveyVal_SurveyElement_Select' );






