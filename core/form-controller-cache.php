<?php
/**
 * Core: Torro_Form_Controller_Cache class
 *
 * @package TorroForms
 * @subpackage Core
 * @version 1.0.0beta1
 * @since 1.0.0beta1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for temporary response cache
 *
 * @since 1.0.0beta1
 */
class Torro_Form_Controller_Cache {

	/**
	 * Controller id
	 *
	 * @var null
	 * @since 1.0.0
	 */
	private $controller_id = null;

	/**
	 * Torro_Controller_Cache constructor.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function __construct( $controller_id ) {
		$this->controller_id = $controller_id;

		if ( ! isset( $_SESSION ) ) {
			session_start();
		}
	}

	/**
	 * Resetting controller
	 *
	 * @since 1.0.0
	 */
	public function reset(){
		unset( $_SESSION[ 'torro_forms' ][ $this->controller_id ] );
	}

	/**
	 * Setting up response
	 *
	 * @param $response
	 *
	 * @since 1.0.0
	 */
	public function set_response( $response ) {
		$this->set( 'response', $response );
	}

	/**
	 * Setting values by key
	 *
	 * @param $key
	 * @param $data
	 *
	 * @since 1.0.0
	 */
	private function set( $key, $data ) {
		$_SESSION[ 'torro_forms' ][ $this->controller_id ][ $key ] = $data;
	}

	/**
	 * Getting Response
	 *
	 * @return bool|mixed
	 * @since 1.0.0
	 */
	public function get_response() {
		return $this->get( 'response' );
	}

	/**
	 * Adding a Response
	 * @param $response
	 * @since 1.0.0
	 */
	public function add_response( $response ){
		$cached_response = $this->get_response();
		$response_merged = array_replace_recursive( $cached_response, $response );

		// Replacing element values because of maybe empty values of checkboxes
		foreach( $response[ 'containers' ] AS $container_id => $container ){
			foreach( $container[ 'elements' ] AS $element_id => $element ){
				$response_merged[ 'containers' ][ $container_id ][ 'elements' ][ $element_id ] = $response[ 'containers' ][ $container_id ][ 'elements' ][ $element_id ];
			}
		}

		return $this->set_response( $response_merged );
	}

	/**
	 * Getting values by key
	 *
	 * @param $key
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	private function get( $key ) {
		if ( isset( $_SESSION[ 'torro_forms' ][ $this->controller_id ][ $key ] ) ) {
			return $_SESSION[ 'torro_forms' ][ $this->controller_id ][ $key ];
		}

		return array();
	}

	/**
	 * Deleting response values
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function delete_response() {
		return $this->delete( 'response' );
	}

	/**
	 * Deleting values by key
	 *
	 * @param $key
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function delete( $key ) {
		if ( isset( $_SESSION[ 'torro_forms' ][ $this->controller_id ][ $key ] ) ) {
			unset( $_SESSION[ 'torro_forms' ][ $this->controller_id ][ $key ] );

			return true;
		}

		return false;
	}

	/**
	 * Setting finished
	 *
	 * @since 1.0.0
	 */
	public function set_finished(){
		$this->set( 'finished', true );
	}

	/**
	 * Checking if is finished
	 *
	 * @return bool|mixed
	 * @since 1.0.0
	 */
	public function is_finished(){
		return $this->get( 'finished' );
	}
}
