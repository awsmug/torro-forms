<?php
/**
 * Core: Torro_Forms_Manager class
 *
 * @package TorroForms
 * @subpackage CoreManagers
 * @version 1.0.0-beta.2
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms form manager class
 *
 * This class holds and manages all form class instances.
 *
 * @since 1.0.0-beta.1
 */
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
		$form_id = $this->form_controller->get_form_id();
		if ( ! $form_id ) {
			$post = get_post();
			if ( $post && 'torro_form' === $post->post_type ) {
				$form_id = $post->ID;
			}
		}
		return $form_id;
	}

	/**
	 * Creates a new form.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $args
	 *
	 * @return Torro_Form|Torro_Error
	 */
	public function create( $args = array(), $invalid = false ) {
		return parent::create( 0, $args );
	}

	/**
	 * Updates an existing form.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int   $id
	 * @param array $args
	 *
	 * @return Torro_Form|Torro_Error
	 */
	public function update( $id, $args = array() ) {
		return parent::update( $id, $args );
	}

	/**
	 * Gets a form.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 *
	 * @return Torro_Form|Torro_Error
	 */
	public function get( $id ) {
		return parent::get( $id );
	}

	public function query( $args = array() ) {
		$args['post_type'] = 'torro_form';
		$args['post_status'] = 'publish';

		if ( isset( $args['number'] ) ) {
			$args['posts_per_page'] = $args['number'];
			unset( $args['number'] );
		}

		if ( ! isset( $args['orderby'] ) ) {
			$args['orderby'] = 'none';
		}
		if ( ! isset( $args['order'] ) ) {
			$args['order'] = 'ASC';
		}

		$posts = get_posts( $args );

		$results = array();
		foreach ( $posts as $post ) {
			$results[] = $this->get( $post->ID );
		}

		return $results;
	}

	/**
	 * Copies a form.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int   $id
	 * @param array $args
	 *
	 * @return Torro_Form|Torro_Error
	 */
	public function copy( $id, $args = array() ) {
		return parent::copy( $id, $args );
	}

	/**
	 * Deletes a form.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id
	 *
	 * @return Torro_Form|Torro_Error
	 */
	public function delete( $id ) {
		return parent::delete( $id );
	}

	protected function init() {
		$this->class_name = 'Torro_Form';
	}

	protected function get_category() {
		return 'forms';
	}
}
