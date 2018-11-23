<?php
/**
 * REST element types controller class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements\Element_Types;

use awsmug\Torro_Forms\DB_Objects\Elements\Element_Manager;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_Error;

/**
 * Class to access element types via the REST API.
 *
 * @since 1.0.0
 */
class REST_Element_Types_Controller extends WP_REST_Controller {

	/**
	 * The manager instance.
	 *
	 * @since 1.0.0
	 * @var Element_Manager
	 */
	protected $manager;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Element_Manager $manager The manager instance.
	 */
	public function __construct( $manager ) {
		$this->manager = $manager;

		$prefix = $this->manager->get_prefix();
		if ( '_' === substr( $prefix, -1 ) ) {
			$prefix = substr( $prefix, 0, -1 );
		}

		$this->namespace = $prefix . '/v1';
		$this->rest_base = $this->manager->get_plural_slug() . '/types';
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
						'description' => __( 'An alphanumeric identifier for the element type.', 'torro-forms' ),
						'type'        => 'string',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'context' => $this->get_context_param(
							array(
								'default' => 'view',
							)
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Checks whether a given request has permission to read types.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|true True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		$capabilities = $this->manager->capabilities();

		if ( 'edit' === $request['context'] && ( ! $capabilities || ! $capabilities->user_can_edit() ) ) {
			return new WP_Error( 'rest_cannot_edit_types', __( 'Sorry, you are not allowed to edit elements and their types.', 'torro-forms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		if ( ! $this->manager->is_public() && ( ! $capabilities || ! $capabilities->user_can_read() ) ) {
			return new WP_Error( 'rest_cannot_read_types', __( 'Sorry, you are not allowed to view elements and their types.', 'torro-forms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Retrieves a collection of element types.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$data = array();

		foreach ( $this->manager->types()->get_all() as $obj ) {
			$type = $this->prepare_item_for_response( $obj, $request );

			$data[] = $this->prepare_response_for_collection( $type );
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Checks whether a given request has permission to read a type.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|true True if the request has read access, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		$capabilities = $this->manager->capabilities();

		if ( 'edit' === $request['context'] && ( ! $capabilities || ! $capabilities->user_can_edit() ) ) {
			return new WP_Error( 'rest_cannot_edit_type', __( 'Sorry, you are not allowed to edit elements of this type.', 'torro-forms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		$obj = $this->manager->types()->get( $request['slug'] );
		if ( is_wp_error( $obj ) ) {
			return new WP_Error( 'rest_invalid_type_slug', __( 'Invalid element type slug.', 'torro-forms' ), array( 'status' => 404 ) );
		}

		if ( ! $this->manager->is_public() && ( ! $capabilities || ! $capabilities->user_can_read() ) ) {
			return new WP_Error( 'rest_cannot_read_type', __( 'Sorry, you are not allowed to view elements of this type.', 'torro-forms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Retrieves a specific element type.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$obj = $this->manager->types()->get( $request['slug'] );

		$data = $this->prepare_item_for_response( $obj, $request );

		return rest_ensure_response( $data );
	}

	/**
	 * Prepares an element type for response.
	 *
	 * @since 1.0.0
	 *
	 * @param Element_Type    $element_type Element type object.
	 * @param WP_REST_Request $request      Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $element_type, $request ) {
		$schema = $this->get_item_schema();

		$data = array();

		foreach ( $schema['properties'] as $property => $params ) {
			switch ( $property ) {
				case 'slug':
				case 'title':
				case 'description':
				case 'icon_css_class':
				case 'icon_svg_id':
				case 'icon_url':
					$data[ $property ] = call_user_func( array( $element_type, 'get_' . $property ) );
					break;
				case 'non_input':
					$data[ $property ] = is_a( $element_type, Non_Input_Element_Type_Interface::class );
					break;
				case 'evaluable':
					$data[ $property ] = is_a( $element_type, Choice_Element_Type_Interface::class );
					break;
				case 'multifield':
					$data[ $property ] = is_a( $element_type, Multi_Field_Element_Type_Interface::class );
					break;
				case 'sections':
					$sections          = $element_type->get_settings_sections();
					$data[ $property ] = array();
					foreach ( $sections as $slug => $section ) {
						$data[ $property ][] = array_merge(
							array(
								'slug' => $slug,
							),
							$section
						);
					}
					break;
				case 'fields':
					$fields            = $element_type->get_settings_fields();
					$data[ $property ] = array();
					foreach ( $fields as $slug => $field ) {
						$data[ $property ][] = array_merge(
							array(
								'slug' => $slug,
							),
							$field
						);
					}
					break;
				default:
					$data[ $property ] = null;
			}
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

		$data = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $element_type ) );

		return $response;
	}

	/**
	 * Prepares links for the request.
	 *
	 * @since 1.0.0
	 *
	 * @param Element_Type $element_type Element type object.
	 * @return array Links for the given element type.
	 */
	protected function prepare_links( $element_type ) {
		$base = sprintf( '%s/%s', $this->namespace, $this->rest_base );

		$links = array(
			'self'       => array(
				'href' => rest_url( trailingslashit( $base ) . $element_type->get_slug() ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
			'elements'   => array(
				'href' => rest_url( substr( $base, 0, -6 ) . '?type=' . $element_type->get_slug() ),
			),
		);

		return $links;
	}

	/**
	 * Retrieves the element type's schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Element type schema data.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => $this->manager->get_singular_slug() . '_type',
			'type'       => 'object',
			'properties' => array(
				'slug'           => array(
					'description' => __( 'Slug for the element type.', 'torro-forms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'title'          => array(
					'description' => __( 'Title for the element type.', 'torro-forms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'description'    => array(
					'description' => __( 'Description for the element type.', 'torro-forms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'icon_css_class' => array(
					'description' => __( 'Icon CSS class for the element type.', 'torro-forms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'icon_svg_id'    => array(
					'description' => __( 'Icon SVG ID for the element type.', 'torro-forms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'icon_url'       => array(
					'description' => __( 'Icon URL for the element type.', 'torro-forms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'non_input'      => array(
					'description' => __( 'Whether the element type does not expect any input.', 'torro-forms' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'evaluable'      => array(
					'description' => __( 'Whether the element type is evaluable in stats.', 'torro-forms' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'multifield'     => array(
					'description' => __( 'Whether the element type contains multiple fields.', 'torro-forms' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'sections'       => array(
					'description' => __( 'Settings sections the element type uses.', 'torro-forms' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'slug'  => array(
								'description' => __( 'Slug for the element type settings section.', 'torro-forms' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),
								'readonly'    => true,
							),
							'title' => array(
								'description' => __( 'Title for the element type settings section.', 'torro-forms' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),
								'readonly'    => true,
							),
						),
					),
				),
				'fields'         => array(
					'description' => __( 'Settings fields the element type uses.', 'torro-forms' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'slug'        => array(
								'description' => __( 'Slug for the element type settings field.', 'torro-forms' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),
								'readonly'    => true,
							),
							'section'     => array(
								'description' => __( 'Slug of the section for the element type settings field.', 'torro-forms' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),
								'readonly'    => true,
							),
							'type'        => array(
								'description' => __( 'Identifier for the field API type the element type settings field uses.', 'torro-forms' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),
								'readonly'    => true,
							),
							'label'       => array(
								'description' => __( 'Label for the element type settings field.', 'torro-forms' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),
								'readonly'    => true,
							),
							'description' => array(
								'description' => __( 'Description for the element type settings field.', 'torro-forms' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),
								'readonly'    => true,
							),
							'is_label'    => array(
								'description' => __( 'Whether the element type settings field determines the element label.', 'torro-forms' ),
								'type'        => 'boolean',
								'context'     => array( 'view', 'edit', 'embed' ),
								'readonly'    => true,
							),
							'is_choices'  => array(
								'description' => __( 'Whether the element type settings field determines element choices.', 'torro-forms' ),
								'type'        => 'boolean',
								'context'     => array( 'view', 'edit', 'embed' ),
								'readonly'    => true,
							),
						),
					),
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
			'context' => $this->get_context_param(
				array(
					'default' => 'view',
				)
			),
		);
	}
}
