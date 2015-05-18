<?php
/**
 * @package    WordPress
 * @subpackage Questions
 * @author     Sven Wagener
 * @copyright  2015, awesome.ug
 * @link       http://awesome.ug
 * @license    http://www.opensource.org/licenses/gpl-2.0.php GPL License
 */

// No direct access is allowed
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Questions_SurveyElement_Text extends Questions_SurveyElement {

	public function __construct( $id = NULL ) {

		$this->slug        = 'Text';
		$this->title       = esc_attr__( 'Text', 'questions-locale' );
		$this->description = esc_attr__(
			'Add a question which can be answered within a text field.', 'questions-locale'
		);
		$this->icon_url        = QUESTIONS_URLPATH . '/assets/images/icon-textfield.png';

		parent::__construct( $id );
	}

	public function input_html() {

		return '<p><input type="text" name="' . $this->get_input_name() . '" value="' . $this->response . '" /></p>';
	}

	public function settings_fields() {

		$this->settings_fields = array(
			'description' => array(
				'title'       => esc_attr__( 'Description', 'questions-locale' ),
				'type'        => 'text',
				'description' => esc_attr__( 'The description will be shown after the question.', 'questions-locale' ),
				'default'     => ''
			),
			'min_length'  => array(
				'title'       => esc_attr__( 'Minimum length', 'questions-locale' ),
				'type'        => 'text',
				'description' => esc_attr__( 'The minimum number of chars which can be typed in.', 'questions-locale' ),
				'default'     => '10'
			),
			'max_length'  => array(
				'title'       => esc_attr__( 'Maximum length', 'questions-locale' ),
				'type'        => 'text',
				'description' => esc_attr__( 'The maximum number of chars which can be typed in.', 'questions-locale' ),
				'default'     => '100'
			),
			'validation'  => array(
				'title'       => esc_attr__( 'String Validation', 'questions-locale' ),
				'type'        => 'radio',
				'values'      => array(
					'none'            => esc_attr__( 'No validation', 'questions-locale' ),
					'numbers'         => esc_attr__( 'Numbers', 'questions-locale' ),
					'numbers_decimal' => esc_attr__( 'Decimal Numbers', 'questions-locale' ),
					'email_address'   => esc_attr__( 'Email-Address', 'questions-locale' ),
				),
				'description' => esc_attr__( 'The will do a validation for the input.', 'questions-locale' ),
				'default'     => 'none'
			),
		);
	}

	public function validate( $input ) {
	
			$min_length = $this->settings[ 'min_length' ];
			$max_length = $this->settings[ 'max_length' ];
			$validation = $this->settings[ 'validation' ];
	
			$error = FALSE;
	
			if ( ! empty( $min_length ) ) {
				if ( strlen( $input ) < $min_length ):
					$this->validate_errors[ ] = esc_attr__(
							'The input ist too short.', 'questions-locale'
						) . ' ' . sprintf(
							esc_attr__( 'It have to be at minimum %d and maximum %d chars.', 'questions-locale' ),
							$min_length,
							$max_length
						);
					$error                    = TRUE;
				endif;
			}
	
			if ( ! empty( $max_length ) ) {
				if ( strlen( $input ) > $max_length ):
					$this->validate_errors[ ] = esc_attr__( 'The input is too long.', 'questions-locale' ) . ' ' . sprintf(
							esc_attr__( 'It have to be at minimum %d and maximum %d chars.', 'questions-locale' ),
							$min_length,
							$max_length
						);
					$error                    = TRUE;
				endif;
			}
	
			if ( 'none' != $validation ):
				switch ( $validation ) {
					case 'numbers':
						if ( ! preg_match( '/^[0-9]{1,}$/', $input ) ):
							$this->validate_errors[ ] = sprintf(
								esc_attr__( 'Please input a number.', 'questions-locale' ), $max_length
							);
							$error                    = TRUE;
						endif;
						break;
					case 'numbers_decimal':
						if ( ! preg_match( '/^-?([0-9])+\.?([0-9])+$/', $input )
							&& ! preg_match(
								'/^-?([0-9])+\,?([0-9])+$/', $input
							)
						):
							$this->validate_errors[ ] = sprintf(
								esc_attr__( 'Please input a decimal number.', 'questions-locale' ), $max_length
							);
							$error                    = TRUE;
						endif;
						break;
					case 'email_address':
						if ( ! preg_match( '/^[\w-.]+[@][a-zA-Z0-9-.äöüÄÖÜ]{3,}\.[a-z.]{2,4}$/', $input ) ):
							$this->validate_errors[ ] = sprintf(
								esc_attr__( 'Please input a valid Email-Address.', 'questions-locale' ), $max_length
							);
							$error                    = TRUE;
						endif;
						break;
				}
			endif;
	
			if ( $error ):
				return FALSE;
			endif;
	
			return TRUE;
	}
}

qu_register_survey_element( 'Questions_SurveyElement_Text' );






