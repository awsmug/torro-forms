<?php
/**
 * Torro Forms extensions manager class
 *
 * This class holds and manages all extension class instances.
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

final class Torro_Forms_Manager extends Torro_Manager {
	private static $instance = null;

	private $id;
	private $object;

	public static function instance( $id = null) {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		if ( self::$instance->id !== $id && null != $id ) {
			self::$instance->id = $id;
			self::$instance->object    = new Torro_Form( $id );
		}

		return self::$instance;
	}

	public function delete(){
		return $this->form->delete();
	}

	public function delete_responses(){
		return $this->form->delete_responses();
	}

	public function dublicate( $copy_meta = true, $copy_taxonomies = true, $copy_comments = true, $copy_elements = true, $copy_answers = true, $copy_participants = true, $draft = false ){
		return $this->form->dublicate( $copy_meta, $copy_taxonomies, $copy_comments, $copy_elements, $copy_answers, $copy_participants, $draft);
	}

	public function exists() {
		return $this->form->exists();
	}

	public function html( $object_action_url = null ) {
		$form_loader = Torro_Form_Controller::instance();
		$form_loader->set_id( $this->id );

		if ( null != $form_action_url ) {
			$form_loader->set_form_action_url( $form_action_url );
		}

		return $form_loader->html();
	}

	public function get_current_id(){
		$form_loader = Torro_Form_Controller::instance();
		return $form_loader->get_id();
	}

	public function get_containers() {
		return $this->object->get_containers();
	}

	public function get_participants() {
		return $this->object->get_participants();
	}

	public function get_response_errors(){
		$form_loader = Torro_Form_Controller::instance();
		$form_loader->set_id( $form_loader->get_id() );
		return $form_loader->get_response_errors();
	}

	public function get_step_count() {
		return $this->object->get_step_count();
	}

	public function get_step_elements( $step = 0 ) {
		return $this->object->get_step_elements( $step );
	}

	public function has_participated( $user_id = null ) {
		return $this->object->has_participated( $user_id );
	}

	public function save_response( $response ) {
		return $this->object->save_response( $response );
	}

	protected function init() {
	}
}