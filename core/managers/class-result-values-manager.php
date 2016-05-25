<?php
/**
 * Core: Torro_Result_Values_Manager class
 *
 * @package TorroForms
 * @subpackage CoreManagers
 * @version 1.0.0-beta.3
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms result value manager class
 *
 * This class holds and manages all result value class instances.
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Result_Values_Manager extends Torro_Instance_Manager {

	/**
	 * Instance
	 *
	 * @var null|Torro_Result_Values_Manager
	 * @since 1.0.0-beta.1
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

	/**
	 * Creates a new result value.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int   $result_id
	 * @param array $args
	 *
	 * @return Torro_Result_Value|Torro_Error
	 */
	public function create( $result_id, $args = array() ) {
		return parent::create( $result_id, $args );
	}

	/**
	 * Updates an existing result value.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int   $id
	 * @param array $args
	 *
	 * @return Torro_Result_Value|Torro_Error
	 */
	public function update( $id, $args = array() ) {
		return parent::update( $id, $args );
	}

	/**
	 * Gets a result value.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 *
	 * @return Torro_Result_Value|Torro_Error
	 */
	public function get( $id ) {
		return parent::get( $id );
	}

	/**
	 * Moves a result value to another result.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 * @param int $result_id
	 *
	 * @return Torro_Result_Value|Torro_Error
	 */
	public function move( $id, $result_id ) {
		return parent::move( $id, $result_id );
	}

	/**
	 * Copies a result value to another result.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 * @param int $result_id
	 *
	 * @return Torro_Result_Value|Torro_Error
	 */
	public function copy( $id, $result_id ) {
		return parent::copy( $id, $result_id );
	}

	/**
	 * Deletes a result value.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 *
	 * @return Torro_Result_Value|Torro_Error
	 */
	public function delete( $id ) {
		return parent::delete( $id );
	}

	protected function init() {
		$this->table_name = 'torro_result_values';
		$this->class_name = 'Torro_Result_Value';
	}

	protected function get_category() {
		return 'resultvalues';
	}
}
