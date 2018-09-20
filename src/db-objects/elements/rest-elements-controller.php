<?php
/**
 * REST elements controller class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements;

use Leaves_And_Love\Plugin_Lib\DB_Objects\REST_Models_Controller;
use awsmug\Torro_Forms\DB_Objects\REST_Embed_Limits_Trait;
use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\REST_Element_Types_Controller;
use WP_REST_Request;
use WP_Error;

/**
 * Class to access elements via the REST API.
 *
 * @since 1.0.0
 */
class REST_Elements_Controller extends REST_Models_Controller {
	use REST_Embed_Limits_Trait;

	/**
	 * REST element types controller.
	 *
	 * @since 1.0.0
	 * @var REST_Element_Types_Controller
	 */
	protected $types_controller;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Element_Manager $manager The manager instance.
	 */
	public function __construct( $manager ) {
		parent::__construct( $manager );

		$this->namespace .= '/v1';

		$this->types_controller = new REST_Element_Types_Controller( $this->manager );
	}

	/**
	 * Retrieves the model's schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Model schema data.
	 */
	public function get_item_schema() {
		$schema = parent::get_item_schema();

		$schema['properties']['container_id'] = array(
			'description' => __( 'ID of the container this element belongs to.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
			'context'     => array( 'view', 'edit', 'embed' ),
			'arg_options' => array(
				'minimum' => 1,
			),
		);

		$schema['properties']['sort'] = array(
			'description' => __( 'Numeric value to determine the order within a list of multiple elements.', 'torro-forms' ),
			'type'        => 'integer',
			'context'     => array( 'view', 'edit', 'embed' ),
		);

		$schema['properties']['type'] = array(
			'description' => __( 'Type of the element.', 'torro-forms' ),
			'type'        => 'string',
			'enum'        => array_keys( $this->manager->types()->get_all() ),
			'context'     => array( 'view', 'edit', 'embed' ),
		);

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

		unset( $query_params['per_page']['maximum'] );
		$query_params['orderby']['default'] = 'sort';

		$query_params['form_id'] = array(
			'description' => __( 'Limit result set to elements associated with a specific form ID.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
		);

		$query_params['container_id'] = array(
			'description' => __( 'Limit result set to elements associated with a specific container ID.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
		);

		$query_params['type'] = array(
			'description'       => __( 'Limit result set to elements with one or more specific statuses.', 'torro-forms' ),
			'type'              => 'array',
			'items'             => array(
				'type' => 'string',
				'enum' => array_keys( $this->manager->types()->get_all() ),
			),
			'sanitize_callback' => array( $this, 'sanitize_type_param' ),
		);

		return $query_params;
	}

	/**
	 * Prepares links for the request.
	 *
	 * @since 1.0.0
	 *
	 * @param Element $element Element object.
	 * @return array Links for the given element.
	 */
	protected function prepare_links( $element ) {
		$links = parent::prepare_links( $element );

		if ( ! empty( $element->type ) ) {
			$links['about'] = array(
				'href' => rest_url( trailingslashit( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ) . 'types/' . $element->type ),
			);
		}

		if ( ! empty( $element->container_id ) ) {
			$links['parent_container'] = array(
				'href' => rest_url( trailingslashit( sprintf( '%s/%s', $this->namespace, 'containers' ) ) . $element->container_id ),
			);
		}

		$primary_property = $this->manager->get_primary_property();

		$links['element_choices'] = array(
			'href'       => add_query_arg(
				array(
					'element_id' => $element->$primary_property,
					'per_page'   => $this->get_embed_limit( 'element_choices' ),
				),
				rest_url( sprintf( '%s/%s', $this->namespace, 'element_choices' ) )
			),
			'embeddable' => true,
		);

		$links['element_settings'] = array(
			'href'       => add_query_arg(
				array(
					'element_id' => $element->$primary_property,
					'per_page'   => $this->get_embed_limit( 'element_settings' ),
				),
				rest_url( sprintf( '%s/%s', $this->namespace, 'element_settings' ) )
			),
			'embeddable' => true,
		);

		return $links;
	}

	/**
	 * Sanitizes and validates the list of element types.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array    $types     One or more element types.
	 * @param WP_REST_Request $request   Full details about the request.
	 * @param string          $parameter Additional parameter to pass to validation.
	 * @return array|WP_Error A list of valid types, otherwise WP_Error object.
	 */
	public function sanitize_type_param( $types, $request, $parameter ) {
		$types = wp_parse_slug_list( $types );

		$all_types = $this->manager->types()->get_all();

		foreach ( $types as $type ) {
			$result = rest_validate_request_arg( $type, $request, $parameter );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		return $types;
	}
}
