<?php
/**
 * Core: Torro_Element_Setting_Manager class
 *
 * @package TorroForms
 * @subpackage CoreManagers
 * @version 1.0.0-beta.7
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms element setting manager class
 *
 * This class holds and manages all element setting class instances.
 *
 * @since 1.0.0-beta.1
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

	/**
	 * Creates a new element setting.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int   $element_id
	 * @param array $args
	 *
	 * @return Torro_Element_Setting|Torro_Error
	 */
	public function create( $element_id, $args = array() ) {
		return parent::create( $element_id, $args );
	}

	/**
	 * Updates an existing element setting.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int   $id
	 * @param array $args
	 *
	 * @return Torro_Element_Setting|Torro_Error
	 */
	public function update( $id, $args = array() ) {
		return parent::update( $id, $args );
	}

	/**
	 * Gets an element setting.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 *
	 * @return Torro_Element_Setting|Torro_Error
	 */
	public function get( $id ) {
		return parent::get( $id );
	}

	/**
	 * Moves an element setting to another element.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 * @param int $element_id
	 *
	 * @return Torro_Element_Setting|Torro_Error
	 */
	public function move( $id, $element_id ) {
		return parent::move( $id, $element_id );
	}

	/**
	 * Copies an element setting to another element.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 * @param int $element_id
	 *
	 * @return Torro_Element_Setting|Torro_Error
	 */
	public function copy( $id, $element_id ) {
		return parent::copy( $id, $element_id );
	}

	/**
	 * Deletes an element setting.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 *
	 * @return Torro_Element_Setting|Torro_Error
	 */
	public function delete( $id ) {
		return parent::delete( $id );
	}

	protected function init() {
		$this->table_name = 'torro_element_settings';
		$this->class_name = 'Torro_Element_Setting';
	}

	protected function get_category() {
		return 'element_settings';
	}
}
