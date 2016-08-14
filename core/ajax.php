<?php
/**
 * Core: Torro_AJAX class
 *
 * @package TorroForms
 * @subpackage Core
 * @version 1.0.0-beta.7
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms AJAX handler class
 *
 * Handles all AJAX requests for the plugin and its extensions.
 *
 * @since 1.0.0-beta.1
 */
final class Torro_AJAX {
	/**
	 * Intance
	 *
	 * @var object $instance
	 * @since 1.0.0
	 */
	private static $instance = null;

	/**
	 * Singleton
	 *
	 * @return null|Torro_AJAX
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Actions
	 *
	 * @var array $actions
	 * @since 1.0.0
	 */
	private $actions = array(
		'duplicate_form'				=> array( 'nopriv' => false ),
		'delete_responses'				=> array( 'nopriv' => false ),
		'get_editor_html'				=> array( 'nopriv' => false ),
	);

	private $nonces = array();

	/**
	 * Torro_AJAX constructor
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		add_action( 'admin_init', array( $this, 'add_actions' ) );
	}

	public function register_action( $name, $args = array() ) {
		if ( doing_action( 'admin_init' ) || did_action( 'admin_init' ) ) {
			return new Torro_Error( 'register_ajax_action_too_late', sprintf( __( 'AJAX actions must be registered before the %s hook.', 'torro-forms' ), '<code>admin_init</code>' ), __METHOD__ );
		}

		if ( ! $name || isset( $this->actions[ $name ] ) ) {
			return new Torro_Error( 'invalid_ajax_action_name', __( 'Invalid AJAX action name.', 'torro-forms' ), __METHOD__ );
		}

		if ( ! isset( $args['callback'] ) ) {
			return new Torro_Error( 'missing_ajax_action_callback', __( 'Registered AJAX actions must have a callback function.', 'torro-forms' ), __METHOD__ );
		}

		$args = wp_parse_args( $args, array( 'nopriv' => false ) );

		$this->actions[ $name ] = $args;

		return true;
	}

	/**
	 * Adding actions to WP AJAX engine
	 *
	 * @since 1.0.0
	 */
	public function add_actions() {
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			return;
		}

		foreach ( $this->actions as $action => $data ) {
			if ( ! is_array( $data ) ) {
				$data = array();
			}
			$nopriv = ( isset( $data['nopriv'] ) && $data['nopriv'] ) ? true : false;

			add_action( 'wp_ajax_torro_' . $action, array( $this, 'request' ) );
			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_torro_' . $action, array( $this, 'request' ) );
			}
		}
	}

	/**
	 * Doing a request
	 *
	 * @since 1.0.0
	 */
	public function request() {
		$action = str_replace( 'torro_', '', $_REQUEST['action'] );

		if ( ! isset( $this->actions[ $action ] ) ) {
			wp_send_json_error( __( 'Invalid action.', 'torro-forms' ) );
		}

		$callback = ( is_array( $this->actions[ $action ] ) && isset( $this->actions[ $action ]['callback'] ) && $this->actions[ $action ]['callback'] ) ? $this->actions[ $action ]['callback'] : array( $this, 'ajax_' . $action );

		if ( ! is_callable( $callback ) ) {
			wp_send_json_error( __( 'Invalid action callback.', 'torro-forms' ) );
		}

		if ( ! isset( $_REQUEST['nonce'] ) ) {
			wp_send_json_error( __( 'Missing nonce.', 'torro-forms' ) );
		}

		if ( ! check_ajax_referer( $this->get_nonce_action( $action ), 'nonce', false ) ) {
			wp_send_json_error( __( 'Invalid nonce.', 'torro-forms' ) );
		}

		$response = call_user_func( $callback, $_REQUEST );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_message() );
		}

		wp_send_json_success( $response );
	}

	/**
	 * Getting nonce
	 *
	 * @param $action
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function get_nonce( $action ) {
		if ( ! isset( $this->nonces[ $action ] ) ) {
			$this->nonces[ $action ] = wp_create_nonce( $this->get_nonce_action( $action ) );
		}
		return $this->nonces[ $action ];
	}

	/**
	 * Getting nonce action
	 *
	 * @param $action
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function get_nonce_action( $action ) {
		return 'torro_ajax_' . $action;
	}

	/**
	 * Dublicating form
	 *
	 * @param $data
	 *
	 * @return array|Torro_Error
	 * @since 1.0.0
	 */
	public function ajax_duplicate_form( $data ) {
		if ( ! isset( $data['form_id'] ) ) {
			return new Torro_Error( 'ajax_duplicate_form_form_id_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'form_id' ) );
		}

		$form_id = absint( $data['form_id'] );

		$form = get_post( $form_id );

		if ( ! $form || 'torro_form' !== $form->post_type ) {
			return new Torro_Error( 'ajax_duplicate_form_invalid_form', __( 'The post is not a form.', 'torro-forms' ) );
		}

		$form = torro()->forms()->get( $form_id );
		$new_form = $form->copy( array(
			'terms'				=> true,
			'meta'				=> true,
			'comments'			=> false,
			'containers'		=> true,
			'elements'			=> true,
			'element_answers'	=> true,
			'element_settings'	=> true,
			'participants'		=> true,
			'as_draft'			=> true,
		) );
		if ( is_wp_error( $new_form ) ) {
			return $new_form;
		}

		$new_form_id = $new_form->id;

		$post = get_post( $new_form_id );

		$response = array(
			'form_id'    => $new_form_id,
			'post_title' => $post->post_title,
			'admin_url'  => admin_url( 'post.php?post=' . $new_form_id . '&action=edit' ),
		);

		return $response;
	}

	/**
	 * Deleting responses of a form
	 *
	 * @param $data
	 *
	 * @return array|Torro_Error
	 * @since 1.0.0
	 */
	public function ajax_delete_responses( $data ) {
		if ( ! isset( $data['form_id'] ) ) {
			return new Torro_Error( 'ajax_delete_responses_form_id_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'form_id' ) );
		}

		$form_id = absint( $data[ 'form_id' ] );

		$form = get_post( $form_id );

		if ( 'torro_form' !== $form->post_type ) {
			return new Torro_Error( 'ajax_delete_responses_invalid_form', __( 'The post is not a form.', 'torro-forms' ) );
		}

		$form = torro()->forms()->get( $form_id );
		$form->delete_responses();

		$entries = torro()->resulthandlers()->get_registered( 'entries' );
		if ( is_wp_error( $entries ) ) {
			return new Torro_Error( 'ajax_delete_responses_entries_error', __( 'Error retrieving the entries handler.', 'torro-forms' ) );
		}

		$response = array(
			'form_id'	=> $form_id,
			'deleted'	=> true,
			'html'		=> $entries->show_not_found_notice(),
		);

		return $response;
	}

	/**
	 * Get editor HTML
	 *
	 * @param $data
	 *
	 * @return array|Torro_Error
	 * @since 1.0.0
	 */
	public function ajax_get_editor_html( $data ) {
		if ( ! isset( $data['element_id'] ) ) {
			return new Torro_Error( 'ajax_get_editor_html_element_id_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'element_id' ) );
		}

		if ( ! isset( $data['field_name'] ) ) {
			return new Torro_Error( 'ajax_get_editor_html_field_name_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'field_name' ) );
		}

		$editor_id = 'wp_editor_' . $data['element_id'];
		$field_name = $data['field_name'];
		$message = isset( $data['message'] ) ? $data['message'] : '';

		return Torro_AJAX_WP_Editor::get( $message, $editor_id, array(
			'textarea_name'		=> $field_name,
		) );
	}
}
Torro_AJAX::instance();
