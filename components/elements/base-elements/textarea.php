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
if( !defined( 'ABSPATH' ) ){
	exit;
}

class Questions_FormElement_Textarea extends Questions_FormElement
{

	public function __construct( $id = NULL )
	{

		$this->slug = 'Textarea';
		$this->title = esc_attr__( 'Textarea', 'questions-locale' );
		$this->description = esc_attr__( 'Add a question which can be answered within a text area.', 'questions-locale' );
		$this->icon_url = QUESTIONS_URLPATH . '/assets/images/icon-textarea.png';

		parent::__construct( $id );
	}

	public function input_html()
	{

		return '<p><textarea name="' . $this->get_input_name() . '" maxlength="' . $this->settings[ 'max_length' ] . '" rows="' . $this->settings[ 'rows' ] . '" cols="' . $this->settings[ 'cols' ] . '">' . $this->response . '</textarea></p>';
	}

	public function settings_fields()
	{

		$this->settings_fields = array(
			'description' => array(
				'title'       => esc_attr__( 'Description', 'questions-locale' ),
				'type'        => 'textarea',
				'description' => esc_attr__( 'The description will be shown after the question.', 'questions-locale' ),
				'default'     => '' ),
			'min_length'  => array(
				'title'       => esc_attr__( 'Minimum length', 'questions-locale' ),
				'type'        => 'text',
				'description' => esc_attr__( 'The minimum number of chars which can be typed in.', 'questions-locale' ),
				'default'     => '50' ),
			'max_length'  => array(
				'title'       => esc_attr__( 'Maximum length', 'questions-locale' ),
				'type'        => 'text',
				'description' => esc_attr__( 'The maximum number of chars which can be typed in.', 'questions-locale' ),
				'default'     => '500' ),
			'rows'        => array(
				'title'       => esc_attr__( 'Rows', 'questions-locale' ),
				'type'        => 'text',
				'description' => esc_attr__( 'Number of rows for typing in  (can be overwritten by CSS).', 'questions-locale' ),
				'default'     => '10' ),
			'cols'        => array(
				'title'       => esc_attr__( 'Columns', 'questions-locale' ),
				'type'        => 'text',
				'description' => esc_attr__( 'Number of columns for typing in (can be overwritten by CSS).', 'questions-locale' ),
				'default'     => '75' ), );
	}

	public function validate( $input )
	{

		$min_length = $this->settings[ 'min_length' ];
		$max_length = $this->settings[ 'max_length' ];

		$error = FALSE;

		if( !empty( $min_length ) ){
			if( strlen( $input ) < $min_length ):
				$this->validate_errors[] = esc_attr__( 'The input ist too short.', 'questions-locale' ) . ' ' . sprintf( esc_attr__( 'It have to be at minimum %d and maximum %d chars.', 'questions-locale' ), $min_length, $max_length );
				$error = TRUE;
			endif;
		}

		if( !empty( $max_length ) ){
			if( strlen( $input ) > $max_length ):
				$this->validate_errors[] = esc_attr__( 'The input is too long.', 'questions-locale' ) . ' ' . sprintf( esc_attr__( 'It have to be at minimum %d and maximum %d chars.', 'questions-locale' ), $min_length, $max_length );
				$error = TRUE;
			endif;
		}

		if( $error ):
			return FALSE;
		endif;

		return TRUE;
	}

	public function after_question()
	{

		$html = '';

		if( !empty( $this->settings[ 'description' ] ) ):
			$html = '<p class="questions-element-description">';
			$html .= $this->settings[ 'description' ];
			$html .= '</p>';
		endif;

		return $html;
	}
}
qu_register_survey_element( 'Questions_FormElement_Textarea' );