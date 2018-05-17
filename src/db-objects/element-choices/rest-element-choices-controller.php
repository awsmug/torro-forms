<?php
/**
 * REST element choices controller class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Element_Choices;

use Leaves_And_Love\Plugin_Lib\DB_Objects\REST_Models_Controller;

/**
 * Class to access element choices via the REST API.
 *
 * @since 1.0.0
 */
class REST_Element_Choices_Controller extends REST_Models_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Leaves_And_Love\Plugin_Lib\DB_Objects\Manager $manager The manager instance.
	 */
	public function __construct( $manager ) {
		parent::__construct( $manager );

		$this->namespace .= '/v1';
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

		$schema['properties']['element_id'] = array(
			'description' => __( 'ID of the element this element choice belongs to.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
			'context'     => array( 'view', 'edit', 'embed' ),
			'arg_options' => array(
				'minimum' => 1,
			),
		);

		$schema['properties']['sort'] = array(
			'description' => __( 'Numeric value to determine the order within a list of multiple element choices.', 'torro-forms' ),
			'type'        => 'integer',
			'context'     => array( 'view', 'edit', 'embed' ),
		);

		$schema['properties']['field'] = array(
			'description' => __( 'Element type field identifier the element choice is associated with, if not the main field.', 'torro-forms' ),
			'type'        => 'string',
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
			'description' => __( 'Limit result set to element choices associated with a specific form ID.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
		);

		$query_params['container_id'] = array(
			'description' => __( 'Limit result set to element choices associated with a specific container ID.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
		);

		$query_params['element_id'] = array(
			'description' => __( 'Limit result set to element choices associated with a specific element ID.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
		);

		return $query_params;
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
		$response = parent::prepare_item_for_response( $model, $request );

		if ( isset( $response->data['field'] ) && '' === $response->data['field'] ) {
			$response->data['field'] = '_main';
		}

		return $response;
	}

	/**
	 * Prepares links for the request.
	 *
	 * @since 1.0.0
	 *
	 * @param Element_Choice $element_choice Element choice object.
	 * @return array Links for the given element choice.
	 */
	protected function prepare_links( $element_choice ) {
		$links = parent::prepare_links( $element_choice );

		if ( ! empty( $element_choice->element_id ) ) {
			$links['parent_element'] = array(
				'href' => rest_url( trailingslashit( sprintf( '%s/%s', $this->namespace, 'elements' ) ) . $element_choice->element_id ),
			);
		}

		return $links;
	}
}
