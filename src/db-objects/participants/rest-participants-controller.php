<?php
/**
 * REST participants controller class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Participants;

use Leaves_And_Love\Plugin_Lib\DB_Objects\REST_Models_Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Class to access participants via the REST API.
 *
 * @since 1.0.0
 */
class REST_Participants_Controller extends REST_Models_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
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
	 * @access public
	 *
	 * @return array Model schema data.
	 */
	public function get_item_schema() {
		$schema = parent::get_item_schema();

		$schema['properties']['form_id'] = array(
			'description' => __( 'ID of the form this participant refers to.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
			'context'     => array( 'view', 'edit', 'embed' ),
			'arg_options' => array(
				'minimum' => 1,
			),
		);

		$schema['properties']['user_id'] = array(
			'description' => __( 'ID of the user this participant refers to, if any.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 0,
			'context'     => array( 'view', 'edit', 'embed' ),
			'arg_options' => array(
				'minimum' => 0,
			),
		);

		return $schema;
	}

	/**
	 * Retrieves the query params for the models collection.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		$query_params = parent::get_collection_params();

		$query_params['per_page']['maximum'] = 500;

		$query_params['form_id'] = array(
			'description' => __( 'Limit result set to participants associated with a specific form ID.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
		);

		$query_params['user_id'] = array(
			'description' => __( 'Limit result set to participants associated with a specific user ID.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
		);

		return $query_params;
	}

	/**
	 * Prepares links for the request.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Leaves_And_Love\Plugin_Lib\DB_Objects\Model $model Model object.
	 * @return array Links for the given model.
	 */
	protected function prepare_links( $model ) {
		$links = parent::prepare_links( $model );

		if ( ! empty( $model->form_id ) ) {
			$links['form'] = array(
				'href' => rest_url( trailingslashit( sprintf( '%s/%s', $this->namespace, 'forms' ) ) . $model->form_id ),
			);
		}

		if ( ! empty( $model->user_id ) ) {
			$links['user'] = array(
				'href'       => rest_url( 'wp/v2/users/' . $model->user_id ),
			);
		}

		return $links;
	}
}
