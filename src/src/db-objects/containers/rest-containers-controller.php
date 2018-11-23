<?php
/**
 * REST containers controller class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Containers;

use Leaves_And_Love\Plugin_Lib\DB_Objects\REST_Models_Controller;
use awsmug\Torro_Forms\DB_Objects\REST_Embed_Limits_Trait;

/**
 * Class to access containers via the REST API.
 *
 * @since 1.0.0
 */
class REST_Containers_Controller extends REST_Models_Controller {
	use REST_Embed_Limits_Trait;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Container_Manager $manager The manager instance.
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
			'description' => __( 'ID of the form this container belongs to.', 'torro-forms' ),
			'type'        => 'integer',
			'minimum'     => 1,
			'context'     => array( 'view', 'edit', 'embed' ),
			'arg_options' => array(
				'minimum' => 1,
			),
		);

		$schema['properties']['sort'] = array(
			'description' => __( 'Numeric value to determine the order within a list of multiple containers.', 'torro-forms' ),
			'type'        => 'integer',
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
			'description' => __( 'Limit result set to containers associated with a specific form ID.', 'torro-forms' ),
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
	 * @param Container $container Container object.
	 * @return array Links for the given container.
	 */
	protected function prepare_links( $container ) {
		$links = parent::prepare_links( $container );

		if ( ! empty( $container->form_id ) ) {
			$links['parent_form'] = array(
				'href' => rest_url( trailingslashit( sprintf( '%s/%s', $this->namespace, 'forms' ) ) . $container->form_id ),
			);
		}

		$primary_property = $this->manager->get_primary_property();

		$links['elements'] = array(
			'href'       => add_query_arg(
				array(
					'container_id' => $container->$primary_property,
					'per_page'     => $this->get_embed_limit( 'elements' ),
				),
				rest_url( sprintf( '%s/%s', $this->namespace, 'elements' ) )
			),
			'embeddable' => true,
		);

		$links['element_choices'] = array(
			'href'       => add_query_arg(
				array(
					'container_id' => $container->$primary_property,
					'per_page'     => $this->get_embed_limit( 'element_choices' ),
				),
				rest_url( sprintf( '%s/%s', $this->namespace, 'element_choices' ) )
			),
			'embeddable' => true,
		);

		$links['element_settings'] = array(
			'href'       => add_query_arg(
				array(
					'container_id' => $container->$primary_property,
					'per_page'     => $this->get_embed_limit( 'element_settings' ),
				),
				rest_url( sprintf( '%s/%s', $this->namespace, 'element_settings' ) )
			),
			'embeddable' => true,
		);

		return $links;
	}
}
