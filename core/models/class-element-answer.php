<?php
/**
 * Core: Torro_Instance_Base class
 *
 * @package TorroForms
 * @subpackage CoreModels
 * @version 1.0.0-beta.7
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Element answer class
 *
 * @since 1.0.0-beta.1
 *
 * @property int    $element_id
 * @property string $answer
 * @property int    $sort
 */
class Torro_Element_Answer extends Torro_Instance_Base {

	protected $answer = null;

	protected $sort = null;

	protected $section = '';

	protected function init() {
		$this->table_name = 'torro_element_answers';
		$this->superior_id_name = 'element_id';
		$this->manager_method = 'element_answers';
		$this->valid_args = array(
			'answer'	=> 'string',
			'sort'		=> 'int',
			'section'	=> 'string',
		);
	}
}
