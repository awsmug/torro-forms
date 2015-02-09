<?php
/**
* @package WordPress
* @subpackage Questions
* @author Sven Wagener
* @copyright 2014, Rheinschmiede
* @link http://rheinschmiede.de
* @license http://www.opensource.org/licenses/gpl-2.0.php GPL License
*/

// No direct access is allowed
if( ! defined( 'ABSPATH' ) ) exit;

class Questions_SurveyElement_Splitter extends Questions_SurveyElement{
	
	public function __construct( $id = null ){
		$this->slug = 'Splitter';
		$this->title = __( 'Split Form', 'questions-locale' );
		$this->description = __( 'Splits a form into several steps', 'questions-locale' );
		$this->icon = QUESTIONS_URLPATH . '/assets/images/icon-split-form.png';
		
		$this->is_question = FALSE;
		$this->splitter = TRUE;
		
		parent::__construct( $id );
	}
	
	public function settings_fields(){
	}
	
	public function get_html(){
	}
}
qu_register_survey_element( 'Questions_SurveyElement_Splitter' );






