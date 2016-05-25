<?php
/**
 * Core: Torro_Containers_Manager class
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
 * Torro Forms container manager class
 *
 * This class holds and manages all container class instances.
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Containers_Manager extends Torro_Instance_Manager {

	/**
	 * Instance
	 *
	 * @var Torro_Containers_Manager
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
	 * Creates a new container.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int   $form_id
	 * @param array $args
	 *
	 * @return Torro_Container|Torro_Error
	 */
	public function create( $form_id, $args = array() ) {
		return parent::create( $form_id, $args );
	}

	/**
	 * Updates an existing container.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int   $id
	 * @param array $args
	 *
	 * @return Torro_Container|Torro_Error
	 */
	public function update( $id, $args = array() ) {
		return parent::update( $id, $args );
	}

	/**
	 * Gets a container.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 *
	 * @return Torro_Container|Torro_Error
	 */
	public function get( $id ) {
		return parent::get( $id );
	}

	/**
	 * Moves a container to another form.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 * @param int $form_id
	 *
	 * @return Torro_Container|Torro_Error
	 */
	public function move( $id, $form_id ) {
		return parent::move( $id, $form_id );
	}

	/**
	 * Copies a container to another form.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 * @param int $form_id
	 *
	 * @return Torro_Container|Torro_Error
	 */
	public function copy( $id, $form_id ) {
		return parent::copy( $id, $form_id );
	}

	/**
	 * Deletes a container.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 *
	 * @return Torro_Container|Torro_Error
	 */
	public function delete( $id ) {
		return parent::delete( $id );
	}

	protected function init() {
		$this->table_name = 'torro_containers';
		$this->class_name = 'Torro_Container';
	}

	protected function get_category() {
		return 'containers';
	}
}
