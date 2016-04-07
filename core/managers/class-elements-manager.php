<?php
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

final class Torro_Form_Elements_Manager extends Torro_Instance_Manager {

	/**
	 * Instance
	 *
	 * @var null|Torro_Form_Elements_Manager
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

	protected function init() {
		$this->table_name = 'torro_elements';
	}

	protected function create_raw( $args = array() ) {
		$type = isset( $args['type'] ) ? $args['type'] : 'textfield';
		$class_name = $this->get_class_name_by_type( $type );
		if ( ! class_exists( $class_name ) ) {
			$class_name = 'Torro_Form_Element_Textfield';
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

		$class_name = $this->get_class_name_by_type( $type );
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

	private function get_class_name_by_type( $type ) {
		return apply_filters( 'torro_element_type_class_name', 'Torro_Form_Element_' . ucfirst( $type ), $type );
	}
}
