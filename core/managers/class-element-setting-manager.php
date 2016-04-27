<?php
/**
 * Core: Torro_Element_Setting_Manager class
 *
 * @package TorroForms
 * @subpackage CoreManagers
 * @version 1.0.0beta1
 * @since 1.0.0beta1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms element setting manager class
 *
 * This class holds and manages all element setting class instances.
 *
 * @since 1.0.0beta1
 */
final class Torro_Element_Setting_Manager extends Torro_Instance_Manager {

	/**
	 * Instance
	 *
	 * @var null|Torro_Element_Setting_Manager
	 * @since 1.0.0
	 */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected function __construct() {
		parent::__construct();
	}

	public function create( $element_id, $args = array() ) {
		return parent::create( $element_id, $args );
	}

	public function move( $id, $element_id ) {
		return parent::move( $id, $element_id );
	}

	public function copy( $id, $element_id ) {
		return parent::copy( $id, $element_id );
	}

	protected function init() {
		$this->table_name = 'torro_element_settings';
		$this->class_name = 'Torro_Element_Setting';
	}

	protected function get_category() {
		return 'element_settings';
	}
}
