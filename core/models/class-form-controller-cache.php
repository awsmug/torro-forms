<?php

/**
 * Torro Controller Cache
 *
 * @author  awesome.ug <contact@awesome.ug>
 * @package TorroForms
 * @version 2015-04-16
 * @since   1.0.0
 * @license GPL 2
 *          Copyright 2015 rheinschmiede (contact@awesome.ug)
 *          This program is free software; you can redistribute it and/or modify
 *          it under the terms of the GNU General Public License, version 2, as
 *          published by the Free Software Foundation.
 *          This program is distributed in the hope that it will be useful,
 *          but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *          GNU General Public License for more details.
 *          You should have received a copy of the GNU General Public License
 *          along with this program; if not, write to the Free Software
 *          Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
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
		if ( ! isset( $_SESSION ) ) {
			return session_start();
		}

		return true;
	}

	/**
	 * Resetting controller
	 *
	 * @since 1.0.0
	 */
	public function reset(){
		unset( $_SESSION[ $this->controller_id ] );
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
		$_SESSION[ $this->controller_id ][ $key ] = $data;
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
	 * Getting values by key
	 *
	 * @param $key
	 *
	 * @return bool|mixed
	 * @since 1.0.0
	 */
	private function get( $key ) {
		if ( isset( $_SESSION[ $this->controller_id ][ $key ] ) ) {
			return $_SESSION[ $this->controller_id ][ $key ];
		}

		return false;
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
		if ( isset( $_SESSION[ $this->controller_id ][ $key ] ) ) {
			unset( $_SESSION[ $this->controller_id ][ $key ] );

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