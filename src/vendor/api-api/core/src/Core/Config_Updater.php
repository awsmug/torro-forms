<?php
/**
 * API-API Config updater class
 *
 * @package APIAPI\Core
 * @since 1.0.0
 */

namespace APIAPI\Core;

use APIAPI\Core\Storages\Storage;

if ( ! class_exists( 'APIAPI\Core\Config_Updater' ) ) {

	/**
	 * Config updater class for the API-API.
	 *
	 * @since 1.0.0
	 */
	class Config_Updater {
		/**
		 * The API-API instance.
		 *
		 * @since 1.0.0
		 * @var APIAPI
		 */
		protected $apiapi;

		/**
		 * Slug of the API-API instance.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $apiapi_name = '';

		/**
		 * Configuration object this updater should manage.
		 *
		 * @since 1.0.0
		 * @var Config
		 */
		protected $config;

		/**
		 * Storage to persistently store configuration values.
		 *
		 * @since 1.0.0
		 * @var Storage
		 */
		protected $storage;

		/**
		 * Array of arguments.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $args = array();

		/**
		 * Structure names that are handled by this instance.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $structure_names = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param APIAPI  $apiapi  The API-API instance.
		 * @param Storage $storage Storage to persistently store configuration values.
		 * @param array   $args    Optional. Array of arguments. Default empty array.
		 */
		public function __construct( APIAPI $apiapi, Storage $storage, array $args = array() ) {
			$this->apiapi   = $apiapi;
			$this->config   = $this->apiapi->config();
			$this->storage  = $storage;
			$this->args     = Util::parse_args( $args, $this->get_defaults() );

			if ( ! empty( $this->args['setup_config_hook'] ) ) {
				$this->apiapi->hook_on( $this->args['setup_config_hook'], function() {
					$this->setup_config();
				});
			} else {
				$this->setup_config();
			}

			if ( ! empty( $this->args['listener_hook'] ) ) {
				$this->apiapi->hook_on( $this->args['listener_hook'], function() {
					$this->listen_for_callback();
				});
			} else {
				$this->listen_for_callback();
			}
		}

		/**
		 * Magic call method.
		 *
		 * Routes dynamic callback method names to their actual implemented methods.
		 *
		 * @since 1.0.0
		 *
		 * @param string $method_name Method name.
		 * @param array  $args        Method arguments.
		 */
		public function __call( $method_name, array $args ) {
			if ( preg_match( '/^apply_([a-z_]+)_(permanent|temporary)_token_callback$/U', $method_name, $matches ) ) {
				$args[] = $matches[1]; // $structure_name
				$args[] = $matches[2]; // $type

				call_user_func_array( array( $this, 'apply_token_callback' ), $args );
			} elseif ( preg_match( '/^redirect_([a-z_]+)_callback$/U', $method_name, $matches ) ) {
				$args[] = $matches[1]; // $structure_name

				call_user_func_array( array( $this, 'redirect_callback' ), $args );
			}
		}

		/**
		 * Sets up dynamic configuration values from storage.
		 *
		 * @since 1.0.0
		 */
		protected function setup_config() {
			$oauth1_fields = array(
				'temporary_token',
				'temporary_token_secret',
				'temporary_token_verifier',
				'token',
				'token_secret',
			);

			$oauth1_required_fields = array(
				'consumer_key',
				'consumer_secret',
			);

			$base_url = $this->get_callback_base_url();
			if ( strpos( $base_url, '?' ) ) {
				$base_url .= '&';
			} else {
				$base_url .= '?';
			}

			foreach ( $this->config->get_params() as $key => $value ) {
				if ( ! is_array( $value ) ) {
					continue;
				}

				if ( ! isset( $value['authentication_data'] ) ) {
					continue;
				}

				if ( ! is_array( $value['authentication_data'] ) ) {
					continue;
				}

				$required_exist = true;
				foreach ( $oauth1_required_fields as $required_field ) {
					if ( empty( $value['authentication_data'][ $required_field ] ) ) {
						$required_exist = false;
						break;
					}
				}

				if ( ! $required_exist ) {
					continue;
				}

				$this->structure_names[] = $key;

				if ( empty( $value['authentication_data']['token'] ) || empty( $value['authentication_data']['token_secret'] ) ) {
					$values = $this->storage->retrieve_multi( $this->args['auth_basename'], $key, $oauth1_fields );

					foreach ( $values as $k => $v ) {
						if ( $v ) {
							$value['authentication_data'][ $k ] = $v;
						}
					}
				}

				$value['authentication_data']['callback'] = $base_url . $this->args['listener_query_var'] . '=' . $key;

				$value['authentication_data']['apply_token_callback']           = array( $this, 'apply_' . $key . '_permanent_token_callback' );
				$value['authentication_data']['apply_temporary_token_callback'] = array( $this, 'apply_' . $key . '_temporary_token_callback' );

				if ( empty( $value['authentication_data']['authorize_redirect_callback'] ) ) {
					$value['authentication_data']['authorize_redirect_callback'] = array( $this, 'redirect_' . $key . '_callback' );
				}

				$this->config->set( $key, 'authentication_data', $value['authentication_data'] );
			}
		}

		/**
		 * Listens for OAuth1 callbacks and persistently stores the necessary data.
		 *
		 * @since 1.0.0
		 */
		protected function listen_for_callback() {
			if ( ! isset( $_GET[ $this->args['listener_query_var'] ] ) ) {
				return;
			}

			$structure_name = stripslashes( $_GET[ $this->args['listener_query_var'] ] );
			if ( ! in_array( $structure_name, $this->structure_names, true ) ) {
				return;
			}

			if ( ! isset( $_GET['oauth_token'] ) || ! isset( $_GET['oauth_verifier'] ) ) {
				return;
			}

			$temporary_token = $_GET['oauth_token'];
			$temporary_token_verifier = $_GET['oauth_verifier'];

			$authentication_data = $this->config->get( $structure_name, 'authentication_data' );
			if ( ! is_array( $authentication_data ) || ! isset( $authentication_data['temporary_token'] ) ) {
				return;
			}

			if ( $temporary_token !== $authentication_data['temporary_token'] ) {
				return;
			}

			$authentication_data['temporary_token_verifier'] = $temporary_token_verifier;

			$this->storage->store( $this->args['auth_basename'], $structure_name, 'temporary_token_verifier', $temporary_token_verifier );

			$this->config->set( $structure_name, 'authentication_data', $authentication_data );
		}

		/**
		 * Callback to apply a token.
		 *
		 * @since 1.0.0
		 *
		 * @param string $consumer_key    Consumer key.
		 * @param string $consumer_secret Consumer secret.
		 * @param string $token           Token.
		 * @param string $token_secret    Token secret.
		 * @param string $structure_name  Name of the structure.
		 * @param string $type            Optional. Type of the token. Either 'temporary' or 'permanent'.
		 *                                Default 'permanent'.
		 */
		protected function apply_token_callback( $consumer_key, $consumer_secret, $token, $token_secret, $structure_name, $type = 'permanent' ) {
			if ( ! in_array( $structure_name, $this->structure_names, true ) ) {
				return;
			}

			$authentication_data = $this->config->get( $structure_name, 'authentication_data' );
			if ( $authentication_data['consumer_key'] !== $consumer_key || $authentication_data['consumer_secret'] !== $consumer_secret ) {
				return;
			}

			$token_values = array();

			if ( 'temporary' === $type ) {
				$token_values = array_merge( $token_values, array(
					'temporary_token'        => $token,
					'temporary_token_secret' => $token_secret,
				) );
			} else {
				$token_values = array_merge( $token_values, array(
					'token'        => $token,
					'token_secret' => $token_secret,
				) );

				$this->storage->delete_multi( $this->args['auth_basename'], $structure_name, array(
					'temporary_token',
					'temporary_token_secret',
					'temporary_token_verifier',
				) );

				unset( $authentication_data['temporary_token'] );
				unset( $authentication_data['temporary_token_secret'] );
				unset( $authentication_data['temporary_token_verifier'] );
			}

			$authentication_data = array_merge( $authentication_data, $token_values );

			$this->storage->store_multi( $this->args['auth_basename'], $structure_name, $token_values );

			$this->config->set( $structure_name, 'authentication_data', $authentication_data );
		}

		/**
		 * Redirect callback to send the user to an authorize URL.
		 *
		 * @since 1.0.0
		 *
		 * @param string $authorize_url  Authorize URL.
		 * @param string $structure_name Name of the structure.
		 */
		protected function redirect_callback( $authorize_url, $structure_name ) {
			header( 'Location: ' . $authorize_url );
			exit;
		}

		/**
		 * Returns the base URL to use for authentication callbacks.
		 *
		 * If the 'callback_base_url' argument is not set, the current URL will be used.
		 *
		 * @since 1.0.0
		 *
		 * @return string Callback base URL for authentication redirects.
		 */
		protected function get_callback_base_url() {
			if ( ! empty( $this->args['callback_base_url'] ) ) {
				return $this->args['callback_base_url'];
			}

			return ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		}

		/**
		 * Returns default values for the arguments array.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of default `$key => $value` pairs.
		 */
		protected function get_defaults() {
			return array(
				'listener_query_var' => 'apiapi_' . $this->apiapi->get_name() . '_callback',
				'auth_basename'      => 'apiapi_' . $this->apiapi->get_name() . '_config_auth',
				'callback_base_url'  => '',
				'setup_config_hook'  => '',
				'listener_hook'      => '',
			);
		}
	}

}
