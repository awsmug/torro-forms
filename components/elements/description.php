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

class Questions_SurveyElement_Description extends Questions_SurveyElement {

	public function __construct( $id = NULL ) {

		$this->slug        = 'Description';
		$this->title       = esc_attr__( 'Description', 'questions-locale' );
		$this->description = esc_attr__( 'Adds a text to the form.', 'questions-locale' );
		$this->icon_url        = QUESTIONS_URLPATH . '/assets/images/icon-text.png';

		$this->is_question = FALSE;

		parent::__construct( $id );
	}

	public function input_html() {
	}

	public function settings_fields() {

		$this->settings_fields = array(
			'description' => array(
				'title'       => esc_attr__( 'Text to show', 'questions-locale' ),
				'type'        => 'wp_editor',
				'description' => esc_attr__( 'The text which will be shown in the form.', 'questions-locale' ),
				'default'     => ''
			)
		);
	}
}

qu_register_survey_element( 'Questions_SurveyElement_Description' );
