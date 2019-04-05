<?php
/**
 * REST models controller class
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

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\REST_Models_Controller' ) ) :

	/**
	 * Class to access models via the REST API.
	 *
	 * @since 1.0.0
	 */
	abstract class REST_Models_Controller extends WP_REST_Controller {
		/**
		 * The manager instance.
		 *
		 * @since 1.0.0
		 * @var Manager
		 */
		protected $manager;

		/**
		 * Model types controller class name.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $types_controller_class_name = REST_Model_Types_Controller::class;

		/**
		 * Model statuses controller class name.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $statuses_controller_class_name = REST_Model_Statuses_Controller::class;

		/**
		 * REST model types controller.
		 *
		 * @since 1.0.0
		 * @var REST_Model_Types_Controller
		 */
		protected $types_controller;

		/**
		 * REST model statuses controller.
		 *
		 * @since 1.0.0
		 * @var REST_Model_Statuses_Controller
		 */
		protected $statuses_controller;

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
			$this->rest_base = $this->manager->get_plural_slug();

			if ( method_exists( $this->manager, 'get_type_property' ) ) {
				$class_name = $this->types_controller_class_name;

				$this->types_controller = new $class_name( $this->manager );
			}

			if ( method_exists( $this->manager, 'get_status_property' ) ) {
				$class_name = $this->statuses_controller_class_name;

				$this->statuses_controller = new $class_name( $this->manager );
			}
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
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'create_item' ),
						'permission_callback' => array( $this, 'create_item_permissions_check' ),
						'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
					),
					'schema' => array( $this, 'get_public_item_schema' ),
				)
			);

			$primary_property = $this->manager->get_primary_property();

			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base . '/(?P<' . $primary_property . '>[\d]+)',
				array(
					'args'   => array(
						$primary_property => array(
							'description' => $this->manager->get_message( 'rest_item_id_description' ),
							'type'        => 'integer',
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
					array(
						'methods'             => WP_REST_Server::EDITABLE,
						'callback'            => array( $this, 'update_item' ),
						'permission_callback' => array( $this, 'update_item_permissions_check' ),
						'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
					),
					array(
						'methods'             => WP_REST_Server::DELETABLE,
						'callback'            => array( $this, 'delete_item' ),
						'permission_callback' => array( $this, 'delete_item_permissions_check' ),
						'args'                => array(),
					),
					'schema' => array( $this, 'get_public_item_schema' ),
				)
			);

			if ( isset( $this->types_controller ) ) {
				$this->types_controller->register_routes();
			}

			if ( isset( $this->statuses_controller ) ) {
				$this->statuses_controller->register_routes();
			}
		}

		/**
		 * Checks if a given request has access to read models.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
		 */
		public function get_items_permissions_check( $request ) {
			$capabilities = $this->manager->capabilities();

			if ( 'edit' === $request['context'] ) {
				if ( ! $capabilities || ! $capabilities->user_can_edit() ) {
					return new WP_Error( 'rest_forbidden_context', $this->manager->get_message( 'rest_cannot_edit_items' ), array( 'status' => rest_authorization_required_code() ) );
				}

				return true;
			}

			if ( $this->manager->is_public() ) {
				if ( ! method_exists( $this->manager, 'get_status_property' ) ) {
					return true;
				}

				$public_statuses = $this->manager->statuses()->get_public();

				if ( ! empty( $public_statuses ) ) {
					$public = true;
					foreach ( (array) $request[ $this->manager->get_status_property() ] as $status ) {
						if ( ! in_array( $status, $public_statuses, true ) ) {
							$public = false;
							break;
						}
					}

					if ( $public ) {
						return true;
					}
				}
			}

			if ( ! $capabilities || ! $capabilities->user_can_read() ) {
				return new WP_Error( 'rest_cannot_read_items', $this->manager->get_message( 'rest_cannot_read_items' ), array( 'status' => rest_authorization_required_code() ) );
			}

			if ( method_exists( $this->manager, 'get_author_property' ) ) {
				$author_property = $this->manager->get_author_property();

				if ( ! empty( $request[ $author_property ] ) && get_current_user_id() !== $request[ $author_property ] && ( ! $capabilities || ! $capabilities->current_user_can( 'read_others_items' ) ) ) {
					return new WP_Error( 'rest_cannot_read_others_items', $this->manager->get_message( 'rest_cannot_read_others_items' ), array( 'status' => rest_authorization_required_code() ) );
				}
			}

			return true;
		}

		/**
		 * Retrieves a collection of models.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
		 */
		public function get_items( $request ) {
			$registered_args = $this->get_collection_params();

			$args = array();

			$special_args = array(
				'per_page' => 10,
				'page'     => 1,
				'orderby'  => $this->manager->get_primary_property(),
				'order'    => 'asc',
			);

			$date_query     = array();
			$date_query_map = array();

			if ( method_exists( $this->manager, 'get_all_date_properties' ) ) {
				$date_properties = $this->manager->get_all_date_properties();
			}

			foreach ( $registered_args as $property => $params ) {
				if ( ! isset( $request[ $property ] ) ) {
					continue;
				}

				if ( isset( $date_properties ) && isset( $params['format'] ) && 'date-time' === $params['format'] ) {
					$date_column = '';
					$mode        = '';

					if ( '_before' === substr( $property, -7 ) ) {
						$date_column = substr( $property, 0, -7 );
						$mode        = 'before';
					} elseif ( '_after' === substr( $property, -6 ) ) {
						$date_column = substr( $property, -6 );
						$mode        = 'after';
					}

					if ( ! empty( $date_column ) && ! empty( $mode ) && in_array( $date_column, $date_properties, true ) ) {
						if ( ! isset( $date_query_map[ $date_column ] ) ) {
							$date_query_map[ $date_column ]                          = count( $date_query );
							$date_query[ $date_query_map[ $date_column ] ]['column'] = $date_column;
						}

						$date_query[ $date_query_map[ $date_column ] ][ $mode ] = $request[ $property ];
						continue;
					}
				}

				if ( isset( $special_args[ $property ] ) ) {
					$special_args[ $property ] = $request[ $property ];
				} else {
					$args[ $property ] = $request[ $property ];
				}
			}

			if ( ! empty( $date_query ) ) {
				$args['date_query'] = $date_query;
			}

			$args['number']  = $special_args['per_page'];
			$args['offset']  = ( $special_args['page'] - 1 ) * $special_args['per_page'];
			$args['orderby'] = array( $special_args['orderby'] => $special_args['order'] );

			if ( ! $this->manager->is_public() && method_exists( $this->manager, 'get_author_property' ) ) {
				$author_property = $this->manager->get_author_property();

				if ( ! $capabilities || ! $capabilities->current_user_can( 'read_others_items' ) ) {
					$args[ $author_property ] = get_current_user_id();
				}
			}

			$collection = $this->manager->query( $args );

			$results = array();

			foreach ( $collection as $model ) {
				$data      = $this->prepare_item_for_response( $model, $request );
				$results[] = $this->prepare_response_for_collection( $data );
			}

			$page      = $special_args['page'];
			$total     = $collection->get_total();
			$max_pages = ceil( $total / $special_args['per_page'] );

			if ( $page > $max_pages && $total > 0 ) {
				return new WP_Error( 'rest_invalid_page_number', $this->manager->get_message( 'rest_invalid_page_number' ), array( 'status' => 400 ) );
			}

			$response = rest_ensure_response( $results );

			$response->header( 'X-WP-Total', (int) $total );
			$response->header( 'X-WP-TotalPages', (int) $max_pages );

			$base = add_query_arg( $request->get_query_params(), rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ) );

			if ( $page > 1 ) {
				$prev_page = $page - 1;
				if ( $prev_page > $max_pages ) {
					$prev_page = $max_pages;
				}

				$response->link_header( 'prev', add_query_arg( 'page', $prev_page, $base ) );
			}

			if ( $max_pages > $page ) {
				$next_page = $page + 1;

				$response->link_header( 'next', add_query_arg( 'next', $next_page, $base ) );
			}

			return $response;
		}

		/**
		 * Checks if a given request has access to read a model.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return bool|WP_Error True if the request has read access for the model, WP_Error object otherwise.
		 */
		public function get_item_permissions_check( $request ) {
			$primary_property = $this->manager->get_primary_property();

			$model = $this->manager->get( $request[ $primary_property ] );
			if ( ! $model ) {
				return new WP_Error( 'rest_invalid_id', $this->manager->get_message( 'rest_invalid_id' ), array( 'status' => 404 ) );
			}

			$capabilities = $this->manager->capabilities();

			if ( 'edit' === $request['context'] ) {
				if ( ! $capabilities || ! $capabilities->user_can_edit( null, $request[ $primary_property ] ) ) {
					return new WP_Error( 'rest_forbidden_context', $this->manager->get_message( 'rest_cannot_edit_item' ), array( 'status' => rest_authorization_required_code() ) );
				}

				return true;
			}

			if ( $this->manager->is_public() ) {
				if ( ! method_exists( $this->manager, 'get_status_property' ) ) {
					return true;
				}

				$public_statuses = $this->manager->statuses()->get_public();
				$status_property = $this->manager->get_status_property();

				if ( in_array( $model->$status_property, $public_statuses, true ) ) {
					return true;
				}
			}

			if ( ! $capabilities || ! $capabilities->user_can_read( null, $request[ $primary_property ] ) ) {
				return new WP_Error( 'rest_cannot_read_item', $this->manager->get_message( 'rest_cannot_read_item' ), array( 'status' => rest_authorization_required_code() ) );
			}

			return true;
		}

		/**
		 * Retrieves a single model.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
		 */
		public function get_item( $request ) {
			$primary_property = $this->manager->get_primary_property();

			$model = $this->manager->get( $request[ $primary_property ] );
			if ( ! $model ) {
				return new WP_Error( 'rest_invalid_id', $this->manager->get_message( 'rest_invalid_id' ), array( 'status' => 404 ) );
			}

			$data     = $this->prepare_item_for_response( $model, $request );
			$response = rest_ensure_response( $data );

			$permalink = $model->get_permalink();
			if ( ! empty( $permalink ) ) {
				$response->link_header( 'alternate', $permalink, array( 'type' => 'text/html' ) );
			}

			return $response;
		}

		/**
		 * Checks if a given request has access to create a model.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return true|WP_Error True if the request has access to create models, WP_Error object otherwise.
		 */
		public function create_item_permissions_check( $request ) {
			$primary_property = $this->manager->get_primary_property();

			if ( ! empty( $request[ $primary_property ] ) ) {
				return new WP_Error( 'rest_item_exists', $this->manager->get_message( 'rest_item_exists' ), array( 'status' => 400 ) );
			}

			$capabilities = $this->manager->capabilities();

			if ( ! $capabilities || ! $capabilities->user_can_create() ) {
				return new WP_Error( 'rest_cannot_create_items', $this->manager->get_message( 'rest_cannot_create_items' ), array( 'status' => rest_authorization_required_code() ) );
			}

			if ( method_exists( $this->manager, 'get_author_property' ) ) {
				$author_property = $this->manager->get_author_property();

				if ( ! empty( $request[ $author_property ] ) && get_current_user_id() !== $request[ $author_property ] && ( ! $capabilities || ! $capabilities->current_user_can( 'edit_others_items' ) ) ) {
					return new WP_Error( 'rest_cannot_create_others_items', $this->manager->get_message( 'rest_cannot_create_others_items' ), array( 'status' => rest_authorization_required_code() ) );
				}
			}

			return true;
		}

		/**
		 * Creates a single model.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
		 */
		public function create_item( $request ) {
			$primary_property = $this->manager->get_primary_property();

			if ( ! empty( $request[ $primary_property ] ) ) {
				return new WP_Error( 'rest_item_exists', $this->manager->get_message( 'rest_item_exists' ), array( 'status' => 400 ) );
			}

			$model = $this->prepare_item_for_database( $request );
			if ( is_wp_error( $model ) ) {
				return $model;
			}

			$result = $model->sync_upstream();

			if ( is_wp_error( $result ) ) {
				if ( 'db_insert_error' === $result->get_error_code() ) {
					$result->add_data( array( 'status' => 500 ) );
				} else {
					$result->add_data( array( 'status' => 400 ) );
				}

				return $result;
			}

			$request->set_param( 'context', 'edit' );

			$response = $this->prepare_item_for_response( $model, $request );
			$response = rest_ensure_response( $response );

			$response->set_status( 201 );
			$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $model->$primary_property ) ) );

			return $response;
		}

		/**
		 * Checks if a given request has access to update a model.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return true|WP_Error True if the request has access to update the item, WP_Error object otherwise.
		 */
		public function update_item_permissions_check( $request ) {
			$primary_property = $this->manager->get_primary_property();

			$model = $this->manager->get( $request[ $primary_property ] );
			if ( ! $model ) {
				return new WP_Error( 'rest_invalid_id', $this->manager->get_message( 'rest_invalid_id' ), array( 'status' => 404 ) );
			}

			$capabilities = $this->manager->capabilities();

			if ( ! $capabilities || ! $capabilities->user_can_edit( null, $request[ $primary_property ] ) ) {
				return new WP_Error( 'rest_cannot_edit_item', $this->manager->get_message( 'rest_cannot_edit_item' ), array( 'status' => rest_authorization_required_code() ) );
			}

			if ( method_exists( $this->manager, 'get_author_property' ) ) {
				$author_property = $this->manager->get_author_property();

				if ( ! empty( $request[ $author_property ] ) && get_current_user_id() !== $request[ $author_property ] && ( ! $capabilities || ! $capabilities->current_user_can( 'edit_others_items' ) ) ) {
					return new WP_Error( 'rest_cannot_edit_others_item', $this->manager->get_message( 'rest_cannot_edit_others_item' ), array( 'status' => rest_authorization_required_code() ) );
				}
			}

			return true;
		}

		/**
		 * Updates a single model.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
		 */
		public function update_item( $request ) {
			$model = $this->prepare_item_for_database( $request );
			if ( is_wp_error( $model ) ) {
				return $model;
			}

			$result = $model->sync_upstream();

			if ( is_wp_error( $result ) ) {
				if ( 'db_update_error' === $result->get_error_code() ) {
					$result->add_data( array( 'status' => 500 ) );
				} else {
					$result->add_data( array( 'status' => 400 ) );
				}

				return $result;
			}

			$request->set_param( 'context', 'edit' );

			$response = $this->prepare_item_for_response( $model, $request );
			$response = rest_ensure_response( $response );

			return $response;
		}

		/**
		 * Checks if a given request has access to delete a model.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return true|WP_Error True if the request has access to delete the item, WP_Error object otherwise.
		 */
		public function delete_item_permissions_check( $request ) {
			$primary_property = $this->manager->get_primary_property();

			$model = $this->manager->get( $request[ $primary_property ] );
			if ( ! $model ) {
				return new WP_Error( 'rest_invalid_id', $this->manager->get_message( 'rest_invalid_id' ), array( 'status' => 404 ) );
			}

			$capabilities = $this->manager->capabilities();

			if ( ! $capabilities || ! $capabilities->user_can_delete( null, $request[ $primary_property ] ) ) {
				return new WP_Error( 'rest_cannot_delete_item', $this->manager->get_message( 'rest_cannot_delete_item' ), array( 'status' => rest_authorization_required_code() ) );
			}

			return true;
		}

		/**
		 * Deletes a single model.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
		 */
		public function delete_item( $request ) {
			$primary_property = $this->manager->get_primary_property();

			$model = $this->manager->get( $request[ $primary_property ] );
			if ( ! $model ) {
				return new WP_Error( 'rest_invalid_id', $this->manager->get_message( 'rest_invalid_id' ), array( 'status' => 404 ) );
			}

			$request->set_param( 'context', 'edit' );

			$previous = $this->prepare_item_for_response( $model, $request );

			$result = $model->delete();

			if ( is_wp_error( $result ) ) {
				if ( 'db_delete_error' === $result->get_error_code() ) {
					$result->add_data( array( 'status' => 500 ) );
				} else {
					$result->add_data( array( 'status' => 400 ) );
				}

				return $result;
			}

			$response = new WP_REST_Response();
			$response->set_data(
				array(
					'deleted'  => true,
					'previous' => $previous->get_data(),
				)
			);

			return $response;
		}

		/**
		 * Prepares a single model for create or update.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Request object.
		 * @return Leaves_And_Love\Plugin_Lib\DB_Objects\Model|WP_Error The prepared model, or WP_Error object on failure.
		 */
		protected function prepare_item_for_database( $request ) {
			$primary_property = $this->manager->get_primary_property();

			if ( isset( $request[ $primary_property ] ) ) {
				$model = $this->manager->get( $request[ $primary_property ] );
				if ( ! $model ) {
					return new WP_Error( 'rest_invalid_id', $this->manager->get_message( 'rest_invalid_id' ), array( 'status' => 404 ) );
				}
			} else {
				$model = $this->manager->create();
			}

			$schema = $this->get_item_schema();

			foreach ( $schema['properties'] as $property => $params ) {
				if ( ! empty( $params['readonly'] ) ) {
					continue;
				}

				if ( ! isset( $request[ $property ] ) ) {
					continue;
				}

				if ( isset( $params['format'] ) && 'date-time' === $params['format'] ) {
					if ( '_gmt' === substr( $property, -4 ) ) {
						$date_data = rest_get_date_with_gmt( $request[ $property ], true );
						if ( ! empty( $date_data ) ) {
							$model->$property = $date_data[1];

							$property_no_gmt = substr( $property, 0, -4 );
							if ( isset( $model->$property_no_gmt ) ) {
								$model->$property_no_gmt = $date_data[0];
							}
						}
					} else {
						$date_data = rest_get_date_with_gmt( $request[ $property ] );
						if ( ! empty( $date_data ) ) {
							$model->$property = $date_data[0];

							$property_gmt = $property . '_gmt';
							if ( isset( $model->$property_gmt ) ) {
								$model->$property_gmt = $date_data[1];
							}
						}
					}
				} else {
					$model->$property = $request[ $property ];
				}
			}

			return $model;
		}

		/**
		 * Prepares a single model output for response.
		 *
		 * @since 1.0.0
		 *
		 * @param Model           $model   Model object.
		 * @param WP_REST_Request $request Request object.
		 * @return WP_REST_Response Response object.
		 */
		public function prepare_item_for_response( $model, $request ) {
			$schema = $this->get_item_schema();

			if ( method_exists( $this, 'get_fields_for_response' ) ) {
				$fields = $this->get_fields_for_response( $request );
			} else {
				$fields = array_keys( $schema['properties'] );
			}

			$data = array();

			foreach ( $fields as $property ) {
				$params = $schema['properties'][ $property ];
				if ( isset( $params['format'] ) && 'date-time' === $params['format'] ) {
					$data[ $property ] = $this->prepare_date_for_response( $model->$property );
				} else {
					$data[ $property ] = $model->$property;
				}
			}

			$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

			$data = $this->filter_response_by_context( $data, $context );

			$response = rest_ensure_response( $data );

			$response->add_links( $this->prepare_links( $model ) );

			return $response;
		}

		/**
		 * Prepares a date for response.
		 *
		 * @since 1.0.0
		 *
		 * @param string $date The datetime string to prepare.
		 * @return string|null ISO8601/RFC3339 formatted datetime.
		 */
		protected function prepare_date_for_response( $date ) {
			if ( '0000-00-00 00:00:00' === $date ) {
				return null;
			}

			return mysql_to_rfc3339( $date );
		}

		/**
		 * Prepares links for the request.
		 *
		 * @since 1.0.0
		 *
		 * @param Model $model Model object.
		 * @return array Links for the given model.
		 */
		protected function prepare_links( $model ) {
			$base = sprintf( '%s/%s', $this->namespace, $this->rest_base );

			$primary_property = $this->manager->get_primary_property();

			$links = array(
				'self'       => array(
					'href' => rest_url( trailingslashit( $base ) . $model->$primary_property ),
				),
				'collection' => array(
					'href' => rest_url( $base ),
				),
			);

			if ( method_exists( $this->manager, 'get_type_property' ) && isset( $this->types_controller ) ) {
				$type_property = $this->manager->get_type_property();

				$type = $model->$type_property;

				if ( ! empty( $type ) ) {
					$links['about'] = array(
						'href' => rest_url( trailingslashit( $base ) . 'types/' . $type ),
					);
				}
			}

			if ( method_exists( $this->manager, 'get_author_property' ) ) {
				$author_property = $this->manager->get_author_property();

				$author_id = $model->$author_property;
				if ( ! empty( $author_id ) ) {
					$links['author'] = array(
						'href'       => rest_url( 'wp/v2/users/' . $author_id ),
						'embeddable' => true,
					);
				}
			}

			return $links;
		}

		/**
		 * Retrieves the model's schema, conforming to JSON Schema.
		 *
		 * @since 1.0.0
		 *
		 * @return array Model schema data.
		 */
		public function get_item_schema() {
			$schema = array(
				'$schema'    => 'http://json-schema.org/schema#',
				'title'      => $this->manager->get_singular_slug(),
				'type'       => 'object',
				'properties' => array(),
			);

			$primary_property = $this->manager->get_primary_property();

			$schema['properties'][ $primary_property ] = array(
				'description' => $this->manager->get_message( 'rest_item_id_description' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			);

			if ( method_exists( $this->manager, 'get_slug_property' ) ) {
				$slug_property = $this->manager->get_slug_property();

				$schema['properties'][ $slug_property ] = array(
					'description' => $this->manager->get_message( 'rest_item_slug_description' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => array( $this, 'sanitize_slug' ),
					),
				);
			}

			if ( method_exists( $this->manager, 'get_title_property' ) ) {
				$title_property = $this->manager->get_title_property();

				$schema['properties'][ $title_property ] = array(
					'description' => $this->manager->get_message( 'rest_item_title_description' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				);
			}

			if ( method_exists( $this->manager, 'get_content_property' ) ) {
				$content_property = $this->manager->get_content_property();

				$schema['properties'][ $content_property ] = array(
					'description' => $this->manager->get_message( 'rest_item_content_description' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				);
			}

			if ( method_exists( $this->manager, 'get_type_property' ) ) {
				$type_property = $this->manager->get_type_property();

				$schema['properties'][ $type_property ] = array(
					'description' => $this->manager->get_message( 'rest_item_type_description' ),
					'type'        => 'string',
					'enum'        => array_keys( $this->manager->types()->query() ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'default'  => $this->manager->types()->get_default(),
						'required' => true,
					),
				);
			}

			if ( method_exists( $this->manager, 'get_status_property' ) ) {
				$status_property = $this->manager->get_status_property();

				$schema['properties'][ $status_property ] = array(
					'description' => $this->manager->get_message( 'rest_item_status_description' ),
					'type'        => 'string',
					'enum'        => array_keys( $this->manager->statuses()->query() ),
					'context'     => array( 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => array( $this, 'sanitize_set_status' ),
						'default'           => $this->manager->statuses()->get_default(),
						'required'          => true,
					),
				);
			}

			if ( method_exists( $this->manager, 'get_author_property' ) ) {
				$author_property = $this->manager->get_author_property();

				$schema['properties'][ $author_property ] = array(
					'description' => $this->manager->get_message( 'rest_item_author_description' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => array( $this, 'sanitize_author' ),
					),
				);
			}

			if ( method_exists( $this->manager, 'get_date_property' ) ) {
				$date_property = $this->manager->get_date_property();

				$schema['properties'][ $date_property ] = array(
					'description' => $this->manager->get_message( 'rest_item_date_description' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit', 'embed' ),
					/* Sanitization is handled directly in the prepare methods. */
				);

				foreach ( $this->manager->get_secondary_date_properties() as $secondary_date_property ) {
					$description = $this->manager->get_message( 'rest_item_date_' . $secondary_date_property . '_description' );
					if ( empty( $description ) ) {
						continue;
					}

					$schema['properties'][ $secondary_date_property ] = array(
						'description' => $description,
						'type'        => 'string',
						'format'      => 'date-time',
						'context'     => array( 'view', 'edit', 'embed' ),
						/* Sanitization is handled directly in the prepare methods. */
					);
				}
			}

			return $schema;
		}

		/**
		 * Retrieves the query params for the models collection.
		 *
		 * @since 1.0.0
		 *
		 * @return array Collection parameters.
		 */
		public function get_collection_params() {
			$query_params = parent::get_collection_params();

			$primary_property = $this->manager->get_primary_property();
			$query_object     = $this->manager->create_query_object();

			$query_params['context']['default'] = 'view';

			$search_fields = $query_object->get_search_fields();
			if ( empty( $search_fields ) ) {
				unset( $query_params['search'] );
			}

			$query_params['include'] = array(
				'description' => $this->manager->get_message( 'rest_collection_include_description' ),
				'type'        => 'array',
				'items'       => array(
					'type' => 'integer',
				),
				'default'     => array(),
			);

			$query_params['exclude'] = array(
				'description' => $this->manager->get_message( 'rest_collection_exclude_description' ),
				'type'        => 'array',
				'items'       => array(
					'type' => 'integer',
				),
				'default'     => array(),
			);

			$query_params['orderby'] = array(
				'description' => $this->manager->get_message( 'rest_collection_orderby_description' ),
				'type'        => 'string',
				'default'     => $primary_property,
				'enum'        => $query_object->get_valid_orderby_fields(),
			);

			$query_params['order'] = array(
				'description' => $this->manager->get_message( 'rest_collection_order_description' ),
				'type'        => 'string',
				'default'     => 'asc',
				'enum'        => array( 'asc', 'desc' ),
			);

			if ( method_exists( $this->manager, 'get_slug_property' ) ) {
				$slug_property = $this->manager->get_slug_property();

				$query_params[ $slug_property ] = array(
					'description'       => $this->manager->get_message( 'rest_collection_slug_description' ),
					'type'              => 'array',
					'items'             => array(
						'type' => 'string',
					),
					'sanitize_callback' => 'wp_parse_slug_list',
				);
			}

			if ( method_exists( $this->manager, 'get_title_property' ) ) {
				$title_property = $this->manager->get_title_property();

				$query_params[ $title_property ] = array(
					'description'       => $this->manager->get_message( 'rest_collection_title_description' ),
					'type'              => 'string',
					'sanitize_callback' => 'strip_tags',
				);
			}

			if ( method_exists( $this->manager, 'get_type_property' ) ) {
				$type_property = $this->manager->get_type_property();

				$query_params[ $type_property ] = array(
					'description'       => $this->manager->get_message( 'rest_collection_type_description' ),
					'type'              => 'array',
					'items'             => array(
						'type' => 'string',
						'enum' => array_keys( $this->manager->types()->query() ),
					),
					'default'           => $this->manager->types()->get_public(),
					'sanitize_callback' => array( $this, 'sanitize_types' ),
				);
			}

			if ( method_exists( $this->manager, 'get_status_property' ) ) {
				$status_property = $this->manager->get_status_property();

				$query_params[ $status_property ] = array(
					'description'       => $this->manager->get_message( 'rest_collection_status_description' ),
					'type'              => 'array',
					'items'             => array(
						'type' => 'string',
						'enum' => array_keys( $this->manager->statuses()->query() ),
					),
					'default'           => $this->manager->statuses()->get_public(),
					'sanitize_callback' => array( $this, 'sanitize_statuses' ),
				);
			}

			if ( method_exists( $this->manager, 'get_author_property' ) ) {
				$author_property = $this->manager->get_author_property();

				$query_params[ $author_property ] = array(
					'description'       => $this->manager->get_message( 'rest_collection_author_description' ),
					'type'              => 'integer',
					'sanitize_callback' => array( $this, 'sanitize_author' ),
				);
			}

			if ( method_exists( $this->manager, 'get_date_property' ) ) {
				$date_property = $this->manager->get_date_property();

				$query_params[ $date_property . '_after' ] = array(
					'description' => $this->manager->get_message( 'rest_collection_date_after_description' ),
					'type'        => 'string',
					'format'      => 'date-time',
				);

				$query_params[ $date_property . '_before' ] = array(
					'description' => $this->manager->get_message( 'rest_collection_date_before_description' ),
					'type'        => 'string',
					'format'      => 'date-time',
				);

				foreach ( $this->manager->get_secondary_date_properties() as $secondary_date_property ) {
					$after_description = $this->manager->get_message( 'rest_collection_date_' . $secondary_date_property . '_after_description' );
					if ( ! empty( $after_description ) ) {
						$query_params[ $secondary_date_property . '_after' ] = array(
							'description' => $after_description,
							'type'        => 'string',
							'format'      => 'date-time',
						);
					}

					$before_description = $this->manager->get_message( 'rest_collection_date_' . $secondary_date_property . '_before_description' );
					if ( ! empty( $before_description ) ) {
						$query_params[ $secondary_date_property . '_before' ] = array(
							'description' => $before_description,
							'type'        => 'string',
							'format'      => 'date-time',
						);
					}
				}
			}

			return $query_params;
		}

		/**
		 * Sanitizes the model author.
		 *
		 * @since 1.0.0
		 *
		 * @param string          $author    The author ID.
		 * @param WP_REST_Request $request   Full details about the request.
		 * @param string          $parameter Additional parameter to pass to validation.
		 * @return string|WP_Error Sanitized author ID, otherwise WP_Error object.
		 */
		public function sanitize_author( $author, $request, $parameter ) {
			$author = absint( $author );

			if ( get_current_user_id() !== $author ) {
				$user = get_userdata( $author );
				if ( ! $user ) {
					return new WP_Error( 'rest_invalid_author', $this->manager->get_message( 'rest_invalid_author' ), array( 'status' => 400 ) );
				}
			}

			return $author;
		}

		/**
		 * Sanitizes the model status to set.
		 *
		 * @since 1.0.0
		 *
		 * @param string          $status    The status to set.
		 * @param WP_REST_Request $request   Full details about the request.
		 * @param string          $parameter Additional parameter to pass to validation.
		 * @return string|WP_Error Sanitized status, otherwise WP_Error object.
		 */
		public function sanitize_set_status( $status, $request, $parameter ) {
			$capabilities = $this->manager->capabilities();

			$public_statuses = $this->manager->statuses()->get_public();

			if ( in_array( $status, $public_statuses, true ) ) {
				$id = isset( $request['id'] ) ? absint( $request['id'] ) : null;

				if ( ! $capabilities || ! $capabilities->user_can_publish( null, $id ) ) {
					return new WP_Error( 'rest_cannot_publish_item', $this->manager->get_message( 'rest_cannot_publish_item' ), rest_authorization_required_code() );
				}
			}

			return $status;
		}

		/**
		 * Sanitizes and validates the list of model statuses.
		 *
		 * @since 1.0.0
		 *
		 * @param string|array    $statuses  One or more model statuses.
		 * @param WP_REST_Request $request   Full details about the request.
		 * @param string          $parameter Additional parameter to pass to validation.
		 * @return array|WP_Error A list of valid statuses, otherwise WP_Error object.
		 */
		public function sanitize_statuses( $statuses, $request, $parameter ) {
			$capabilities = $this->manager->capabilities();

			$public_statuses = $this->manager->statuses()->get_public();

			$statuses = wp_parse_slug_list( $statuses );

			foreach ( $statuses as $status ) {
				if ( in_array( $status, $public_statuses, true ) ) {
					continue;
				}

				if ( ! $capabilities || ! $capabilities->user_can_edit() ) {
					return new WP_Error( 'rest_cannot_view_status', $this->manager->get_message( 'rest_cannot_view_status' ), rest_authorization_required_code() );
				}

				$result = rest_validate_request_arg( $status, $request, $parameter );
				if ( is_wp_error( $result ) ) {
					return $result;
				}
			}

			return $statuses;
		}

		/**
		 * Sanitizes and validates the list of model types.
		 *
		 * @since 1.0.0
		 *
		 * @param string|array    $types     One or more model types.
		 * @param WP_REST_Request $request   Full details about the request.
		 * @param string          $parameter Additional parameter to pass to validation.
		 * @return array|WP_Error A list of valid types, otherwise WP_Error object.
		 */
		public function sanitize_types( $types, $request, $parameter ) {
			$capabilities = $this->manager->capabilities();

			$public_types = $this->manager->types()->get_public();

			$types = wp_parse_slug_list( $types );

			foreach ( $types as $type ) {
				if ( in_array( $type, $public_types, true ) ) {
					continue;
				}

				if ( ! $capabilities || ! $capabilities->user_can_edit() ) {
					return new WP_Error( 'rest_cannot_view_type', $this->manager->get_message( 'rest_cannot_view_type' ), rest_authorization_required_code() );
				}

				$result = rest_validate_request_arg( $type, $request, $parameter );
				if ( is_wp_error( $result ) ) {
					return $result;
				}
			}

			return $types;
		}
	}

endif;
