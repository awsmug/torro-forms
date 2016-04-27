<?php
/**
 * Core: Torro_Instance_Base class
 *
 * @package TorroForms
 * @subpackage CoreModels
 * @version 1.0.0beta1
 * @since 1.0.0beta1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Element answer class
 *
 * @since 1.0.0beta1
 *
 * @property int    $element_id
 * @property string $answer
 * @property int    $sort
 */
class Torro_Element_Answer extends Torro_Instance_Base {

	protected $answer = null;

	protected $sort = null;

	protected $section = '';

	public function __construct( $id = null ) {
		parent::__construct( $id );
	}

	public function move( $element_id ) {
		return parent::move( $element_id );
	}

	public function copy( $element_id ) {
		return parent::copy( $element_id );
	}

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
