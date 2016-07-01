<?php
/**
 * Core: Torro_Results_Manager class
 *
 * @package TorroForms
 * @subpackage CoreManagers
 * @version 1.0.0-beta.6
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms result manager class
 *
 * This class holds and manages all result class instances.
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Results_Manager extends Torro_Instance_Manager {

	/**
	 * Instance
	 *
	 * @var null|Torro_Results_Manager
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

	/**
	 * Creates a new result.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int   $form_id
	 * @param array $args
	 *
	 * @return Torro_Result|Torro_Error
	 */
	public function create( $form_id, $args = array() ) {
		return parent::create( $form_id, $args );
	}

	/**
	 * Updates an existing result.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int   $id
	 * @param array $args
	 *
	 * @return Torro_Result|Torro_Error
	 */
	public function update( $id, $args = array() ) {
		return parent::update( $id, $args );
	}

	/**
	 * Gets a result.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 *
	 * @return Torro_Result|Torro_Error
	 */
	public function get( $id ) {
		return parent::get( $id );
	}

	/**
	 * Moves a result to another form.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 * @param int $form_id
	 *
	 * @return Torro_Result|Torro_Error
	 */
	public function move( $id, $form_id ) {
		return parent::move( $id, $form_id );
	}

	/**
	 * Copies a result to another form.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 * @param int $form_id
	 *
	 * @return Torro_Result|Torro_Error
	 */
	public function copy( $id, $form_id ) {
		return parent::copy( $id, $form_id );
	}

	/**
	 * Deletes a result.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 *
	 * @return Torro_Result|Torro_Error
	 */
	public function delete( $id ) {
		return parent::delete( $id );
	}

	protected function init() {
		$this->table_name = 'torro_results';
		$this->class_name = 'Torro_Result';
	}

	protected function get_category() {
		return 'results';
	}
}
