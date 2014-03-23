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

class SurveyVal_SurveyElement_Range extends SurveyVal_SurveyElement{
	
	public function __construct( $id = null ){
		$this->slug = 'Range';
		$this->title = __( 'Range', 'surveyval-locale' );
		$this->description = __( 'Add a question which can be answered within a range scale.', 'surveyval-locale' );
		$this->icon = SURVEYVAL_URLPATH . '/assets/images/icon-range.png';
		
		$this->answer_syntax = '<input type="range" min="0" max="1000" name="%s" value="%s" />';
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
			'range_from' => array(
				'title'			=> __( 'Range from', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'This value will be shown at the beginning of the scale.', 'surveyval-locale' ),
				'default'		=> '1'
			), 
			'range_to' => array(
				'title'			=> __( 'Range to', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'This value will be shown at the end of the scale', 'surveyval-locale' ),
				'default'		=> '10'
			), 
			'step_size' => array(
				'title'			=> __( 'Step size', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'The size of steps between from and to', 'surveyval-locale' ),
				'default'		=> '1'
			), 
		);
	}
	
	public function get_element(){
		if( $this->error )
			$error_css = ' survey-element-error';
			
		$html = '<div class="question question_' . $this->id . $error_css . '">';
		$html.= $this->before_question();
		$html.= '<h5>' . $this->question . '</h5>';
		$html.= $this->after_question();
		
		$step_values = array();
		$step_values[] = $this->settings['range_from'];
		$step_size = $this->settings['step_size'];
		
		$actual_size = $this->settings['range_from'];
		$actual_size += $step_size;
		
		while( $actual_size < $this->settings['range_to'] ):
			$step_values[] = $actual_size;
			$actual_size += $step_size;
		endwhile;
		
		$step_values[] = $this->settings['range_to'];
		
		$html.= '<table class="surveyval-range-table"><tr>';
		
		foreach( $step_values AS $value ):
			$html.= '<td><input type="radio" name="surveyval_response[' . $this->id . ']" value="' . $value . '"><br />' .  $value . '<td>';
		endforeach;
		
		$html.= '</table></tr>';
		
		$html.= '</div>';
		
		return $html;
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
sv_register_survey_element( 'SurveyVal_SurveyElement_Range' );






