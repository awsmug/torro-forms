<?php
/**
 * API form setting class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Form_Settings;

use awsmug\Torro_Forms\Components\Dump_Nonce;

/**
 * Class for Rest API settings in Form settings.
 *
 * @since 1.1.0
 */
class Rest_API extends Form_Setting {

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.1.0
	 */
	protected function bootstrap() {
		$this->slug        = 'api';
		$this->title       = __( 'Rest API', 'torro-forms' );
		$this->description = __( 'Adjust Rest-API settings.', 'torro-forms' );
	}

	/**
	 * Returns the available meta box fields for the module.
	 *
	 * @since 1.1.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$prefix = $this->module->get_prefix();

		$token = wp_hash( implode( '', $_SERVER ) . microtime() );

		$meta_fields = array(
			'verification_mode' => array(
				'type'        => 'select',
				'label'       => __( 'Verification', 'torro-forms' ),
				'description' => __( 'Verification mode for submission endpoint.', 'torro-forms' ),
				'default'     => 'token',
				'choices'     => array(
					'dump_nonce' => __( 'Dump Nonce', 'torro-forms' ),
					'token'      => __( 'Token', 'torro-forms' ),
					'no'         => __( 'No Verification', 'torro-forms' ),
				),
			),
			'token'             => array(
				'type'          => 'text',
				'label'         => __( 'Form Token', 'torro-forms' ),
				'description'   => __( 'Token which is needed for accessing Rest API submissions.', 'torro-forms' ),
				'default'       => $token,
				'input_classes' => array( 'regular-text' ),
			),

		);

		$meta_fields['token'] = array(
			'type'          => 'text',
			'label'         => __( 'Form Token', 'torro-forms' ),
			'description'   => __( 'Token which is needed for accessing Rest API submissions.', 'torro-forms' ),
			'default'       => $token,
			'input_classes' => array( 'regular-text' ),
		);

		/**
		 * Filters the meta fields in the form settings metabox.
		 *
		 * @since 1.1.0
		 *
		 * @param array $meta_fields Array of `$field_slug => $field_data` pairs.
		 */
		return apply_filters( "{$prefix}form_settings_labels_meta_fields", $meta_fields );
	}

	/**
	 * Pass submission.
	 *
	 * @since 1.1.0
	 *
	 * @param bool                                     $enabled Wether submussion endpoint cann be accessed or not.
	 * @param awsmug\Torro_Forms\DB_Objects\Forms\Form $form Form object.
	 * @param \WP_REST_Request                         $request Full details about the request.
	 * @return bool                                    $enabled Wether submussion endpoint cann be accessed or not.
	 */
	public function pass_submission( $enabled, $form, $request ) {
		$mode = $this->get_form_option( $form->id, 'verification_mode' );

		if ( 'no' === $mode ) {
			return true;
		}

		return $enabled;
	}

	/**
	 * Check submission dump nonce.
	 *
	 * @since 1.1.0
	 *
	 * @param bool                                     $enabled Wether submussion endpoint cann be accessed or not.
	 * @param awsmug\Torro_Forms\DB_Objects\Forms\Form $form Form object.
	 * @param \WP_REST_Request                         $request Full details about the request.
	 * @return bool                                    $enabled Wether submussion endpoint cann be accessed or not.
	 */
	public function check_submission_token( $enabled, $form, $request ) {
		$mode = $this->get_form_option( $form->id, 'verification_mode' );

		if ( 'token' === $mode ) {
			$token = $request->get_param( 'torro_token' );

			if ( $this->get_form_option( $form->id, 'token' ) === $token ) {
				return true;
			}
		}

		return $enabled;
	}

	/**
	 * Check submission dump nonce.
	 *
	 * @since 1.1.0
	 *
	 * @param bool                                     $enabled Wether submussion endpoint cann be accessed or not.
	 * @param awsmug\Torro_Forms\DB_Objects\Forms\Form $form    Form object.
	 * @param \WP_REST_Request                         $request Full details about the request.
	 * @return bool                                    $enabled Wether submussion endpoint cann be accessed or not.
	 */
	public function check_submission_dump_nonce( $enabled, $form, $request ) {
		$mode = $this->get_form_option( $form->id, 'verification_mode' );

		if ( 'dump_nonce' === $mode ) {
			$nonce = $request->get_param( 'torro_dump_nonce' );

			if ( Dump_Nonce::check( $nonce ) ) {
				return true;
			}
		}

		return $enabled;
	}

	/**
	 * Addingn submission dump nonce to response.
	 *
	 * @since 1.1.0
	 *
	 * @param \WP_REST_Response $response Response object.
	 * @return \WP_REST_Response $response Response object.
	 */
	public function response_add_submission_dump_nonce( $response ) {
		$data    = $response->get_data();
		$form_id = $data['form_id'];
		$mode    = $this->get_form_option( $form_id, 'verification_mode' );

		if ( 'yes' === $mode ) {
			$data['torro_dump_nonce'] = Dump_Nonce::create();
			$response->set_data( $data );
		}

		return $response;
	}

	/**
	 * Adding submission value dump nonce to response.
	 *
	 * @since 1.1.0
	 *
	 * @param \WP_REST_Response $response Response object.
	 * @return \WP_REST_Response $response Response object.
	 */
	public function response_add_submission_value_dump_nonce( $response ) {
		$data       = $response->get_data();
		$element_id = $data['element_id'];

		$form_id = torro()->elements()->get( $element_id )->get_container()->get_form()->id;

		$allow = $this->get_form_option( $form_id, 'verify_submissions_by_dump_nonce' );

		if ( 'yes' === $allow ) {
			$data['torro_dump_nonce'] = Dump_Nonce::create();
			$response->set_data( $data );
		}

		return $response;
	}

	public function filter_response( $response ) {
		$data = $response->get_data();

		if ( ! array_key_exists( 'form_id', $data ) && ! array_key_exists( 'element_id', $data ) ) {
			return $response;
		}

		$form_id = null;

		if( array_key_exists( 'form_id', $data ) ) {
			$form_id = $data['form_id'];
		}

		if( array_key_exists( 'element_id', $data ) ) {
			$form_id = torro()->elements()->get( $data['element_id'] )->get_container()->get_form()->id;
		}

		$allow = $this->get_form_option( $form_id, 'verify_submissions_by_dump_nonce' );

		if ( 'yes' === $allow ) {
			$data['torro_dump_nonce'] = Dump_Nonce::create();
			$response->set_data( $data );
		}

		return $response;
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * Hooks declared here must occur at some point after `init`.
	 *
	 * @since 1.1.0
	 */
	protected function setup_hooks() {
		$prefix = $this->module->get_prefix();

		$this->filters[] = array(
			'name'     => "{$prefix}rest_api_can_create_submission",
			'callback' => array( $this, 'pass_submission' ),
			'priority' => 10,
			'num_args' => 3,
		);

		$this->filters[] = array(
			'name'     => "{$prefix}rest_api_can_update_submission",
			'callback' => array( $this, 'pass_submission' ),
			'priority' => 10,
			'num_args' => 3,
		);

		$this->filters[] = array(
			'name'     => "{$prefix}rest_api_can_create_submission_value",
			'callback' => array( $this, 'pass_submission' ),
			'priority' => 10,
			'num_args' => 3,
		);

		$this->filters[] = array(
			'name'     => "{$prefix}rest_api_can_update_submission_value",
			'callback' => array( $this, 'pass_submission' ),
			'priority' => 10,
			'num_args' => 3,
		);

		$this->filters[] = array(
			'name'     => "{$prefix}rest_api_can_create_submission",
			'callback' => array( $this, 'check_submission_token' ),
			'priority' => 10,
			'num_args' => 3,
		);

		$this->filters[] = array(
			'name'     => "{$prefix}rest_api_can_update_submission",
			'callback' => array( $this, 'check_submission_token' ),
			'priority' => 10,
			'num_args' => 3,
		);

		$this->filters[] = array(
			'name'     => "{$prefix}rest_api_can_create_submission_value",
			'callback' => array( $this, 'check_submission_token' ),
			'priority' => 10,
			'num_args' => 3,
		);

		$this->filters[] = array(
			'name'     => "{$prefix}rest_api_can_update_submission_value",
			'callback' => array( $this, 'check_submission_token' ),
			'priority' => 10,
			'num_args' => 3,
		);

		$this->filters[] = array(
			'name'     => "{$prefix}rest_api_can_create_submission",
			'callback' => array( $this, 'check_submission_dump_nonce' ),
			'priority' => 10,
			'num_args' => 3,
		);

		$this->filters[] = array(
			'name'     => "{$prefix}rest_api_can_update_submission",
			'callback' => array( $this, 'check_submission_dump_nonce' ),
			'priority' => 10,
			'num_args' => 3,
		);

		$this->filters[] = array(
			'name'     => "{$prefix}rest_api_can_create_submission_value",
			'callback' => array( $this, 'check_submission_dump_nonce' ),
			'priority' => 10,
			'num_args' => 3,
		);

		$this->filters[] = array(
			'name'     => "{$prefix}rest_api_can_update_submission_value",
			'callback' => array( $this, 'check_submission_dump_nonce' ),
			'priority' => 10,
			'num_args' => 3,
		);
		/*
		$this->filters[] = array(
			'name'     => 'rest_post_dispatch',
			'callback' => array( $this, 'filter_response' ),
			'priority' => 10,
			'num_args' => 1,
		);
		*/

		$this->filters[] = array(
			'name'     => "{$prefix}rest_api_submission_response",
			'callback' => array( $this, 'response_add_submission_dump_nonce' ),
			'priority' => 10,
			'num_args' => 1,
		);


		$this->filters[] = array(
			'name'     => "{$prefix}rest_api_submission_value_response",
			'callback' => array( $this, 'response_add_submission_value_dump_nonce' ),
			'priority' => 10,
			'num_args' => 1,
		);

	}
}
