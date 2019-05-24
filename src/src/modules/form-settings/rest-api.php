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
			'allow_submissions_by_token'      => array(
				'type'        => 'radio',
				'label'       => __( 'Verify submissions via Token.', 'torro-forms' ),
				'description' => __( 'Take rorm token and and sent it by POST method to the Rest API submission endpoints via the parameter <code>torro_token</code> to verify the submission.', 'torro-forms' ),
				'default'     => 'no',
				'choices'     => array(
					'yes' => __( 'Yes', 'torro-forms' ),
					'no'  => __( 'No', 'torro-forms' ),
				),
			),
			'token'                           => array(
				'type'          => 'text',
				'label'         => __( 'Form Token', 'torro-forms' ),
				'description'   => __( 'Token which is needed for accessing Rest API submissions.', 'torro-forms' ),
				'default'       => $token,
				'input_classes' => array( 'regular-text' ),
			),
			'allow_submissions_by_dump_nonce' => array(
				'type'        => 'radio',
				'label'       => __( 'Verify submissions via dump nonces.', 'torro-forms' ),
				'description' => __( 'Create a nonce by<code>Dump_Nonce::create()</code> and sent it by POST method to the Rest API submission endpoints via the parameter <code>torro_dump_nonce</code> to verify the submission.', 'torro-forms' ),
				'default'     => 'no',
				'choices'     => array(
					'yes' => __( 'Yes', 'torro-forms' ),
					'no'  => __( 'No', 'torro-forms' ),
				),
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
		$allow = $this->get_form_option( $form->id, 'allow_submissions_by_token' );

		if ( 'yes' === $allow ) {
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
	public function check_submission_dump_nonce( $enabled, $form, $request) {
		$allow = $this->get_form_option( $form->id, 'allow_submissions_by_dump_nonce' );

		if ( 'yes' === $allow ) {
			$nonce = $request->get_param( 'torro_dump_nonce' );

			if ( Dump_Nonce::check( $nonce ) ) {
				return true;
			}
		}

		return $enabled;
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
	}
}
