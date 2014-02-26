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

class SurveyVal_SurveyElement_Separator extends SurveyVal_SurveyElement{
	
	public function __construct( $id = null ){
		$this->slug = 'Separator';
		$this->title = __( 'Separator', 'surveyval-locale' );
		$this->description = __( 'Adds a optical separator (<hr>) between questions.', 'surveyval-locale' );
		$this->icon = SURVEYVAL_URLPATH . '/assets/images/icon-separator.png';
		$this->is_question = FALSE;
		
		parent::__construct( $id );
	}
	
	public function settings_fields(){
		$this->settings_fields = array(
			'header' => array(
				'title'			=> __( 'Headline', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'Text which will be shown above the separator', 'surveyval-locale' ),
				'default'		=> ''
			)
		);
	}
	
	public function get_html(){
		$html = '<div class="survey-element survey-element-' . $this->id . '">';
		
		if( !empty( $this->settings['header'] ) )
			$html.= '<h3>' . $this->settings['header'] . '</h3>';
			
		$html.= '<hr /></div>';
		
		return $html;
	}
}
sv_register_survey_element( 'SurveyVal_SurveyElement_Separator' );






