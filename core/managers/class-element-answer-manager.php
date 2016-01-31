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

final class Torro_Element_Answer_Manager extends Torro_Manager {

	private static $instance = null;

	private $answer_id;

	private $answer;

	public static function instance( $id = null ) {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		self::set_answer( $id );

		return self::$instance;
	}

	private static function set_answer( $id = null ){
		// If new $id was set
		if ( self::$instance->answer_id !== $id && null != $id ) {
			self::$instance->answer_id = $id;
		}

		if( ! is_object( self::$instance->answer ) ) {
			self::$instance->answer = new Torro_Element_Answer( $id );
		}
	}

	public function element( $id = null ){
		if( null !== $id ) {
			$this->answer->element_id = $id;
		}else{
			return $this->answer->element_answer_id;
		}
	}

	public function label( $label = null ){
		if( null !== $label ) {
			$this->answer->label = $label;
		}else{
			return $this->answer->label;
		}
	}

	public function sort( $number = null ){
		if( null !== $number ) {
			$this->answer->sort = $number;
		}else{
			return $this->answer->sort;
		}
	}

	public function section( $section = null ){
		if( null !== $section ) {
			$this->answer->section = $section;
		}else{
			return $this->answer->section;
		}
	}

	public function save(){
		return $this->answer->save();
	}

	public function delete(){
		return $this->answer->delete();
	}

	protected function init() {
	}

	public function get_elements(){
		return $this->answer->elements;
	}
}