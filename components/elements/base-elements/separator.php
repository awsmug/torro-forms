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

class Questions_FormElement_Separator extends Questions_FormElement {

	public function __construct( $id = NULL ) {

		$this->slug        = 'Separator';
		$this->title       = esc_attr__( 'Separator', 'questions-locale' );
		$this->description = esc_attr__( 'Adds a optical separator (<hr>) between questions.', 'questions-locale' );
		$this->icon_url        = QUESTIONS_URLPATH . '/assets/images/icon-separator.png';

		$this->is_question = FALSE;

		parent::__construct( $id );
	}

	public function input_html() {

		$html = '<div class="survey-element survey-element-' . $this->id . '">';

		if ( ! empty( $this->settings[ 'header' ] ) ) {
			$html .= '<h3>' . $this->settings[ 'header' ] . '</h3>';
		}

		$html .= '<hr /></div>';

		return $html;
	}

	public function settings_fields() {

		$this->settings_fields = array(
			'header' => array(
				'title'       => esc_attr__( 'Headline', 'questions-locale' ),
				'type'        => 'textarea',
				'description' => esc_attr__( 'Text which will be shown above the separator', 'questions-locale' ),
				'default'     => ''
			)
		);
	}
}

qu_register_survey_element( 'Questions_FormElement_Separator' );
