<?php
/**
 * REST forms controller class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Forms;

use Leaves_And_Love\Plugin_Lib\DB_Objects\REST_Models_Controller;
use awsmug\Torro_Forms\DB_Objects\REST_Embed_Limits_Trait;
use WP_REST_Request;
use WP_Error;

/**
 * Class to access forms via the REST API.
 *
 * @since 1.0.0
 */
class REST_Forms_Controller extends REST_Models_Controller {
	use REST_Embed_Limits_Trait;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Form_Manager $manager The manager instance.
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

		$schema['properties']['status'] = array(
			'description' => __( 'Status of the form.', 'torro-forms' ),
			'type'        => 'string',
			'enum'        => array_keys(
				get_post_stati(
					array(
						'internal' => false,
					)
				)
			),
			'context'     => array( 'edit' ),
		);

		$schema['properties']['timestamp'] = array(
			'description' => __( 'UNIX timestamp for when the form was created.', 'torro-forms' ),
			'type'        => 'integer',
			'context'     => array( 'view', 'edit', 'embed' ),
		);

		$schema['properties']['timestamp_modified'] = array(
			'description' => __( 'UNIX timestamp for when the form was last modified.', 'torro-forms' ),
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
		$query_params['orderby']['default'] = 'timestamp';
		$query_params['order']['default']   = 'desc';

		$query_params['status'] = array(
			'description'       => __( 'Limit result set to forms with one or more specific statuses.', 'torro-forms' ),
			'type'              => 'array',
			'items'             => array(
				'type' => 'string',
				'enum' => array_keys(
					get_post_stati(
						array(
							'internal' => false,
						)
					)
				),
			),
			'default'           => 'publish',
			'sanitize_callback' => array( $this, 'sanitize_status_param' ),
		);

		return $query_params;
	}

	/**
	 * Prepares links for the request.
	 *
	 * @since 1.0.0
	 *
	 * @param Form $form Form object.
	 * @return array Links for the given form.
	 */
	protected function prepare_links( $form ) {
		$links = parent::prepare_links( $form );

		$primary_property = $this->manager->get_primary_property();

		$links['containers'] = array(
			'href'       => add_query_arg(
				array(
					'form_id'  => $form->$primary_property,
					'per_page' => $this->get_embed_limit( 'containers' ),
				),
				rest_url( sprintf( '%s/%s', $this->namespace, 'containers' ) )
			),
			'embeddable' => true,
		);

		$links['elements'] = array(
			'href'       => add_query_arg(
				array(
					'form_id'  => $form->$primary_property,
					'per_page' => $this->get_embed_limit( 'elements' ),
				),
				rest_url( sprintf( '%s/%s', $this->namespace, 'elements' ) )
			),
			'embeddable' => true,
		);

		$links['element_choices'] = array(
			'href'       => add_query_arg(
				array(
					'form_id'  => $form->$primary_property,
					'per_page' => $this->get_embed_limit( 'element_choices' ),
				),
				rest_url( sprintf( '%s/%s', $this->namespace, 'element_choices' ) )
			),
			'embeddable' => true,
		);

		$links['element_settings'] = array(
			'href'       => add_query_arg(
				array(
					'form_id'  => $form->$primary_property,
					'per_page' => $this->get_embed_limit( 'element_settings' ),
				),
				rest_url( sprintf( '%s/%s', $this->namespace, 'element_settings' ) )
			),
			'embeddable' => true,
		);

		$links['submissions'] = array(
			'href' => add_query_arg( 'form_id', $form->$primary_property, rest_url( sprintf( '%s/%s', $this->namespace, 'submissions' ) ) ),
		);

		$links['submission_values'] = array(
			'href' => add_query_arg( 'form_id', $form->$primary_property, rest_url( sprintf( '%s/%s', $this->namespace, 'submission_values' ) ) ),
		);

		return $links;
	}

	/**
	 * Sanitizes and validates the list of statuses.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array    $statuses  One or more statuses.
	 * @param WP_REST_Request $request   Full details about the request.
	 * @param string          $parameter Additional parameter to pass to validation.
	 * @return array|WP_Error A list of valid statuses, otherwise WP_Error object.
	 */
	public function sanitize_status_param( $statuses, $request, $parameter ) {
		$statuses = wp_parse_slug_list( $statuses );

		$attributes     = $request->get_attributes();
		$default_status = $attributes['args']['status']['default'];

		foreach ( $statuses as $status ) {
			if ( $status === $default_status ) {
				continue;
			}

			$post_type_obj = get_post_type_object( $this->manager->get_prefix() . 'form' );

			if ( current_user_can( $post_type_obj->cap->edit_posts ) ) {
				$result = rest_validate_request_arg( $status, $request, $parameter );
				if ( is_wp_error( $result ) ) {
					return $result;
				}
			} else {
				return new WP_Error( 'rest_forbidden_status', __( 'Status is forbidden.', 'torro-forms' ), array( 'status' => rest_authorization_required_code() ) );
			}
		}

		return $statuses;
	}
}
