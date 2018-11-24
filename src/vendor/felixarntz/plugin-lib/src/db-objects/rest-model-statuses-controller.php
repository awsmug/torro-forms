<?php
/**
 * REST model statuses controller class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\REST_Model_Statuses_Controller' ) ) :

	/**
	 * Class to access model statuses via the REST API.
	 *
	 * @since 1.0.0
	 */
	class REST_Model_Statuses_Controller extends WP_REST_Controller {
		/**
		 * The manager instance.
		 *
		 * @since 1.0.0
		 * @var Manager
		 */
		protected $manager;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param Manager $manager The manager instance.
		 */
		public function __construct( $manager ) {
			$this->manager = $manager;

			$prefix = $this->manager->get_prefix();
			if ( '_' === substr( $prefix, -1 ) ) {
				$prefix = substr( $prefix, 0, -1 );
			}

			$this->namespace = $prefix;
			$this->rest_base = $this->manager->get_plural_slug() . '/statuses';
		}

		/**
		 * Registers the routes for the objects of the controller.
		 *
		 * @since 1.0.0
		 */
		public function register_routes() {
			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base,
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_items' ),
						'permission_callback' => array( $this, 'get_items_permissions_check' ),
						'args'                => $this->get_collection_params(),
					),
					'schema' => array( $this, 'get_public_item_schema' ),
				)
			);

			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base . '/(?P<slug>[\w-]+)',
				array(
					'args'   => array(
						'slug' => array(
							'description' => $this->manager->get_message( 'rest_status_slug_description' ),
							'type'        => 'string',
						),
					),
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_item' ),
						'permission_callback' => array( $this, 'get_item_permissions_check' ),
						'args'                => array(
							'context' => $this->get_context_param( array( 'default' => 'view' ) ),
						),
					),
					'schema' => array( $this, 'get_public_item_schema' ),
				)
			);
		}

		/**
		 * Checks whether a given request has permission to read statuses.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return WP_Error|true True if the request has read access, WP_Error object otherwise.
		 */
		public function get_items_permissions_check( $request ) {
			$capabilities = $this->manager->capabilities();

			if ( 'edit' === $request['context'] && ( ! $capabilities || ! $capabilities->user_can_edit() ) ) {
				return new WP_Error( 'rest_cannot_edit_statuses', $this->manager->get_message( 'rest_cannot_edit_statuses' ), array( 'status' => rest_authorization_required_code() ) );
			}

			if ( ! $this->manager->is_public() && ( ! $capabilities || ! $capabilities->user_can_read() ) ) {
				return new WP_Error( 'rest_cannot_read_statuses', $this->manager->get_message( 'rest_cannot_read_statuses' ), array( 'status' => rest_authorization_required_code() ) );
			}

			return true;
		}

		/**
		 * Retrieves a collection of model statuses.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
		 */
		public function get_items( $request ) {
			$args = 'edit' === $request['context'] ? array() : array( 'public' => true );

			$data = array();

			foreach ( $this->manager->statuses()->query( $args ) as $obj ) {
				$status = $this->prepare_item_for_response( $obj, $request );

				$data[ $obj->slug ] = $this->prepare_response_for_collection( $status );
			}

			return rest_ensure_response( $data );
		}

		/**
		 * Checks whether a given request has permission to read a status.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return WP_Error|true True if the request has read access, WP_Error object otherwise.
		 */
		public function get_item_permissions_check( $request ) {
			$capabilities = $this->manager->capabilities();

			if ( 'edit' === $request['context'] && ( ! $capabilities || ! $capabilities->user_can_edit() ) ) {
				return new WP_Error( 'rest_cannot_edit_status', $this->manager->get_message( 'rest_cannot_edit_status' ), array( 'status' => rest_authorization_required_code() ) );
			}

			$obj = $this->manager->statuses()->get( $request['slug'] );

			if ( ! $obj || ( ! $obj->public && 'edit' !== $request['context'] ) ) {
				return new WP_Error( 'rest_invalid_status_slug', $this->manager->get_message( 'rest_invalid_status_slug' ), array( 'status' => 404 ) );
			}

			if ( ! $this->manager->is_public() && ( ! $capabilities || ! $capabilities->user_can_read() ) ) {
				return new WP_Error( 'rest_cannot_read_status', $this->manager->get_message( 'rest_cannot_read_status' ), array( 'status' => rest_authorization_required_code() ) );
			}

			return true;
		}

		/**
		 * Retrieves a specific model status.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
		 */
		public function get_item( $request ) {
			$obj = $this->manager->statuses()->get( $request['slug'] );

			if ( ! $obj || ( ! $obj->public && 'edit' !== $request['context'] ) ) {
				return new WP_Error( 'rest_invalid_status_slug', $this->manager->get_message( 'rest_invalid_status_slug' ), array( 'status' => 404 ) );
			}

			$data = $this->prepare_item_for_response( $obj, $request );

			return rest_ensure_response( $data );
		}

		/**
		 * Prepares a model status for response.
		 *
		 * @since 1.0.0
		 *
		 * @param Model_Status    $model_status Model status object.
		 * @param WP_REST_Request $request      Request object.
		 * @return WP_REST_Response Response object.
		 */
		public function prepare_item_for_response( $model_status, $request ) {
			if ( method_exists( $this, 'get_fields_for_response' ) ) {
				$fields = $this->get_fields_for_response( $request );
			} else {
				$schema = $this->get_item_schema();
				$fields = array_keys( $schema['properties'] );
			}

			$data = array();

			foreach ( $fields as $property ) {
				$data[ $property ] = $model_status->$property;
			}

			$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

			$data = $this->filter_response_by_context( $data, $context );

			$response = rest_ensure_response( $data );

			$response->add_links( $this->prepare_links( $model_status ) );

			return $response;
		}

		/**
		 * Prepares links for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param Model_Status $model_status Model status object.
		 * @return array Links for the given model status.
		 */
		protected function prepare_links( $model_status ) {
			$base = sprintf( '%s/%s', $this->namespace, $this->rest_base );

			$links = array(
				'self'                    => array(
					'href' => rest_url( trailingslashit( $base ) . $model_status->slug ),
				),
				'collection'              => array(
					'href' => rest_url( $base ),
				),
				'https://api.w.org/items' => array(
					'href' => rest_url( substr( $base, 0, -9 ) . '?' . $this->manager->get_status_property() . '=' . $model_status->slug ),
				),
			);

			return $links;
		}

		/**
		 * Retrieves the model status' schema, conforming to JSON Schema.
		 *
		 * @since 1.0.0
		 *
		 * @return array Model status schema data.
		 */
		public function get_item_schema() {
			$schema = array(
				'$schema'    => 'http://json-schema.org/schema#',
				'title'      => $this->manager->get_singular_slug() . '_status',
				'type'       => 'object',
				'properties' => array(
					'slug'     => array(
						'description' => $this->manager->get_message( 'rest_status_slug_description' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit', 'embed' ),
						'readonly'    => true,
					),
					'label'    => array(
						'description' => $this->manager->get_message( 'rest_status_label_description' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit', 'embed' ),
						'readonly'    => true,
					),
					'public'   => array(
						'description' => $this->manager->get_message( 'rest_status_public_description' ),
						'type'        => 'boolean',
						'context'     => array( 'edit' ),
						'readonly'    => true,
					),
					'internal' => array(
						'description' => $this->manager->get_message( 'rest_status_internal_description' ),
						'type'        => 'boolean',
						'context'     => array( 'edit' ),
						'readonly'    => true,
					),
					'default'  => array(
						'description' => $this->manager->get_message( 'rest_status_default_description' ),
						'type'        => 'boolean',
						'context'     => array( 'edit' ),
						'readonly'    => true,
					),
				),
			);

			return $schema;
		}

		/**
		 * Retrieves the query params for collections.
		 *
		 * @since 1.0.0
		 *
		 * @return array Collection parameters.
		 */
		public function get_collection_params() {
			return array(
				'context' => $this->get_context_param( array( 'default' => 'view' ) ),
			);
		}
	}

endif;
