<?php
/**
 * REST element settings controller class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Element_Settings;

use Leaves_And_Love\Plugin_Lib\DB_Objects\REST_Models_Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Class to access element settings via the REST API.
 *
 * @since 1.0.0
 */
class REST_Element_Settings_Controller extends REST_Models_Controller {

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

		$schema['properties']['element_id'] = array(
			'description' => __( 'ID of the element this element setting belongs to.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
			'context'     => array( 'view', 'edit', 'embed' ),
		);

		$schema['properties']['name'] = array(
			'description' => __( 'Alphanumeric identifier for the element setting.', 'torro-forms' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit', 'embed' ),
		);

		$schema['properties']['value'] = array(
			'description' => __( 'Value of the element setting.', 'torro-forms' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit', 'embed' ),
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
			'description' => __( 'Limit result set to element settings associated with a specific form ID.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
		);

		$query_params['container_id'] = array(
			'description' => __( 'Limit result set to element settings associated with a specific container ID.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
		);

		$query_params['element_id'] = array(
			'description' => __( 'Limit result set to element settings associated with a specific element ID.', 'torro-forms' ),
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

		if ( ! empty( $model->element_id ) ) {
			$links['parent_element'] = array(
				'href' => rest_url( trailingslashit( sprintf( '%s/%s', $this->namespace, 'elements' ) ) . $model->element_id ),
			);
		}

		return $links;
	}
}
