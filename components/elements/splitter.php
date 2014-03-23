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

class SurveyVal_SurveyElement_Splitter extends SurveyVal_SurveyElement{
	
	public function __construct( $id = null ){
		$this->slug = 'Splitter';
		$this->title = __( 'Split Form', 'surveyval-locale' );
		$this->description = __( 'Splits a form into several steps', 'surveyval-locale' );
		$this->icon = SURVEYVAL_URLPATH . '/assets/images/icon-separator.png';
		
		$this->is_question = FALSE;
		$this->splitter = TRUE;
		
		parent::__construct( $id );
	}
	
	public function settings_fields(){
	}
	
	public function get_html(){
	}
}
sv_register_survey_element( 'SurveyVal_SurveyElement_Splitter' );






