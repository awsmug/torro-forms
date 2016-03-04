<?php
/**
 * Torro Forms continer manager class
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

final class Torro_Element_Answer_Manager extends Torro_Instance_Manager {

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
	 * Returns Element_Answer instance
	 *
	 * @param $id
	 *
	 * @return Torro_Element_Answer
	 *
	 * @since 1.0.0
	 */
	public function get( $id ){
		return parent::get( $id );
	}

	public function create_raw() {
		return new Torro_Element_Answer();
	}

	protected function get_from_db( $id ) {
		$answer = new Torro_Element_Answer( $id );
		if ( ! $answer->id ) {
			return false;
		}
		return $answer;
	}

	protected function get_category() {
		return 'element_answers';
	}
}
