<?php
/**
 * Torro Forms element classes manager class
 *
 * This class holds and manages all element class instances.
 * It can return both general instances for a type and instances for a specific element.
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 2015-04-16
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

class Torro_Form_Elements_Manager extends Torro_Manager {
	protected $element_instances = array();

	protected function init() {
		$this->base_class = 'Torro_Form_Element';
	}

	protected function after_instance_added( $instance ) {
		return $instance;
	}

	public function get( $element_id, $type = '' ) {
		global $wpdb;

		// maybe $element_id is actually an element type name
		if ( isset( $this->instances[ $element_id ] ) ) {
			return $this->instances[ $element_id ];
		}

		// otherwise it is an element ID
		if ( ! isset( $this->element_instances[ $element_id ] ) ) {
			if ( empty( $type ) ) {
				$type = $wpdb->get_var( $wpdb->prepare( "SELECT type FROM $wpdb->torro_elements WHERE id = %d ORDER BY sort ASC", $element_id ) );
				if ( ! $type ) {
					return new Torro_Error( 'torro_element_id_invalid', sprintf( __( 'The element ID %s is invalid. The type could not be detected.', 'torro-forms' ), $element_id ), __METHOD__ );
				}
			}

			if ( ! isset( $this->instances[ $type ] ) ) {
				return new Torro_Error( 'torro_instance_not_exist', sprintf( __( 'The instance %s does not exist.', 'torro-forms' ), $type ), __METHOD__ );
			}

			$class = get_class( $this->instances[ $type ] );

			$this->element_instances[ $element_id ] = call_user_func( array( $class, 'instance' ), $element_id );
		}

		return $this->element_instances[ $element_id ];
	}
}
