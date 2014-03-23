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

class SurveyVal_SurveyElement_Description extends SurveyVal_SurveyElement{
	
	public function __construct( $id = null ){
		$this->slug = 'Description';
		$this->title = __( 'Description', 'surveyval-locale' );
		$this->description = __( 'Adds a text to the form.', 'surveyval-locale' );
		$this->icon = SURVEYVAL_URLPATH . '/assets/images/icon-separator.png';
		
		$this->is_question = FALSE;
		
		parent::__construct( $id );
	}
	
	public function settings_fields(){
		$this->settings_fields = array(
			'description' => array(
				'title'			=> __( 'Text to show', 'surveyval-locale' ),
				'type'			=> 'textarea',
				'description' 	=> __( 'The text which will be shown in the form.', 'surveyval-locale' ),
				'default'		=> ''
			)
		);
	}
	
	public function get_element(){
		$html = '<div class="survey-element survey-element-' . $this->id . '">';
		$html.= '<div class="survey-description">' . $this->settings['description'] . '</div>';
		$html.= '</div>';
		
		return $html;
	}
}
sv_register_survey_element( 'SurveyVal_SurveyElement_Description' );






