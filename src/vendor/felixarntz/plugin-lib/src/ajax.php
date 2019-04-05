<?php
/**
 * AJAX handler class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib;

use Leaves_And_Love\Plugin_Lib\Traits\Hook_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Traits\Translations_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Translations\Translations_AJAX;
use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\AJAX' ) ) :

	/**
	 * Class for handling AJAX requests.
	 *
	 * @since 1.0.0
	 */
	class AJAX extends Service {
		use Hook_Service_Trait, Translations_Service_Trait;

		/**
		 * Registered AJAX actions, as `$name => $args` pairs.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $ajax_actions = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string            $prefix       The prefix for all AJAX actions.
		 * @param Translations_AJAX $translations Translations instance.
		 */
		public function __construct( $prefix, $translations ) {
			$this->set_prefix( $prefix );
			$this->set_translations( $translations );

			$this->setup_hooks();
		}

		/**
		 * Registers an action.
		 *
		 * @since 1.0.0
		 *
		 * @param string   $name     Action name.
		 * @param callable $callback Callback to handle the action. Must return a response or an error object.
		 *                           Instead of a fully qualified function name, a string referencing to a method
		 *                           inside this class can be passed as well. The method must then have the same
		 *                           name prefixed with 'ajax_' in order to work properly.
		 * @param array    $args     Optional. Additional arguments. 'nopriv' is the only supported key.
		 * @return bool|WP_Error True on success, error object on failure.
		 */
		public function register_action( $name, $callback, $args = array() ) {
			if ( doing_action( 'admin_init' ) || did_action( 'admin_init' ) ) {
				return new WP_Error( 'ajax_registered_too_late', sprintf( $this->get_translation( 'ajax_registered_too_late' ), '<code>admin_init</code>' ) );
			}

			if ( ! $name || isset( $this->ajax_actions[ $name ] ) ) {
				return new WP_Error( 'ajax_invalid_action_name', $this->get_translation( 'ajax_invalid_action_name' ) );
			}

			$nonce = function_exists( 'wp_create_nonce' ) ? wp_create_nonce( $this->get_nonce_action( $name ) ) : '';

			$args = wp_parse_args(
				$args,
				array(
					'callback' => $callback,
					'nopriv'   => false,
					'nonce'    => $nonce,
				)
			);

			$this->ajax_actions[ $name ] = $args;

			return true;
		}

		/**
		 * Handles an AJAX request.
		 *
		 * @since 1.0.0
		 */
		public function request() {
			$request_data = wp_unslash( $_REQUEST ); // phpcs:ignore WordPress.Security.NonceVerification

			$name = str_replace( $this->get_prefix(), '', $request_data['action'] );

			if ( ! isset( $this->ajax_actions[ $name ] ) ) {
				wp_send_json_error( $this->get_translation( 'ajax_request_invalid_action' ) );
			}

			$callback = $this->ajax_actions[ $name ]['callback'];
			if ( is_string( $callback ) && ! is_callable( $callback ) ) {
				$callback = array( $this, 'ajax_' . $this->ajax_actions[ $name ]['callback'] );
			}

			if ( ! is_callable( $callback ) ) {
				wp_send_json_error( $this->get_translation( 'ajax_request_invalid_callback' ) );
			}

			$nonce_field = isset( $request_data['nonce'] ) ? 'nonce' : '_wpnonce';

			if ( ! check_ajax_referer( $this->get_nonce_action( $name ), $nonce_field, false ) ) {
				wp_send_json_error( $this->get_translation( 'ajax_request_invalid_nonce' ) );
			}

			$response = call_user_func( $callback, $request_data );

			if ( is_wp_error( $response ) ) {
				wp_send_json_error( $response->get_error_message() );
			}

			wp_send_json_success( $response );
		}

		/**
		 * Returns a nonce for a specific AJAX action.
		 *
		 * @since 1.0.0
		 *
		 * @param string $action Action name.
		 * @return string Nonce for the action, or empty string if action not registered.
		 */
		public function get_nonce( $action ) {
			if ( ! isset( $this->ajax_actions[ $action ] ) ) {
				return '';
			}

			if ( empty( $this->ajax_actions[ $action ]['nonce'] ) ) {
				if ( ! function_exists( 'wp_create_nonce' ) ) {
					return '';
				}

				$this->ajax_actions[ $action ]['nonce'] = wp_create_nonce( $this->get_nonce_action( $action ) );
			}

			return $this->ajax_actions[ $action ]['nonce'];
		}

		/**
		 * Adds AJAX action hooks.
		 *
		 * @since 1.0.0
		 */
		protected function add_ajax_actions() {
			if ( ! wp_doing_ajax() ) {
				return;
			}

			$prefix = $this->get_prefix();

			foreach ( $this->ajax_actions as $name => $args ) {
				add_action( 'wp_ajax_' . $prefix . $name, array( $this, 'request' ) );

				if ( $args['nopriv'] ) {
					add_action( 'wp_ajax_nopriv_' . $prefix . $name, array( $this, 'request' ) );
				}
			}
		}

		/**
		 * Returns the action name to use when working with nonces.
		 *
		 * @since 1.0.0
		 *
		 * @param string $action Action name.
		 * @return string Nonce action.
		 */
		protected function get_nonce_action( $action ) {
			return $this->get_prefix() . 'ajax_' . $action;
		}

		/**
		 * Sets up all action and filter hooks for the service.
		 *
		 * This method must be implemented and then be called from the constructor.
		 *
		 * @since 1.0.0
		 */
		protected function setup_hooks() {
			$this->actions = array(
				array(
					'name'     => 'admin_init',
					'callback' => array( $this, 'add_ajax_actions' ),
					'priority' => 10,
					'num_args' => 0,
				),
			);
		}
	}

endif;
