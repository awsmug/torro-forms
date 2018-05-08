<?php
/**
 * REST form categories controller class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Form_Categories;

use Leaves_And_Love\Plugin_Lib\DB_Objects\REST_Models_Controller;

/**
 * Class to access form categories via the REST API.
 *
 * @since 1.0.0
 */
class REST_Form_Categories_Controller extends REST_Models_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Form_Category_Manager $manager The manager instance.
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

		$schema['properties']['description'] = array(
			'description' => __( 'Description for the form category.', 'torro-forms' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit', 'embed' ),
		);

		$schema['properties']['parent'] = array(
			'description' => __( 'ID of the parent of the form category, if any.', 'torro-forms' ),
			'type'        => 'integer',
			'context'     => array( 'view', 'edit', 'embed' ),
			'arg_options' => array(
				'minimum' => 0,
				'default' => 0,
			),
		);

		$schema['properties']['count'] = array(
			'description' => __( 'Number of forms associated with the form category.', 'torro-forms' ),
			'type'        => 'integer',
			'context'     => array( 'view', 'edit', 'embed' ),
			'readonly'    => true,
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
		$query_params['orderby']['default'] = $this->manager->get_slug_property();

		return $query_params;
	}
}
