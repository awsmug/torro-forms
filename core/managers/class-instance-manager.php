<?php
/**
 * Torro Forms classes manager class
 *
 * This abstract class holds and manages all class instances.
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 1.0.0alpha1
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Torro_Instance_Manager extends Torro_Manager {

	protected $instances = array();

	protected function __construct() {
		parent::__construct();
	}

	public function add( $instance ) {
		if ( ! empty( $instance->id ) ) {
			return new Torro_Error( 'torro_instance_already_exist', sprintf( __( 'The instance %1$s of class %2$s already exists.', 'torro-forms' ), $instance->id, get_class( $instance ) ), __METHOD__ );
		}

		// insert into database
		$id = $instance->save();
		$instance->id = $id;

		$this->instances[ $this->get_category() ][ $instance->id ] = $instance;

		return $instance;
	}

	public function update( $instance ) {
		if ( empty( $instance->id ) ) {
			return $this->add( $instance );
		}

		// update in database
		$id = $instance->save();
		$instance->id = $id;

		$this->instances[ $this->get_category() ][ $instance->id ] = $instance;

		return $instance;
	}

	public function get( $id ) {
		if ( ! isset( $this->instances[ $this->get_category() ][ $id ] ) ) {
			// get from database
			$instance = $this->get_from_db( $id );
			if ( is_wp_error( $instance ) ) {
				return $instance;
			}
			if ( ! $instance ) {
				return new Torro_Error( 'torro_instance_not_exist', sprintf( __( 'The instance %s does not exist.', 'torro-forms' ), $id ), __METHOD__ );
			}
			$this->instances[ $this->get_category() ][ $id ] = $instance;
		}
		return $this->instances[ $this->get_category() ][ $id ];
	}

	/**
	 * Get registered module
	 *
	 * @param $name
	 *
	 * @return Torro_Base
	 * @since 1.0.0
	 */
	public function get_registered( $name ) {
		return parent::get_registered( $name );
	}

	public function delete( $id ) {
		$instance = $this->get( $id );
		if ( is_wp_error( $instance ) ) {
			return $instance;
		}

		// delete instance from database
		$instance->delete();

		unset( $this->instances[ $this->get_category() ][ $id ] );

		return $instance;
	}

	protected abstract function get_from_db( $id );
}
