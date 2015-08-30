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

class Questions_FormElement_Splitter extends Questions_FormElement
{

	public function __construct( $id = NULL )
	{
		$this->name = 'Splitter';
		$this->title = esc_attr__( 'Split Form', 'questions-locale' );
		$this->description = esc_attr__( 'Splits a form into several steps', 'questions-locale' );
		$this->icon_url = QUESTIONS_URLPATH . '/assets/images/icon-split-form.png';

		$this->is_question = FALSE;
		$this->splits_form = TRUE;

		parent::__construct( $id );
	}

	public function settings_fields()
	{
	}

	public function get_html()
	{
	}
}

qu_register_survey_element( 'Questions_FormElement_Splitter' );






