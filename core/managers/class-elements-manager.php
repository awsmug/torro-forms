<?php
/**
 * Core: Torro_Elements_Manager class
 *
 * @package TorroForms
 * @subpackage CoreManagers
 * @version 1.0.0-beta.3
 * @since 1.0.0-beta.1
 */

/**
 * Torro Forms element classes manager class
 *
 * This class holds and manages all element class instances.
 * It can return both general instances for a type and instances for a specific element.
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

/**
 * Torro Forms element manager class
 *
 * This class holds and manages all element class instances.
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Elements_Manager extends Torro_Instance_Manager {

	/**
	 * Instance
	 *
	 * @var null|Torro_Elements_Manager
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
	 * Creates a new element.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int   $container_id
	 * @param array $args
	 *
	 * @return Torro_Element|Torro_Error
	 */
	public function create( $container_id, $args = array() ) {
		return parent::create( $container_id, $args );
	}

	/**
	 * Updates an existing element.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int   $id
	 * @param array $args
	 *
	 * @return Torro_Element|Torro_Error
	 */
	public function update( $id, $args = array() ) {
		return parent::update( $id, $args );
	}

	/**
	 * Gets an element.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 *
	 * @return Torro_Element|Torro_Error
	 */
	public function get( $id ) {
		return parent::get( $id );
	}

	/**
	 * Moves an element to another container.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 * @param int $container_id
	 *
	 * @return Torro_Element|Torro_Error
	 */
	public function move( $id, $container_id ) {
		return parent::move( $id, $container_id );
	}

	/**
	 * Copies an element to another container.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 * @param int $container_id
	 *
	 * @return Torro_Element|Torro_Error
	 */
	public function copy( $id, $container_id ) {
		return parent::copy( $id, $container_id );
	}

	/**
	 * Deletes an element.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 *
	 * @return Torro_Element|Torro_Error
	 */
	public function delete( $id ) {
		return parent::delete( $id );
	}

	protected function init() {
		$this->table_name = 'torro_elements';
	}

	protected function create_raw( $args = array() ) {
		$type = isset( $args['type'] ) ? $args['type'] : 'textfield';
		$class_name = torro()->element_types()->get_class_name_by_type( $type );
		if ( ! class_exists( $class_name ) ) {
			$class_name = 'Torro_Element_Textfield';
		}
		return new $class_name();
	}

	protected function get_from_db( $id ) {
		global $wpdb;

		$type = 'textfield';
		if ( is_object( $id ) && isset( $id->type ) ) {
			$type = $id->type;
		} else {
			$sql = $wpdb->prepare( "SELECT type FROM $wpdb->torro_elements WHERE id = %d ORDER BY sort ASC", absint( $id ) );
			$type = $wpdb->get_var( $sql );
			if ( ! $type ) {
				$type = 'textfield';
			}
		}

		$class_name = torro()->element_types()->get_class_name_by_type( $type );
		if ( ! class_exists( $class_name ) ) {
			return false;
		}

		$element = new $class_name( $id );
		if ( ! $element->id ) {
			return false;
		}
		return $element;
	}

	protected function get_category() {
		return 'elements';
	}
}
