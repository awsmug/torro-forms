<?php
/**
 * Core: Torro_Participants_Manager class
 *
 * @package TorroForms
 * @subpackage CoreManagers
 * @version 1.0.0-beta.1
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms participant manager class
 *
 * This class holds and manages all participant class instances.
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Participants_Manager extends Torro_Instance_Manager {

	/**
	 * Instance
	 *
	 * @var null|Torro_Participants_Manager
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
	 * Creates a new participant.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int   $form_id
	 * @param array $args
	 *
	 * @return Torro_Participant|Torro_Error
	 */
	public function create( $form_id, $args = array() ) {
		return parent::create( $form_id, $args );
	}

	/**
	 * Updates an existing participant.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int   $id
	 * @param array $args
	 *
	 * @return Torro_Participant|Torro_Error
	 */
	public function update( $id, $args = array() ) {
		return parent::update( $id, $args );
	}

	/**
	 * Gets a participant.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 *
	 * @return Torro_Participant|Torro_Error
	 */
	public function get( $id ) {
		return parent::get( $id );
	}

	/**
	 * Moves a participant to another form.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 * @param int $form_id
	 *
	 * @return Torro_Participant|Torro_Error
	 */
	public function move( $id, $form_id ) {
		return parent::move( $id, $form_id );
	}

	/**
	 * Copies a participant to another form.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 * @param int $form_id
	 *
	 * @return Torro_Participant|Torro_Error
	 */
	public function copy( $id, $form_id ) {
		return parent::copy( $id, $form_id );
	}

	/**
	 * Deletes a participant.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 *
	 * @return Torro_Participant|Torro_Error
	 */
	public function delete( $id ) {
		return parent::delete( $id );
	}

	protected function init() {
		$this->table_name = 'torro_participants';
		$this->class_name = 'Torro_Participant';
	}

	protected function get_category() {
		return 'participants';
	}
}
