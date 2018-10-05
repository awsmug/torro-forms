<?php
/**
 * REST API action connections controller class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Actions\API_Action\REST_API;

use awsmug\Torro_Forms\Modules\Actions\API_Action\Connections\Connection;
use awsmug\Torro_Forms\Modules\Actions\API_Action\API_Action;
use awsmug\Torro_Forms\Modules\Actions\Module;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class to access API action connections via the REST API.
 *
 * @since 1.1.0
 */
class API_Action_Connections_Controller extends WP_REST_Controller {

	/**
	 * The base of the parent controller's route.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	private $parent_base;

	/**
	 * Actions module managing registered API action connections.
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
	 * @param Module $module The actions module managing registered API action connections.
	 */
	public function __construct( Module $module ) {
		$this->module = $module;

		$prefix = $this->module->get_prefix();
		if ( '_' === substr( $prefix, -1 ) ) {
			$prefix = substr( $prefix, 0, -1 );
		}

		$this->namespace   = $prefix . '/v1';
		$this->rest_base   = 'connections';
		$this->parent_base = 'api_actions';
	}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since 1.1.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->parent_base . '/(?P<action>[\w-]+)/' . $this->rest_base,
			array(
				'args'   => array(
					'action' => array(
						'description' => __( 'The API action slug the connections belong to.', 'torro-forms' ),
						'type'        => 'string',
					),
				),
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
			'/' . $this->parent_base . '/(?P<action>[\w-]+)/' . $this->rest_base . '/(?P<connection>[\w-]+)',
			array(
				'args'   => array(
					'action'     => array(
						'description' => __( 'The API action slug the connection belongs to.', 'torro-forms' ),
						'type'        => 'string',
					),
					'connection' => array(
						'description' => __( 'The API action connection slug.', 'torro-forms' ),
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
	 * Checks if a given request has access to read API action connections.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		// Check whether the user can edit forms, since there is no capability for API action connections.
		if ( ! $this->current_user_can_edit_forms() ) {
			if ( 'edit' === $request['context'] ) {
				return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit API action connections.', 'torro-forms' ), array( 'status' => rest_authorization_required_code() ) );
			}
			return new WP_Error( 'rest_cannot_read_items', __( 'Sorry, you are not allowed to view API action connections.', 'torro-forms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Retrieves a collection of API action connections.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$action = $this->module->get( $request['action'] );
		if ( is_wp_error( $action ) || ! $action instanceof API_Action ) {
			return new WP_Error( 'rest_invalid_action', __( 'Invalid API action slug.', 'torro-forms' ), array( 'status' => 400 ) );
		}

		$connections = $action->get_available_connections();

		$results = array();

		foreach ( $connections as $connection ) {
			$data      = $this->prepare_item_for_response( $connection, $request );
			$results[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $results );

		$response->header( 'X-WP-Total', count( $connections ) );
		$response->header( 'X-WP-TotalPages', 1 );

		return $response;
	}

	/**
	 * Checks if a given request has access to read an API action connection.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if the request has read access for the API action connection, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		// Check whether the user can edit forms, since there is no capability for API action connections.
		if ( ! $this->current_user_can_edit_forms() ) {
			if ( 'edit' === $request['context'] ) {
				return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit this API action connection.', 'torro-forms' ), array( 'status' => rest_authorization_required_code() ) );
			}
			return new WP_Error( 'rest_cannot_read_item', __( 'Sorry, you are not allowed to view this API action connection.', 'torro-forms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Retrieves a single API action connection.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$action = $this->module->get( $request['action'] );
		if ( is_wp_error( $action ) || ! $action instanceof API_Action ) {
			return new WP_Error( 'rest_invalid_action', __( 'Invalid API action slug.', 'torro-forms' ), array( 'status' => 400 ) );
		}

		$connections = $action->get_available_connections();
		if ( ! isset( $connections[ $request['connection'] ] ) ) {
			return new WP_Error( 'rest_invalid_slug', __( 'Invalid API action connection slug.', 'torro-forms' ), array( 'status' => 404 ) );
		}

		$connection = $connections[ $request['connection'] ];

		$data     = $this->prepare_item_for_response( $connection, $request );
		$response = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * Prepares a single API action connection output for response.
	 *
	 * @since 1.1.0
	 *
	 * @param Connection      $connection  API action connection object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $connection, $request ) {
		$data = array(
			'slug'                => $connection->get_slug(),
			'title'               => $connection->get_title(),
			'structure'           => $connection->get_structure(),
			'authentication_type' => $connection::TYPE,
			'authentication_data' => array(),
			'routes'              => array(),
		);

		// This is only available since WordPress 4.9.
		if ( method_exists( $this, 'get_fields_for_response' ) ) {
			$fields = $this->get_fields_for_response( $request );
			$data   = array_intersect_key( $data, array_flip( $fields ) );
		}

		if ( isset( $data['authentication_data'] ) ) {
			$data['authentication_data'] = $connection->get_authentication_data();
			if ( empty( $data['authentication_data'] ) ) {
				$data['authentication_data'] = new \stdClass(); // Force encoding as object.
			}
		}

		if ( isset( $data['routes'] ) ) {
			$routes = $connection->get_available_routes();
			foreach ( $routes as $route_slug => $route_data ) {
				$route = array(
					'slug'   => $route_slug,
					'title'  => $route_data['title'],
					'fields' => array(),
				);

				foreach ( $route_data['fields'] as $field_slug => $field_data ) {
					if ( empty( $field_data['properties'] ) ) {
						$field_data['properties'] = new \stdClass(); // Force encoding as object.
					}

					$route['fields'][] = array_merge(
						array( 'slug' => $field_slug ),
						$field_data
					);
				}

				$data['routes'][] = $route;
			}
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $connection ) );

		return $response;
	}

	/**
	 * Prepares links for the request.
	 *
	 * @since 1.1.0
	 *
	 * @param Connection $connection API action connection object.
	 * @return array Links for the given API action connection.
	 */
	protected function prepare_links( $connection ) {
		$api_action = $connection->get_api_action();

		$parent_base = sprintf( '%s/%s/%s', $this->namespace, $this->parent_base, $api_action->get_slug() );
		$base        = sprintf( '%s/%s', $parent_base, $this->rest_base );

		return array(
			'self'       => array(
				'href' => rest_url( trailingslashit( $base ) . $connection->get_slug() ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
			'parent'     => array(
				'href' => rest_url( $parent_base ),
			),
		);
	}

	/**
	 * Retrieves the API action connection's schema, conforming to JSON Schema.
	 *
	 * @since 1.1.0
	 *
	 * @return array API action connection schema data.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => 'api_action_connection',
			'type'       => 'object',
			'properties' => array(),
		);

		$schema['properties']['slug'] = array(
			'description' => __( 'The API action connection slug.', 'torro-forms' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit', 'embed' ),
			'readonly'    => true,
		);

		$schema['properties']['title'] = array(
			'description' => __( 'The API action connection title.', 'torro-forms' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit', 'embed' ),
			'readonly'    => true,
		);

		$schema['properties']['structure'] = array(
			'description' => __( 'The API action connection structure.', 'torro-forms' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit', 'embed' ),
			'readonly'    => true,
		);

		$schema['properties']['authentication_type'] = array(
			'description' => __( 'The API action connection authentication type.', 'torro-forms' ),
			'type'        => 'string',
			'enum'        => array_keys( API_Action::get_registered_connection_types() ),
			'context'     => array( 'view', 'edit', 'embed' ),
			'readonly'    => true,
		);

		$schema['properties']['authentication_data'] = array(
			'description'          => __( 'The API action connection authentication data.', 'torro-forms' ),
			'type'                 => 'object',
			'context'              => array( 'view', 'edit' ),
			'readonly'             => true,
			'additionalProperties' => array(
				'type' => 'string',
			),
		);

		$schema['properties']['routes'] = array(
			'description' => __( 'The available routes for the API action connection.', 'torro-forms' ),
			'type'        => 'array',
			'items'       => array(
				'type'                 => 'object',
				'properties'           => array(
					'slug'   => array(
						'description' => __( 'The API route slug.', 'torro-forms' ),
						'type'        => 'string',
					),
					'title'  => array(
						'description' => __( 'The API route title.', 'torro-forms' ),
						'type'        => 'string',
					),
					'fields' => array(
						'description' => __( 'The fields definition for the API route, based on its parameters.', 'torro-forms' ),
						'type'        => 'array',
						'items'       => array(
							'description' => __( 'The field definition for a single parameter field for the API route.', 'torro-forms' ),
							'type'        => 'object',
							'properties'  => array(
								'slug'        => array(
									'description' => __( 'The parameter path for the field. If nested, the parts are separated by -> characters.', 'torro-forms' ),
									'type'        => 'string',
								),
								'description' => array(
									'description' => __( 'A brief description for the field parameter.', 'torro-forms' ),
									'type'        => 'string',
								),
								'type'        => array(
									'description' => __( 'The type of the field parameter.', 'torro-forms' ),
									'type'        => 'string',
									'enum'        => array(
										'string',
										'integer',
										'float',
										'number',
										'boolean',
										'array',
										'object',
									),
								),
								'default'     => array(
									'description' => __( 'The default value for the parameter field, if any.', 'torro-forms' ),
									'default'     => null,
								),
								'location'    => array(
									'description' => __( 'The location of the field parameter in an API request.', 'torro-forms' ),
									'type'        => 'string',
									'enum'        => array(
										'',
										'query',
										'path',
									),
									'default'     => '',
								),
								'required'    => array(
									'description' => __( 'Whether the field parameter is required for every API request.', 'torro-forms' ),
									'type'        => 'boolean',
									'default'     => false,
								),
								'readonly'    => array(
									'description' => __( 'Whether the parameter is automatically determined and should not be available as a field.', 'torro-forms' ),
									'type'        => 'boolean',
									'default'     => false,
								),
								'enum'        => array(
									'description' => __( 'List of valid values for the field parameter, if applicable.', 'torro-forms' ),
									'type'        => 'array',
								),
								'items'       => array(
									'description' => __( 'Array items definition for the field parameter, if applicable.', 'torro-forms' ),
									'type'        => 'array',
								),
								'properties'  => array(
									'description' => __( 'Object properties definition for the field parameter, if applicable.', 'torro-forms' ),
									'type'        => 'object',
								),
							),
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
	 * Retrieves the query params for the API action connections collection.
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
	 * Only users who can edit forms are granted permissions to view API action connections.
	 *
	 * @since 1.1.0
	 *
	 * @return bool True if the current user has sufficient permissions, false otherwise.
	 */
	protected function current_user_can_edit_forms() {
		$capabilities = $this->module->manager()->forms()->capabilities();

		return $capabilities && $capabilities->user_can_edit();
	}
}

