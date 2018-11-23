<?php
/**
 * REST submission values controller class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Submission_Values;

use Leaves_And_Love\Plugin_Lib\DB_Objects\REST_Models_Controller;

/**
 * Class to access submission values via the REST API.
 *
 * @since 1.0.0
 */
class REST_Submission_Values_Controller extends REST_Models_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Submission_Value_Manager $manager The manager instance.
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

		$schema['properties']['submission_id'] = array(
			'description' => __( 'ID of the submission this submission value belongs to.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
			'context'     => array( 'view', 'edit', 'embed' ),
			'arg_options' => array(
				'minimum' => 1,
			),
		);

		$schema['properties']['element_id'] = array(
			'description' => __( 'ID of the element this submission value refers to.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
			'context'     => array( 'view', 'edit', 'embed' ),
			'arg_options' => array(
				'minimum' => 1,
			),
		);

		$schema['properties']['field'] = array(
			'description' => __( 'Element type field identifier the submission value is associated with, if not the main element field.', 'torro-forms' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit', 'embed' ),
		);

		$schema['properties']['value'] = array(
			'description' => __( 'The submission value.', 'torro-forms' ),
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

		$query_params['form_id'] = array(
			'description' => __( 'Limit result set to submission values associated with a specific form ID.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
		);

		$query_params['submission_id'] = array(
			'description' => __( 'Limit result set to submission values associated with a specific submission ID.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
		);

		$query_params['element_id'] = array(
			'description' => __( 'Limit result set to submission values referring to a specific element ID.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
		);

		return $query_params;
	}

	/**
	 * Prepares links for the request.
	 *
	 * @since 1.0.0
	 *
	 * @param Submission_Value $submission_value Submission value object.
	 * @return array Links for the given submission value.
	 */
	protected function prepare_links( $submission_value ) {
		$links = parent::prepare_links( $submission_value );

		if ( ! empty( $submission_value->submission_id ) ) {
			$links['submission'] = array(
				'href' => rest_url( trailingslashit( sprintf( '%s/%s', $this->namespace, 'submissions' ) ) . $submission_value->submission_id ),
			);
		}

		return $links;
	}
}
