<?php
/**
 * Core: Torro_Element_Setting class
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
 * Element setting class
 *
 * @since 1.0.0-beta.1
 *
 * @property int    $element_id
 * @property string $name
 * @property string $value
 */
class Torro_Element_Setting extends Torro_Instance_Base {

	protected $name = '';

	protected $value = '';

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
		$this->table_name = 'torro_element_settings';
		$this->superior_id_name = 'element_id';
		$this->manager_method = 'element_settings';
		$this->valid_args = array(
			'name'		=> 'string',
			'value'		=> 'string',
		);
	}
}
