<?php
/**
 * Frontend posting action class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Actions;

use awsmug\Torro_Forms\Modules\Actions\API_Action\API_Action;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use awsmug\Torro_Forms\Error;

/**
 * Class for an action that creates WordPress posts from the frontend via the REST API.
 *
 * @since 1.1.0
 */
class Frontend_Posting extends API_Action {

	/**
	 * Slug for the API API structure.
	 *
	 * @since 1.1.0
	 */
	const STRUCTURE_SLUG = 'local-rest-api';

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.1.0
	 */
	protected function bootstrap() {
		$this->slug  = 'frontend_posting';
		$this->title = __( 'Frontend Posting', 'torro-forms-plugin-boilerplate' );

		$this->register_site_apiapi_structure();
	}

	/**
	 * Processes the response after it has been received.
	 *
	 * @since 1.1.0
	 *
	 * @param Route_Response $response   Response object.
	 * @param Connection     $connection API connection instance with structure and authentication details.
	 * @param string         $route_slug API-API route identifier.
	 * @param Submission     $submission Submission to handle by the action.
	 * @param Form           $form       Form the submission applies to.
	 * @return Route_Response|WP_Error Processed response object, or error object.
	 */
	protected function process_response( $response, $connection, $route_slug, $submission, $form ) {
		// Detect WordPress REST API errors in the response and act accordingly.
		if ( $response->get_response_code() >= 400 ) {
			$params = $response->get_params();
			if ( isset( $params['code'] ) && isset( $params['message'] ) ) {
				return new Error( $this->get_prefix() . '_apiapi_' . $connection->get_structure() . '_' . $params['code'], $params['message'] );
			}
		}

		return $response;
	}

	/**
	 * Gets the available API structures and their routes.
	 *
	 * @since 1.1.0
	 *
	 * @return array Associative array of $structure_slug => $data pairs. $data must be an associative array with keys
	 *               'title', 'authentication_data' and 'routes'. 'authentication_data' must be an associative array of
	 *               $field_slug => $field_data pairs where details are specified for the respective authentication field.
	 *               Possible keys are 'value', and 'default'. 'routes' must be an associative array of
	 *               $route_slug => $route_data pairs. $route_data must be an associative array with keys 'title' and 'fields'.
	 *               'fields' must be an associative array of $field_slug => $field_data pairs where details are specified for
	 *               each route field that requires special handling. Possible keys are 'value', and 'default'.
	 */
	protected function get_available_structures_and_routes() {
		$routes = array();

		$post_types = get_post_types( array( 'show_in_rest' => true ), 'objects' );
		foreach ( $post_types as $post_type ) {
			if ( ! empty( $post_type->rest_controller_class ) && 'WP_REST_Posts_Controller' !== $post_type->rest_controller_class ) {
				continue;
			}

			$rest_base = ! empty( $post_type->rest_base ) ? $post_type->rest_base : $post_type->name;

			$routes[ 'POST:/wp/v2/' . $rest_base ] = array(
				'title' => $post_type->labels->add_new_item,
			);
		}

		/*
		Normally, the user would need to provide a token, but since we are on the same site as the API, we can automatically
		generate that value on the fly.
		 */
		$authentication_data = array(
			'token' => array(
				'value' => function() {
					return wp_create_nonce( 'wp_rest' );
				},
			),
		);

		return array(
			self::STRUCTURE_SLUG => array(
				'title'               => __( 'Local REST API', 'torro-forms-plugin-boilerplate' ),
				'authentication_data' => $authentication_data,
				'routes'              => $routes,
			),
		);
	}

	/**
	 * Registers the REST API structure for this site.
	 *
	 * It needs to be manually registered because the API-API structure for the WordPress REST API
	 * only registers for wordpress.org and wordpress.com by default.
	 *
	 * @since 1.1.0
	 */
	protected function register_site_apiapi_structure() {
		apiapi_register_structure_wordpress( self::STRUCTURE_SLUG, rest_url( '/' ) );
	}
}
