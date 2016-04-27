<?php
/**
 * Core: Torro_Result_Values_Manager class
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
 * Torro Forms result value manager class
 *
 * This class holds and manages all result value class instances.
 *
 * @since 1.0.0beta1
 */
final class Torro_Result_Values_Manager extends Torro_Instance_Manager {

	/**
	 * Instance
	 *
	 * @var null|Torro_Containers_Manager
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

	public function create( $result_id, $args = array() ) {
		return parent::create( $result_id, $args );
	}

	public function move( $id, $result_id ) {
		return parent::move( $id, $result_id );
	}

	public function copy( $id, $result_id ) {
		return parent::copy( $id, $result_id );
	}

	protected function init() {
		$this->table_name = 'torro_result_values';
		$this->class_name = 'Torro_Result_Value';
	}

	protected function get_category() {
		return 'resultvalues';
	}
}
