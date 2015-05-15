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
		$this->icon        = QUESTIONS_URLPATH . '/assets/images/icon-textfield.png';

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
			)
		);
	}
}

qu_register_survey_element( 'Questions_SurveyElement_Text' );






