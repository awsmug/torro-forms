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

class SurveyVal_SurveyElement_Textarea extends SurveyVal_SurveyElement{
	
	public function __construct( $id = null ){
		$this->slug = 'Textarea';
		$this->title = __( 'Textarea', 'surveyval-locale' );
		$this->description = __( 'Add a question which can be answered within a text area.', 'surveyval-locale' );
		$this->icon = SURVEYVAL_URLPATH . '/assets/images/icon-textarea.png';
		
		$this->answer_syntax = '<p><textarea name="%s" />%s</textarea></p>';
		$this->answer_params = array( 'name', 'value' );
		
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
			'min_length' => array(
				'title'			=> __( 'Minimum length', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'The minimum number of chars which can be typed in.', 'surveyval-locale' ),
				'default'		=> '50'
			),
			'max_length' => array(
				'title'			=> __( 'Maximum length', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'The maximum number of chars which can be typed in.', 'surveyval-locale' ),
				'default'		=> '500'
			),
			'size' => array(
				'title'			=> __( 'Size', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'Size of the field', 'surveyval-locale' ),
				'default'		=> '30'
			),
		);
	}
	
	public function validate( $input ){
		$min_length = $this->settings['min_length'];
		$max_length = $this->settings['max_length'];
		
		$error = FALSE;
		
		if( !empty( $min_length ) )
			if( strlen( $input ) < $min_length ):
				$this->validate_errors[] = __( 'The input ist too short.', 'surveyval-locale' ) . ' ' . sprintf( __( 'It have to be at minimum %d and maximum %d chars.', 'surveyval-locale' ), $min_length, $max_length );
				$error = TRUE;
			endif;
		
		if( !empty( $max_length ) )		
			if( strlen( $input ) > $max_length ):
				$this->validate_errors[] =  __( 'The input is too long.', 'surveyval-locale' ) . ' ' . sprintf( __( 'It have to be at minimum %d and maximum %d chars.', 'surveyval-locale' ), $min_length, $max_length );
				$error = TRUE;
			endif;
			
		if( $error ):
			return FALSE;
		endif;
		
		return TRUE;
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
sv_register_survey_element( 'SurveyVal_SurveyElement_Textarea' );






