<?php
/**
 * Torro Forms extensions manager class
 *
 * This class holds and manages all extension class instances.
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

final class Torro_Forms_Manager extends Torro_Instance_Manager {

	private static $instance = null;

	private $form_controller = null;

	protected function __construct() {
		parent::__construct();
		$this->form_controller = Torro_Form_Controller::instance();
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_current() {
		$form_id = $this->get_current_form_id();
		if ( ! $form_id ) {
			return new Torro_Error( 'no_current_form_detected', __( 'No current form could be detected.', 'torro-forms' ), __METHOD__ );
		}
		return $this->get( $form_id );
	}

	/**
	 * Returns Form instance
	 *
	 * @param $id
	 *
	 * @return Torro_Form
	 *
	 * @since 1.0.0
	 */
	public function get( $id ){
		return parent::get( $id );
	}

	public function get_current_form_id() {
		return $this->form_controller->get_form_id();
	}

	public function create_raw() {
		return new Torro_Form();
	}

	protected function get_from_db( $id ) {
		$form = new Torro_Form( $id );
		if ( ! $form->id ) {
			return false;
		}
		return $form;
	}

	protected function get_category() {
		return 'forms';
	}
}
