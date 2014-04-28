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

class SurveyVal_SurveyElement_MultipleChoice extends SurveyVal_SurveyElement{
	
	public function __construct( $id = null ){
		$this->slug = 'MultipleChoice';
		$this->title = __( 'Multiple Choice', 'surveyval-locale' );
		$this->description = __( 'Add a question which can be answered by selecting one ore more given answers.', 'surveyval-locale' );
		$this->icon = SURVEYVAL_URLPATH . '/assets/images/icon-multiplechoice.png';
		
		$this->preset_of_answers = TRUE;
		$this->preset_is_multiple = TRUE;
		$this->answer_is_multiple = TRUE;
		
		$this->answer_syntax = '<p><input type="checkbox" name="%s" value="%s" /> %s</p>';
		$this->answer_selected_syntax = '<p><input type="checkbox" name="%s" value="%s" checked /> %s</p>';
		$this->answer_params = array( 'name', 'value', 'answer' );
		
		$this->create_answer_syntax = '<p><input type="text" name="%s" value="%s" /></p>';
		$this->create_answer_params = array( 'name', 'answer' );
		
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
			'min_answers' => array(
				'title'			=> __( 'Minimum Answers', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'The minimum number of answers which have to be choosed.', 'surveyval-locale' ),
				'default'		=> '1'
			), 
			'max_answers' => array(
				'title'			=> __( 'Maximum Answers', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'The maximum number of answers which can be choosed.', 'surveyval-locale' ),
				'default'		=> '3'
			), 
		);
	}
	
	public function validate( $input ){
		$min_answers = $this->settings['min_answers'];
		$max_answers = $this->settings['max_answers'];
		
		$error = FALSE;
		
		if( !empty( $min_answers ) )
			if( count( $input ) < $min_answers ):
				$this->validate_errors[] = __( 'Too less choices.', 'surveyval-locale' ) . ' ' . sprintf( __( 'You have to choose between %d and %d answers.', 'surveyval-locale' ), $min_answers, $max_answers );
				$error = TRUE;
			endif;
		
		if( !empty( $max_answers ) )		
			if( count( $input ) > $max_answers ):
				$this->validate_errors[] = __( 'Too many choices.', 'surveyval-locale' ) . ' ' . sprintf( __( 'You have to choose between %d and %d answers.', 'surveyval-locale' ), $min_answers, $max_answers );
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
sv_register_survey_element( 'SurveyVal_SurveyElement_MultipleChoice' );