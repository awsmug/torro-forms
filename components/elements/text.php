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

class Questions_SurveyElement_Text extends Questions_SurveyElement{
	
	public function __construct( $id = null ){
		$this->slug = 'Text';
		$this->title = __( 'Text', 'questions-locale' );
		$this->description = __( 'Add a question which can be answered within a text field.', 'questions-locale' );
		$this->icon = QUESTIONS_URLPATH . '/assets/images/icon-textfield.png';
		
		parent::__construct( $id );
	}
	
	public function input_html(){
		return '<p><input type="text" name="' . $this->get_input_name() . '" value="' . $this->response . '" /></p>';
	}
	
	public function settings_fields(){
		$this->settings_fields = array(
			'description' => array(
				'title'			=> __( 'Description', 'questions-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'The description will be shown after the question.', 'questions-locale' ),
				'default'		=> ''
			),
			'min_length' => array(
				'title'			=> __( 'Minimum length', 'questions-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'The minimum number of chars which can be typed in.', 'questions-locale' ),
				'default'		=> '10'
			), 
			'max_length' => array(
				'title'			=> __( 'Maximum length', 'questions-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'The maximum number of chars which can be typed in.', 'questions-locale' ),
				'default'		=> '100'
			),
			'validation' => array(
				'title'			=> __( 'String Validation', 'questions-locale' ),
				'type'			=> 'radio',
				'values'		=> array(
					'none' => __( 'No validation', 'questions-locale' ),
					'numbers' => __( 'Numbers', 'questions-locale' ),
					'numbers_decimal' => __( 'Decimal Numbers', 'questions-locale' ),
					'email_address' => __( 'Email-Address', 'questions-locale' ),
				),
				'description' 	=> __( 'The will do a validation for the input.', 'questions-locale' ),
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
				$this->validate_errors[] = __( 'The input ist too short.', 'questions-locale' ) . ' ' . sprintf( __( 'It have to be at minimum %d and maximum %d chars.', 'questions-locale' ), $min_length, $max_length );
				$error = TRUE;
			endif;
		
		if( !empty( $max_length ) )		
			if( strlen( $input ) > $max_length ):
				$this->validate_errors[] = __( 'The input is too long.', 'questions-locale' ) . ' ' . sprintf( __( 'It have to be at minimum %d and maximum %d chars.', 'questions-locale' ), $min_length, $max_length );
				$error = TRUE;
			endif;
			
		if( 'none' != $validation ):
			switch( $validation ){
				case 'numbers':
					if( !preg_match('/^[0-9]{1,}$/', $input ) ):
						$this->validate_errors[] = sprintf( __( 'Please input a number.', 'questions-locale' ), $max_length );
						$error = TRUE;
					endif;
					break;
				case 'numbers_decimal':
					if( !preg_match('/^-?([0-9])+\.?([0-9])+$/', $input ) && !preg_match('/^-?([0-9])+\,?([0-9])+$/', $input ) ):
						$this->validate_errors[] = sprintf( __( 'Please input a decimal number.', 'questions-locale' ), $max_length );
						$error = TRUE;
					endif;	
					break;
				case 'email_address':
					if( !preg_match('/^[\w-.]+[@][a-zA-Z0-9-.äöüÄÖÜ]{3,}\.[a-z.]{2,4}$/', $input ) ):
						$this->validate_errors[] = sprintf( __( 'Please input a valid Email-Address.', 'questions-locale' ), $max_length );
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
qu_register_survey_element( 'Questions_SurveyElement_Text' );






