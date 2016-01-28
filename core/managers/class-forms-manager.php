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

	private $form_id = null;

	private $form = null;

	private $form_controller = null;

	private $is_form_set = false;

	protected function __construct() {
		parent::__construct();
		$this->form_controller = Torro_Form_Controller::instance();
	}

	public static function instance( $id = null) {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		if( self::set_form( $id ) ){
			self::$instance->is_form_set = true;
		}

		return self::$instance;
	}

	private static function set_form( $id = null ){
		// If new $id was set
		if ( self::$instance->form_id !== $id && null != $id ) {
			self::$instance->form_id = $id;
		}

		// Try to get ID by form processor
		if( null === self::$instance->form_id ){
			self::$instance->form_id = self::$instance->form_controller->get_form_id();
		}

		if( null !== self::$instance->form_id ) {
			self::$instance->form = new Torro_Form( $id );
			return true;
		}

		return false;
	}

	public function delete(){
		if( ! $this->is_form_set ){
			return new Torro_Error( 'torro_form_not_set_automatically', __( 'Form couldn\'t be set automatically. You have to set a form id.', 'torro-forms' ), __METHOD__ );
		}
		return $this->form->delete();
	}

	public function delete_responses(){
		if( ! $this->is_form_set ){
			return new Torro_Error( 'torro_form_not_set', __( 'Form couldn\'t be set automatically. You have to set a form id.', 'torro-forms' ), __METHOD__ );
		}
		return $this->form->delete_responses();
	}

	public function dublicate( $copy_meta = true, $copy_taxonomies = true, $copy_comments = true, $copy_elements = true, $copy_answers = true, $copy_participants = true, $draft = false ){
		if( ! $this->is_form_set ){
			return new Torro_Error( 'torro_form_not_set', __( 'Form couldn\'t be set automatically. You have to set a form id.', 'torro-forms' ), __METHOD__ );
		}
		return $this->form->dublicate( $copy_meta, $copy_taxonomies, $copy_comments, $copy_elements, $copy_answers, $copy_participants, $draft);
	}

	public function exists() {
		if( ! $this->is_form_set ){
			return new Torro_Error( 'torro_form_not_set', __( 'Form couldn\'t be set automatically. You have to set a form id.', 'torro-forms' ), __METHOD__ );
		}
		return $this->form->exists();
	}

	public function html( $form_action_url = null ) {
		if ( null != $form_action_url ) {
			$this->form_controller->set_form_action_url( $form_action_url );
		}

		return $form_controller->html();
	}

	public function get_id(){
		return $this->form_id;
	}

	public function get_containers() {
		if( ! $this->is_form_set ){
			return new Torro_Error( 'torro_form_not_set', __( 'Form couldn\'t be set automatically. You have to set a form id.', 'torro-forms' ), __METHOD__ );
		}
		return $this->form->get_containers();
	}

	public function get_participants() {
		if( ! $this->is_form_set ){
			return new Torro_Error( 'torro_form_not_set', __( 'Form couldn\'t be set automatically. You have to set a form id.', 'torro-forms' ), __METHOD__ );
		}
		return $this->form->get_participants();
	}

	public function get_response_errors(){
		return $form_controller->get_response_errors();
	}

	public function get_step_count() {
		if( ! $this->is_form_set ){
			return new Torro_Error( 'torro_form_not_set', __( 'Form couldn\'t be set automatically. You have to set a form id.', 'torro-forms' ), __METHOD__ );
		}
		return $this->form->get_step_count();
	}

	public function get_step_elements( $step = 0 ) {
		if( ! $this->is_form_set ){
			return new Torro_Error( 'torro_form_not_set', __( 'Form couldn\'t be set automatically. You have to set a form id.', 'torro-forms' ), __METHOD__ );
		}
		return $this->form->get_step_elements( $step );
	}

	public function has_participated( $user_id = null ) {
		if( ! $this->is_form_set ){
			return new Torro_Error( 'torro_form_not_set', __( 'Form couldn\'t be set automatically. You have to set a form id.', 'torro-forms' ), __METHOD__ );
		}
		return $this->form->has_participated( $user_id );
	}

	public function save_response( $response ) {
		if( ! $this->is_form_set ){
			return new Torro_Error( 'torro_form_not_set', __( 'Form couldn\'t be set automatically. You have to set a form id.', 'torro-forms' ), __METHOD__ );
		}
		return $this->form->save_response( $response );
	}
}