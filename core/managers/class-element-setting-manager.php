<?php
/**
 * Torro Forms continer manager class
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

final class Torro_Element_Setting_Manager extends Torro_Manager {

	private static $instance = null;

	private $id;

	private $object;

	public static function instance( $id = null ) {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		if ( self::$instance->id !== $id  && null !== $id ) {
			self::$instance->id     = $id;
			self::$instance->object = new Torro_Element_Setting( $id );
		}

		return self::$instance;
	}

	public function element( $id = null ) {
		if ( null !== $id ) {
			$this->object->element_id = $id;
		} else {
			return $this->object->element_id;
		}
	}

	public function name( $name = null ) {
		if ( null !== $name ) {
			$this->object->name = $name;
		} else {
			return $this->object->name;
		}
	}

	public function value( $value = null ) {
		if ( null !== $value ) {
			$this->object->value = $value;
		} else {
			return $this->object->value;
		}
	}

	public function save() {
		return $this->object->save();
	}

	public function delete() {
		return $this->object->delete();
	}

	public function get_elements() {
		return $this->object->elements;
	}

	protected function init() {
	}
}