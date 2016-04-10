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
	/**
	 * Instance
	 *
	 * @var null|Torro_Forms_Manager
	 * @since 1.0.0
	 */
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

	public function get_current_form_id() {
		return $this->form_controller->get_form_id();
	}

	public function create( $args = array(), $invalid = false ) {
		return parent::create( 0, $args );
	}

	public function query( $args = array() ) {
		$args['post_type'] = 'torro_form';
		$args['post_status'] = 'publish';

		if ( isset( $args['number'] ) ) {
			$args['posts_per_page'] = $args['number'];
			unset( $args['number'] );
		}

		$posts = get_posts( $args );

		$results = array();
		foreach ( $posts as $post ) {
			$results[] = $this->get( $post->ID );
		}

		return $results;
	}

	public function move( $id, $invalid ) {
		return parent::move( $id, $invalid );
	}

	public function copy( $id, $args = array() ) {
		return parent::copy( $id, $args );
	}

	protected function init() {
		$this->class_name = 'Torro_Form';
	}

	protected function get_category() {
		return 'forms';
	}
}
