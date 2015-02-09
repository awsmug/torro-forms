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

class Questions_SurveyElement_Description extends Questions_SurveyElement{
	
	public function __construct( $id = null ){
		$this->slug = 'Description';
		$this->title = __( 'Description', 'questions-locale' );
		$this->description = __( 'Adds a text to the form.', 'questions-locale' );
		$this->icon = QUESTIONS_URLPATH . '/assets/images/icon-text.png';
		
		$this->is_question = FALSE;
		
		parent::__construct( $id );
	}
	
	public function input_html(){
		$html = '<div class="survey-element survey-element-' . $this->id . '">';
		$html.= '<div class="survey-description">' . $this->settings['description'] . '</div>';
		$html.= '</div>';
		
		return $html;
	}
	
	public function settings_fields(){
		$this->settings_fields = array(
			'description' => array(
				'title'			=> __( 'Text to show', 'questions-locale' ),
				'type'			=> 'textarea',
				'description' 	=> __( 'The text which will be shown in the form.', 'questions-locale' ),
				'default'		=> ''
			)
		);
	}
}
qu_register_survey_element( 'Questions_SurveyElement_Description' );






