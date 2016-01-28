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

final class Torro_Containers_Manager extends Torro_Manager {

	private static $instance = null;

	public static $container_id = null;

	private $container = null;

	public static function instance( $id = null ) {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		self::set_container( $id );

		return self::$instance;
	}

	private static function set_container( $id = null ){
		// If new $id was set
		if ( self::$instance->container_id !== $id && null != $id ) {
			self::$instance->container_id = $id;
		}

		self::$instance->container = new Torro_Container( $id );
	}

	public function form( $id = null ){
		if( null !== $id ) {
			$this->container->form_id = $id;
		}else{
			return $this->container->form_id;
		}
	}

	public function label( $label = null ){
		if( null !== $label ) {
			$this->container->label = $label;
		}else{
			return $this->container->label;
		}
	}

	public function sort( $number = null ){
		if( null !== $number ) {
			$this->container->sort = $number;
		}else{
			return $this->container->sort;
		}
	}

	public function save(){
		return $this->container->save();
	}

	public function delete(){
		return $this->container->delete();
	}

	public function get_elements(){
		return $this->container->elements;
	}
}