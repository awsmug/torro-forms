<?php
/**
 * Core: Torro_Element_Answer_Manager class
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
 * Torro Forms element answer manager class
 *
 * This class holds and manages all element answer class instances.
 *
 * @since 1.0.0beta1
 */
final class Torro_Element_Answer_Manager extends Torro_Instance_Manager {

	/**
	 * Instance
	 *
	 * @var null|Torro_Element_Answer_Manager
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
	 * Creates a new element answer.
	 *
	 * @since 1.0.0beta1
	 *
	 * @param int   $element_id
	 * @param array $args
	 *
	 * @return Torro_Element_Answer|WP_Error
	 */
	public function create( $element_id, $args = array() ) {
		return parent::create( $element_id, $args );
	}

	/**
	 * Updates an existing element answer.
	 *
	 * @since 1.0.0beta1
	 *
	 * @param int   $id
	 * @param array $args
	 *
	 * @return Torro_Element_Answer|WP_Error
	 */
	public function update( $id, $args = array() ) {
		return parent::update( $id, $args );
	}

	/**
	 * Gets an element answer.
	 *
	 * @since 1.0.0beta1
	 *
	 * @param int $id
	 *
	 * @return Torro_Element_Answer|WP_Error
	 */
	public function get( $id ) {
		return parent::get( $id );
	}

	/**
	 * Moves an element answer to another element.
	 *
	 * @since 1.0.0beta1
	 *
	 * @param int $id
	 * @param int $element_id
	 *
	 * @return Torro_Element_Answer|WP_Error
	 */
	public function move( $id, $element_id ) {
		return parent::move( $id, $element_id );
	}

	/**
	 * Copies an element answer to another element.
	 *
	 * @since 1.0.0beta1
	 *
	 * @param int $id
	 * @param int $element_id
	 *
	 * @return Torro_Element_Answer|WP_Error
	 */
	public function copy( $id, $element_id ) {
		return parent::copy( $id, $element_id );
	}

	/**
	 * Deletes an element answer.
	 *
	 * @since 1.0.0beta1
	 *
	 * @param int $id
	 *
	 * @return Torro_Element_Answer|WP_Error
	 */
	public function delete( $id ) {
		return parent::delete( $id );
	}

	protected function init() {
		$this->table_name = 'torro_element_answers';
		$this->class_name = 'Torro_Element_Answer';
	}

	protected function get_category() {
		return 'element_answers';
	}
}
