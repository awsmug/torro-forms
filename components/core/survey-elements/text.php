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

class SurveyVal_SurveyElement_Text extends SurveyVal_SurveyElement{
	
	public function __construct( $id = null ){
		$this->slug = 'Text';
		$this->title = __( 'Text', 'surveyval-locale' );
		$this->description = __( 'Add a question which can be answered within a text field.', 'surveyval-locale' );
		$this->icon = SURVEYVAL_URLPATH . '/assets/images/icon-textfield.png';
		
		$this->answer_syntax = '<p><input type="text" name="%s" value="%s" /></p>';
		$this->answer_params = array( 'name', 'value' );
		
		parent::__construct( $id );
	}
	
	public function settings_fields(){
		$this->settings_fields = array(
			'min_length' => array(
				'title'			=> __( 'Minimum length', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'The minimum number of chars which can be typed in.', 'surveyval-locale' ),
				'default'		=> '10'
			), 
			'max_length' => array(
				'title'			=> __( 'Maximum length', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'The maximum number of chars which can be typed in.', 'surveyval-locale' ),
				'default'		=> '100'
			), 
		);
	}
	
	public function validate( $input ){
		$min_length = $this->settings['min_length'];
		$max_length = $this->settings['max_length'];
		
		$error = FALSE;
		
		if( !empty( $min_length ) )
			if( strlen( $input ) < $min_length ):
				$this->validate_errors[] = sprintf( __( 'The input have to be at minimum %s chars.', 'surveyval-locale' ), $min_length );
				$error = TRUE;
			endif;
		
		if( !empty( $max_length ) )		
			if( strlen( $input ) > $max_length ):
				$this->validate_errors[] = sprintf( __( 'The input have to be at maximum %s chars.', 'surveyval-locale' ), $max_length );
				$error = TRUE;
			endif;
			
		if( $error ):
			return FALSE;
		endif;
		
		return TRUE;
	}
}
sv_register_survey_element( 'SurveyVal_SurveyElement_Text' );






