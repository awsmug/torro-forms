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

class Questions_FormElement_MultipleChoice extends Questions_FormElement
{

	public function __construct( $id = NULL )
	{
		$this->name = 'MultipleChoice';
		$this->title = esc_attr__( 'Multiple Choice', 'questions-locale' );
		$this->description = esc_attr__( 'Add a question which can be answered by selecting one ore more given answers.', 'questions-locale' );
		$this->icon_url = QUESTIONS_URLPATH . '/assets/images/icon-multiplechoice.png';

		$this->has_answers = TRUE;
		$this->answer_is_multiple = TRUE;
		$this->is_analyzable = TRUE;

		$this->create_answer_syntax = '<p><input type="text" name="%s" value="%s" class="question-answer" /></p>';
		$this->create_answer_params = array(
			'name',
			'answer' );

		parent::__construct( $id );
	}

	public function input_html()
	{
		$html = '';
		foreach( $this->answers AS $answer ):
			$checked = '';

			if( is_array( $this->response ) && in_array( $answer[ 'text' ], $this->response ) ){
				$checked = ' checked="checked"';
			}

			$html .= '<p><input type="checkbox" name="' . $this->get_input_name() . '[]" value="' . $answer[ 'text' ] . '" ' . $checked . ' /> ' . $answer[ 'text' ] . '</p>';
		endforeach;

		return $html;
	}

	public function settings_fields()
	{

		$this->settings_fields = array(
			'description' => array(
				'title'       => esc_attr__( 'Description', 'questions-locale' ),
				'type'        => 'textarea',
				'description' => esc_attr__( 'The description will be shown after the question.', 'questions-locale' ),
				'default'     => '' ),
			'min_answers' => array(
				'title'       => esc_attr__( 'Minimum Answers', 'questions-locale' ),
				'type'        => 'text',
				'description' => esc_attr__( 'The minimum number of answers which have to be choosed.', 'questions-locale' ),
				'default'     => '1' ),
			'max_answers' => array(
				'title'       => esc_attr__( 'Maximum Answers', 'questions-locale' ),
				'type'        => 'text',
				'description' => esc_attr__( 'The maximum number of answers which can be choosed.', 'questions-locale' ),
				'default'     => '3' ), );
	}

	public function validate( $input )
	{

		$min_answers = $this->settings[ 'min_answers' ];
		$max_answers = $this->settings[ 'max_answers' ];

		$error = FALSE;

		if( !empty( $min_answers ) ){
			if( !is_array( $input ) || count( $input ) < $min_answers ):
				$this->validate_errors[] = esc_attr__( 'Too less choices.', 'questions-locale' ) . ' ' . sprintf( esc_attr__( 'You have to choose between %d and %d answers.', 'questions-locale' ), $min_answers, $max_answers );
				$error = TRUE;
			endif;
		}

		if( !empty( $max_answers ) ){
			if( is_array( $input ) && count( $input ) > $max_answers ):
				$this->validate_errors[] = esc_attr__( 'Too many choices.', 'questions-locale' ) . ' ' . sprintf( esc_attr__( 'You have to choose between %d and %d answers.', 'questions-locale' ), $min_answers, $max_answers );
				$error = TRUE;
			endif;
		}

		if( $error ):
			return FALSE;
		endif;

		return TRUE;
	}
}

qu_register_survey_element( 'Questions_FormElement_MultipleChoice' );
