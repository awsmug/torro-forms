<?php
/**
 * REST submissions controller class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Submissions;

use Leaves_And_Love\Plugin_Lib\DB_Objects\REST_Models_Controller;

/**
 * Class to access submissions via the REST API.
 *
 * @since 1.0.0
 */
class REST_Submissions_Controller extends REST_Models_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Submission_Manager $manager The manager instance.
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

		$schema['properties']['form_id'] = array(
			'description' => __( 'ID of the form this submission belongs to.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
			'context'     => array( 'view', 'edit', 'embed' ),
			'arg_options' => array(
				'minimum' => 1,
			),
		);

		$schema['properties']['user_id'] = array(
			'description' => __( 'ID of the user this submission belongs to, if any.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 0,
			'context'     => array( 'view', 'edit', 'embed' ),
			'arg_options' => array(
				'minimum' => 0,
			),
		);

		$schema['properties']['timestamp'] = array(
			'description' => __( 'UNIX timestamp for when the submission was created.', 'torro-forms' ),
			'type'        => 'integer',
			'context'     => array( 'view', 'edit', 'embed' ),
		);

		$schema['properties']['remote_addr'] = array(
			'description' => __( 'IP address of who created the submission.', 'torro-forms' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit', 'embed' ),
		);

		$schema['properties']['user_key'] = array(
			'description' => __( 'Key of who created the submission.', 'torro-forms' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit', 'embed' ),
		);

		$schema['properties']['status'] = array(
			'description' => __( 'Status of the submission.', 'torro-forms' ),
			'type'        => 'string',
			'enum'        => array( 'completed', 'progressing' ),
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
		$query_params['orderby']['default'] = 'timestamp';
		$query_params['order']['default']   = 'desc';

		$query_params['form_id'] = array(
			'description' => __( 'Limit result set to submissions associated with a specific form ID.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
		);

		$query_params['user_id'] = array(
			'description' => __( 'Limit result set to submissions associated with a specific user ID.', 'torro-forms' ),
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
	 * @param Submission $submission Submission object.
	 * @return array Links for the given submission.
	 */
	protected function prepare_links( $submission ) {
		$links = parent::prepare_links( $submission );

		if ( ! empty( $submission->user_id ) ) {
			$links['author'] = array(
				'href'       => rest_url( 'wp/v2/users/' . $submission->user_id ),
				'embeddable' => true,
			);
		}

		if ( ! empty( $submission->form_id ) ) {
			$links['form'] = array(
				'href' => rest_url( trailingslashit( sprintf( '%s/%s', $this->namespace, 'forms' ) ) . $submission->form_id ),
			);
		}

		$primary_property = $this->manager->get_primary_property();

		$links['submission_values'] = array(
			'href'       => add_query_arg(
				array(
					'submission_id' => $submission->$primary_property,
					'per_page'      => 50,
				),
				rest_url( sprintf( '%s/%s', $this->namespace, 'submission_values' ) )
			),
			'embeddable' => true,
		);

		return $links;
	}
}
