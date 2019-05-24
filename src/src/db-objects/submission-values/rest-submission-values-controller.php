<?php
/**
 * REST submission values controller class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Submission_Values;

use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use Leaves_And_Love\Plugin_Lib\DB_Objects\REST_Models_Controller;
use WP_REST_Server;

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
			'arg_options' => array(
				'validate_callback' => array( $this, 'validate_value' ),
			),
		);

		return $schema;
	}

	/**
	 * Validating value with form element settings.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed            $value   Value to validate.
	 * @param \WP_REST_Request $request WP Rest request object.
	 * @param string           $key     Name of the key.
	 *
	 * @return mixed|\WP_Error Validated value or WP Error on failure.
	 */
	public function validate_value( $value, $request, $key ) {
		$submission_id = $request->get_param( 'submission_id' );
		$element_id    = $request->get_param( 'element_id' );

		$submission = torro()->submissions()->get( $submission_id );

		$element      = torro()->elements()->get( $element_id );
		$element_type = $element->get_element_type();

		$value = $element_type->validate_field( $request->get_param( 'value' ), $element, $submission );

		return $value;
	}

	/**
	 * Checks if a given request has access to create a model.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true True if the request has access to create models, WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) {
		$form_id = $request->get_param( 'form_id' );
		$form    = torro()->forms()->get( $form_id );

		/**
		 * Filters if submission can be created.
		 *
		 * @since 1.1.0
		 *
		 * @param bool             $can_create_submission True if item can be filtered, false if not.
		 * @param Form             $form                  Form object.
		 * @param \WP_REST_Request $request               Full details about the request.
		 */
		$can_create_submission = apply_filters( $this->manager->get_prefix() . 'rest_api_can_create_submission_value', parent::create_item_permissions_check( $request ), $form, $request );
		return $can_create_submission;
	}

	/**
	 * Checks if a given request has access to update a model.
	 *
	 * @since 1.1.0
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return true True if the request has access to update models, WP_Error object otherwise.
	 */
	public function update_item_permissions_check( $request ) {
		$form_id = $request->get_param( 'form_id' );
		$form    = torro()->forms()->get( $form_id );

		/**
		 * Filters if submission can be updated.
		 *
		 * @since 1.1.0
		 *
		 * @param bool             $can_update_submission True if item can be filtered, false if not.
		 * @param Form             $form                  Form object.
		 * @param \WP_REST_Request $request               Full details about the request.
		 */
		$can_update_submission = apply_filters( $this->manager->get_prefix() . 'rest_api_can_update_submission_value', parent::update_item_permissions_check( $request ), $form, $request );
		return $can_update_submission;
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
