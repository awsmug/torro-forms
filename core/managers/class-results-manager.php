<?php
/**
 * Core: Torro_Results_Manager class
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
 * Torro Forms result manager class
 *
 * This class holds and manages all result class instances.
 *
 * @since 1.0.0beta1
 */
final class Torro_Results_Manager extends Torro_Instance_Manager {

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

	public function create( $form_id, $args = array() ) {
		return parent::create( $form_id, $args );
	}

	public function move( $id, $form_id ) {
		return parent::move( $id, $form_id );
	}

	public function copy( $id, $form_id ) {
		return parent::copy( $id, $form_id );
	}

	protected function init() {
		$this->table_name = 'torro_results';
		$this->class_name = 'Torro_Result';
	}

	protected function get_category() {
		return 'results';
	}
}
