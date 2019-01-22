<?php
/**
 * Structure_WordPress class
 *
 * @package APIAPI\Structure_WordPress
 * @since 1.0.0
 */

namespace APIAPI\Structure_WordPress;

use APIAPI\Core\Structures\Structure;
use APIAPI\Core\Transporters\Transporter;
use APIAPI\Core\Request\Request;
use APIAPI\Core\Request\Response;
use APIAPI\Core\Request\Method;
use APIAPI\Core\Exception;

if ( ! class_exists( 'APIAPI\Structure_WordPress\Structure_WordPress' ) ) {

	/**
	 * Structure implementation for the API of any WordPress site.
	 *
	 * @since 1.0.0
	 */
	class Structure_WordPress extends Structure {
		/**
		 * Callback to get a cached structure response.
		 *
		 * @since 1.0.0
		 * @var callable|null
		 */
		protected $get_cached_structure_callback = null;

		/**
		 * Callback to update the structure response in cache.
		 *
		 * @since 1.0.0
		 * @var callable|null
		 */
		protected $update_cached_structure_callback = null;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name     Slug of the instance.
		 * @param string $base_uri Base URI to the website's API.
		 * @param array  $args     {
		 *     Optional. Array of arguments. Default empty array.
		 *
		 *     @type callable $get_cached_structure_callback    Callback to get a cached structure response.
		 *                                                      Must accept the base URI as sole parameter.
		 *                                                      Default null.
		 *     @type callable $update_cached_structure_callback Callback to update the structure response in
		 *                                                      cache. Must accept two parameters: The base
		 *                                                      URI as first and the structure array as second.
		 *                                                      Default null.
		 * }
		 */
		public function __construct( $name, $base_uri, array $args = array() ) {
			$this->base_uri = $base_uri;

			foreach ( $args as $key => $value ) {
				if ( isset( $this->$key ) ) {
					$this->$key = $value;
				}
			}

			parent::__construct( $name );
		}

		/**
		 * Sets up the API structure.
		 *
		 * This method should populate the routes array, and can also be used to
		 * handle further initialization functionality, like setting the authenticator
		 * class and default authentication data.
		 *
		 * @since 1.0.0
		 *
		 * @throws Exception Thrown when the discovery API response is invalid.
		 */
		protected function setup() {
			$structure_response = is_callable( $this->get_cached_structure_callback ) ? call_user_func( $this->get_cached_structure_callback, $this->base_uri ) : false;
			if ( ! is_array( $structure_response ) ) {
				$transporter = $this->get_default_transporter();

				$request = new Request( $this->base_uri, Method::GET );
				$response = new Response( $transporter->send_request( $request ) );

				if ( null === $response->get_param( 'routes' ) ) {
					throw new Exception( sprintf( 'The structure request to %s returned an invalid response.', $this->base_uri ) );
				}

				$structure_response = array(
					'title'          => $response->get_param( 'name' ),
					'description'    => $response->get_param( 'description' ),
					'namespaces'     => $response->get_param( 'namespaces' ),
					'authentication' => $response->get_param( 'authentication' ),
					'routes'         => $response->get_param( 'routes' ),
				);

				if ( ! is_array( $structure_response['namespaces'] ) ) {
					$namespace = $response->get_param( 'namespace' );
					if ( $namespace ) {
						$structure_response['namespaces'] = array( $namespace );
					} else {
						$structure_response['namespaces'] = array();
					}
				}

				if ( ! is_array( $structure_response['authentication'] ) ) {
					$structure_response['authentication'] = array();
				}

				if ( ! is_array( $structure_response['routes'] ) ) {
					$structure_response['routes'] = array();
				}

				if ( is_callable( $this->update_cached_structure_callback ) ) {
					call_user_func( $this->update_cached_structure_callback, $this->base_uri, $structure_response );
				}
			}

			if ( $structure_response['title'] ) {
				$this->title = $structure_response['title'];
			} elseif ( strpos( $this->base_uri, 'wordpress.com' ) ) { // WordPress.com works in a special way.
				$this->title = 'WordPress.com';
			}

			if ( $structure_response['description'] ) {
				$this->description = $structure_response['description'];
			} elseif ( strpos( $this->base_uri, 'wordpress.com' ) ) { // WordPress.com works in a special way.
				$this->description = 'Blog web hosting service provider.';
			}

			if ( isset( $structure_response['authentication']['oauth1'] ) ) {
				$this->authenticator = 'oauth1';
				$this->authentication_data_defaults = $structure_response['authentication']['oauth1'];
			} else {
				$this->authenticator = 'x';
				$this->authentication_data_defaults = array(
					'header_name' => 'WP-Nonce',
				);
			}

			foreach ( $structure_response['routes'] as $uri => $data ) {
				/* Ignore basic namespace discovery endpoints. */
				if ( '/' === $uri || in_array( ltrim( $uri, '/' ), $structure_response['namespaces'], true ) ) {
					continue;
				}

				$route_data = array(
					'primary_params' => array(),
					'methods'        => array(),
				);

				$primary_param_names = array();
				if ( preg_match_all( '@(\/|^)\(\?P\<([A-Za-z_]+)\>\[(.+)\]\+\)@U', $uri, $matches ) ) {
					for ( $i = 0; $i < count( $matches[2] ); $i++ ) {
						$primary_param_names[] = $matches[2][ $i ];
					}
				}

				foreach ( $data['endpoints'] as $endpoint ) {
					$endpoint_params = $endpoint['args'];

					if ( ! empty( $primary_param_names ) ) {
						$new_primary_params = array_intersect_key( $endpoint_params, array_flip( $primary_param_names ) );
						if ( ! empty( $new_primary_params ) ) {
							$route_data['primary_params'] = array_merge( $route_data['primary_params'], $new_primary_params );
							$endpoint_params = array_diff_key( $endpoint_params, $new_primary_params );
						}
					}

					foreach ( $endpoint['methods'] as $method ) {
						$needs_authentication = true;
						if ( Method::GET === $method && 0 === strpos( $uri, '/wp/v2' ) ) {
							$needs_authentication = false;
						}

						$route_data['methods'][ $method ] = array(
							'params'                 => $endpoint_params,
							'supports_custom_params' => false,
							'request_data_type'      => 'raw',
							'needs_authentication'   => $needs_authentication,
						);
					}
				}

				$this->routes[ $uri ] = $route_data;
			}
		}

		/**
		 * Gets the default transporter object.
		 *
		 * @since 1.0.0
		 *
		 * @return Transporter Default transporter object.
		 */
		protected function get_default_transporter() {
			//TODO: This breaks the dependency injection pattern.
			return apiapi_manager()->transporters()->get_default();
		}
	}

}
