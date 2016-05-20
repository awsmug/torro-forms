<?php
/**
 * Core: Torro_Result class
 *
 * @package TorroForms
 * @subpackage CoreModels
 * @version 1.0.0-beta.2
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Result class
 *
 * @since 1.0.0-beta.1
 *
 * @property int    $form_id
 * @property int    $user_id
 * @property int    $timestamp
 * @property string $remote_addr
 * @property string $cookie_key
 *
 * @property-read array $values
 */
class Torro_Result extends Torro_Instance_Base {

	protected $user_id;

	protected $timestamp;

	protected $remote_addr;

	protected $cookie_key;

	protected $values = array();

	/**
	 * Torro_Container constructor.
	 *
	 * @param int $id
	 *
	 * @since 1.0.0
	 */
	public function __construct( $id = null ) {
		parent::__construct( $id );
	}

	public function move( $form_id ) {
		return parent::move( $form_id );
	}

	public function copy( $form_id ) {
		return parent::copy( $form_id );
	}

	protected function init() {
		$this->table_name = 'torro_results';
		$this->superior_id_name = 'form_id';
		$this->manager_method = 'results';
		$this->valid_args = array(
			'user_id'		=> 'int',
			'timestamp'		=> 'int',
			'remote_addr'	=> 'string',
			'cookie_key'	=> 'string',
		);
	}

	/**
	 * Populating object
	 *
	 * @param int $id
	 *
	 * @since 1.0.0
	 */
	protected function populate( $id ) {
		parent::populate( $id );

		if ( $this->id ) {
			$this->values = torro()->result_values()->query( array(
				'result_id'		=> $this->id,
				'number'		=> -1,
			) );
		}
	}

	protected function delete_from_db(){
		$status = parent::delete_from_db();

		if ( $status && ! is_wp_error( $status ) ) {
			foreach ( $this->values as $value ) {
				torro()->result_values()->delete( $value->id );
			}
		}

		return $status;
	}
}
