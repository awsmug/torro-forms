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

	public function create( $superior_id, $args = array() ) {
		$instance = $this->create_raw( $args );
		if ( $superior_id ) {
			$instance->superior_id = absint( $superior_id );
		} else {
			$superior_id = 0;
		}
		$id = $instance->update( $args );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		$this->instances[ $this->get_category() ][ $instance->id ] = $instance;

		return $instance;
	}

	public function exists( $id ) {
		$instance = $this->get( $id );
		if ( is_wp_error( $instance ) ) {
			return false;
		}
		return true;
	}

	public function update( $id, $args = array() ) {
		$instance = $this->get( $id );
		if ( is_wp_error( $instance ) ) {
			return $instance;
		}
		$id = $instance->update( $args );
		if ( is_wp_error( $id ) ) {
			return $id;
		}

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

	public function move( $id, $superior_id ) {
		$instance = $this->get( $id );
		if ( is_wp_error( $instance ) ) {
			return $instance;
		}
		$id = $instance->move( $superior_id );
		if ( is_wp_error( $id ) ) {
			return $id;
		}

		return $instance;
	}

	public function copy( $id, $superior_id ) {
		$instance = $this->get( $id );
		if ( is_wp_error( $instance ) ) {
			return $instance;
		}
		return $instance->copy( $superior_id );
	}

	public function delete( $id ) {
		$instance = $this->get( $id );
		if ( is_wp_error( $instance ) ) {
			return $instance;
		}

		// delete instance from database
		$status = $instance->delete();
		if ( is_wp_error( $status ) ) {
			return $status;
		}

		unset( $this->instances[ $this->get_category() ][ $id ] );

		return $instance;
	}

	protected abstract function create_raw( $args = array() );

	protected abstract function get_from_db( $id );
}
