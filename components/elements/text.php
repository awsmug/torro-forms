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
		
		parent::__construct( $id );
	}
	
	public function input_html(){
		return '<p><input type="text" name="' . $this->get_input_name() . '" value="' . $this->response . '" /></p>';
	}
	
	public function settings_fields(){
		$this->settings_fields = array(
			'description' => array(
				'title'			=> __( 'Description', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'The description will be shown after the question.', 'surveyval-locale' ),
				'default'		=> ''
			),
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
			'validation' => array(
				'title'			=> __( 'String Validation', 'surveyvalscience-locale' ),
				'type'			=> 'radio',
				'values'		=> array(
					'none' => __( 'No validation', 'surveyvalscience-locale' ),
					'numbers' => __( 'Numbers', 'surveyvalscience-locale' ),
					'numbers_decimal' => __( 'Decimal Numbers', 'surveyvalscience-locale' ),
					'email_address' => __( 'Email-Address', 'surveyvalscience-locale' ),
				),
				'description' 	=> __( 'The will do a validation for the input.', 'surveyvalscience-locale' ),
				'default'		=> 'none'
			),
		);
	}
	
	public function validate( $input ){
		$min_length = $this->settings['min_length'];
		$max_length = $this->settings['max_length'];
		$validation = $this->settings['validation'];
		
		$error = FALSE;
		
		if( !empty( $min_length ) )
			if( strlen( $input ) < $min_length ):
				$this->validate_errors[] = __( 'The input ist too short.', 'surveyval-locale' ) . ' ' . sprintf( __( 'It have to be at minimum %d and maximum %d chars.', 'surveyval-locale' ), $min_length, $max_length );
				$error = TRUE;
			endif;
		
		if( !empty( $max_length ) )		
			if( strlen( $input ) > $max_length ):
				$this->validate_errors[] = __( 'The input is too long.', 'surveyval-locale' ) . ' ' . sprintf( __( 'It have to be at minimum %d and maximum %d chars.', 'surveyval-locale' ), $min_length, $max_length );
				$error = TRUE;
			endif;
			
		if( 'none' != $validation ):
			switch( $validation ){
				case 'numbers':
					if( !preg_match('/^[0-9]{1,}$/', $input ) ):
						$this->validate_errors[] = sprintf( __( 'Please input a number.', 'surveyval-locale' ), $max_length );
						$error = TRUE;
					endif;
					break;
				case 'numbers_decimal':
					if( !preg_match('/^-?([0-9])+\.?([0-9])+$/', $input ) && !preg_match('/^-?([0-9])+\,?([0-9])+$/', $input ) ):
						$this->validate_errors[] = sprintf( __( 'Please input a decimal number.', 'surveyval-locale' ), $max_length );
						$error = TRUE;
					endif;	
					break;
				case 'email_address':
					if( !preg_match('/^[\w-.]+[@][a-zA-Z0-9-.äöüÄÖÜ]{3,}\.[a-z.]{2,4}$/', $input ) ):
						$this->validate_errors[] = sprintf( __( 'Please input a valid Email-Address.', 'surveyval-locale' ), $max_length );
						$error = TRUE;
					endif;
					break;
			}				
		endif;
		
		if( $error ):
			return FALSE;
		endif;
		
		return TRUE;
	}
}
sv_register_survey_element( 'SurveyVal_SurveyElement_Text' );






