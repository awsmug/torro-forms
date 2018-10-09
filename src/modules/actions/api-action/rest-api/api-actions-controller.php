<?php
/**
 * REST API actions controller class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Actions\API_Action\REST_API;

use awsmug\Torro_Forms\Modules\Actions\API_Action\API_Action;
use awsmug\Torro_Forms\Modules\Actions\Module;
use APIAPI\Core\Structures\Structure;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class to access API actions via the REST API.
 *
 * @since 1.1.0
 */
class API_Actions_Controller extends WP_REST_Controller {

	/**
	 * Actions module managing registered API actions.
	 *
	 * @since 1.1.0
	 * @var Module
	 */
	protected $module;

	/**
	 * Constructor.
	 *
	 * Sets the actions module.
	 *
	 * @since 1.1.0
	 *
	 * @param Module $module The actions module managing registered API actions.
	 */
	public function __construct( Module $module ) {
		$this->module = $module;

		$prefix = $this->module->get_prefix();
		if ( '_' === substr( $prefix, -1 ) ) {
			$prefix = substr( $prefix, 0, -1 );
		}

		$this->namespace = $prefix . '/v1';
		$this->rest_base = 'api_actions';
	}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since 1.1.0
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
			'/' . $this->rest_base . '/(?P<action>[\w-]+)',
			array(
				'args'   => array(
					'action' => array(
						'description' => __( 'The API action slug.', 'torro-forms' ),
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
	 * Checks if a given request has access to read API actions.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		// Check whether the user can edit forms, since there is no capability for API actions.
		if ( ! $this->current_user_can_edit_forms() ) {
			if ( 'edit' === $request['context'] ) {
				return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit API actions.', 'torro-forms' ), array( 'status' => rest_authorization_required_code() ) );
			}
			return new WP_Error( 'rest_cannot_read_items', __( 'Sorry, you are not allowed to view API actions.', 'torro-forms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Retrieves a collection of API actions.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$actions = array_filter(
			$this->module->get_all(),
			function( $action ) {
				return $action instanceof API_Action;
			}
		);

		$results = array();

		foreach ( $actions as $action ) {
			$data      = $this->prepare_item_for_response( $action, $request );
			$results[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $results );

		$response->header( 'X-WP-Total', count( $actions ) );
		$response->header( 'X-WP-TotalPages', 1 );

		return $response;
	}

	/**
	 * Checks if a given request has access to read an API action.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if the request has read access for the API action, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		// Check whether the user can edit forms, since there is no capability for API actions.
		if ( ! $this->current_user_can_edit_forms() ) {
			if ( 'edit' === $request['context'] ) {
				return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit this API action.', 'torro-forms' ), array( 'status' => rest_authorization_required_code() ) );
			}
			return new WP_Error( 'rest_cannot_read_item', __( 'Sorry, you are not allowed to view this API action.', 'torro-forms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Retrieves a single API action.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$action = $this->module->get( $request['action'] );
		if ( is_wp_error( $action ) || ! $action instanceof API_Action ) {
			return new WP_Error( 'rest_invalid_slug', __( 'Invalid API action slug.', 'torro-forms' ), array( 'status' => 404 ) );
		}

		$data     = $this->prepare_item_for_response( $action, $request );
		$response = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * Prepares a single API action output for response.
	 *
	 * @since 1.1.0
	 *
	 * @param API_Action      $action  API action object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $action, $request ) {
		$data = array(
			'slug'        => $action->get_slug(),
			'title'       => $action->get_title(),
			'description' => $action->get_description(),
			'structures'  => array(),
		);

		// This is only available since WordPress 4.9.
		if ( method_exists( $this, 'get_fields_for_response' ) ) {
			$fields = $this->get_fields_for_response( $request );
			$data   = array_intersect_key( $data, array_flip( $fields ) );
		}

		if ( isset( $data['structures'] ) ) {
			$structures = $action->get_available_structures();
			foreach ( $structures as $structure_slug => $structure_data ) {
				$api_structure = $action->api_structure( $structure_slug );

				$authentication_type   = $this->get_structure_authentication_type( $api_structure );
				$authentication_fields = array();
				if ( ! empty( $authentication_type ) ) {
					$connection_types = API_Action::get_registered_connection_types();
					if ( isset( $connection_types[ $authentication_type ] ) ) {
						$connection_fields            = call_user_func( array( $connection_types[ $authentication_type ], 'get_authenticator_fields' ) );
						$authentication_data_defaults = $action->get_authentication_data_defaults( $structure_slug );
						foreach ( $connection_fields as $field_slug => $field_data ) {
							if ( ! empty( $field_data['readonly'] ) ) {
								continue;
							}

							if ( isset( $authentication_data_defaults[ $field_slug ] ) ) {
								continue;
							}

							$authentication_fields[] = $field_slug;
						}
					}
				}

				$data['structures'][] = array(
					'slug'                  => $structure_slug,
					'title'                 => $structure_data['title'],
					'authentication_type'   => $authentication_type,
					'authentication_fields' => $authentication_fields,
				);
			}
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $action ) );

		return $response;
	}

	/**
	 * Prepares links for the request.
	 *
	 * @since 1.1.0
	 *
	 * @param API_Action $action API action object.
	 * @return array Links for the given API action.
	 */
	protected function prepare_links( $action ) {
		$base = sprintf( '%s/%s', $this->namespace, $this->rest_base );

		return array(
			'self'        => array(
				'href' => rest_url( trailingslashit( $base ) . $action->get_slug() ),
			),
			'collection'  => array(
				'href' => rest_url( $base ),
			),
			'connections' => array(
				'href'       => rest_url( trailingslashit( $base ) . $action->get_slug() . '/connections' ),
				'embeddable' => true,
			),
		);
	}

	/**
	 * Retrieves the API action's schema, conforming to JSON Schema.
	 *
	 * @since 1.1.0
	 *
	 * @return array API action schema data.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => 'api_action',
			'type'       => 'object',
			'properties' => array(),
		);

		$schema['properties']['slug'] = array(
			'description' => __( 'The API action slug.', 'torro-forms' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit', 'embed' ),
			'readonly'    => true,
		);

		$schema['properties']['title'] = array(
			'description' => __( 'The API action title.', 'torro-forms' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit', 'embed' ),
			'readonly'    => true,
		);

		$schema['properties']['description'] = array(
			'description' => __( 'The API action description.', 'torro-forms' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit', 'embed' ),
			'readonly'    => true,
		);

		$schema['properties']['structures'] = array(
			'description' => __( 'The available structures for the API action.', 'torro-forms' ),
			'type'        => 'array',
			'items'       => array(
				'type'                 => 'object',
				'properties'           => array(
					'slug'                  => array(
						'description' => __( 'The API structure slug.', 'torro-forms' ),
						'type'        => 'string',
					),
					'title'                 => array(
						'description' => __( 'The API structure title.', 'torro-forms' ),
						'type'        => 'string',
					),
					'authentication_type'   => array(
						'description' => __( 'The API structure authentication type.', 'torro-forms' ),
						'type'        => 'string',
						'enum'        => array_merge( array_keys( API_Action::get_registered_connection_types() ), array( '' ) ),
					),
					'authentication_fields' => array(
						'description' => __( 'The API structure authentication fields.', 'torro-forms' ),
						'type'        => 'array',
						'items'       => array(
							'type' => 'string',
						),
					),
				),
				'additionalProperties' => false,
			),
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		return $schema;
	}

	/**
	 * Retrieves the query params for the API actions collection.
	 *
	 * @since 1.1.0
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		return array(
			'context' => $this->get_context_param( array( 'default' => 'view' ) ),
		);
	}

	/**
	 * Checks whether the current user has permissions to edit forms.
	 *
	 * Only users who can edit forms are granted permissions to view API actions.
	 *
	 * @since 1.1.0
	 *
	 * @return bool True if the current user has sufficient permissions, false otherwise.
	 */
	protected function current_user_can_edit_forms() {
		$capabilities = $this->module->manager()->forms()->capabilities();

		return $capabilities && $capabilities->user_can_edit();
	}

	/**
	 * Gets the authentication type required for authenticating with a given API structure.
	 *
	 * @since 1.1.0
	 *
	 * @param Structure $structure API-API structure instance.
	 * @return string One of the registered connection types, or empty string if none found.
	 */
	protected function get_structure_authentication_type( Structure $structure ) {
		$authentication_types = array_keys( API_Action::get_registered_connection_types() );

		$authenticator = $structure->get_authenticator();

		// First, try matching exactly.
		if ( in_array( $authenticator, $authentication_types, true ) ) {
			return $authenticator;
		}

		// Then, try matching a substring.
		foreach ( $authentication_types as $authentication_type ) {
			if ( false !== strpos( $authenticator, $authentication_type ) ) {
				return $authentication_type;
			}
		}

		return '';
	}
}

