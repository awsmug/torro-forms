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

class Questions_SurveyElement_Separator extends Questions_SurveyElement{
	
	public function __construct( $id = null ){
		$this->slug = 'Separator';
		$this->title = __( 'Separator', 'questions-locale' );
		$this->description = __( 'Adds a optical separator (<hr>) between questions.', 'questions-locale' );
		$this->icon = QUESTIONS_URLPATH . '/assets/images/icon-separator.png';
		
		$this->is_question = FALSE;
		
		parent::__construct( $id );
	}
	
	public function input_html(){
		$html = '<div class="survey-element survey-element-' . $this->id . '">';
		
		if( !empty( $this->settings['header'] ) )
			$html.= '<h3>' . $this->settings['header'] . '</h3>';
			
		$html.= '<hr /></div>';
		
		return $html;
	}
	
	public function settings_fields(){
		$this->settings_fields = array(
			'header' => array(
				'title'			=> __( 'Headline', 'questions-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'Text which will be shown above the separator', 'questions-locale' ),
				'default'		=> ''
			)
		);
	}
}
qu_register_survey_element( 'Questions_SurveyElement_Separator' );






