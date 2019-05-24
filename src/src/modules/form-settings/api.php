<?php
/**
 * API form setting class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Form_Settings;

use awsmug\Torro_Forms\Components\Dump_Nonces;

/**
 * Class for form settings for API.
 *
 * @since 1.1.0
 */
class API extends Form_Setting {

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.1.0
	 */
	protected function bootstrap() {
		$this->slug        = 'api';
		$this->title       = __( 'API', 'torro-forms' );
		$this->description = __( 'Adjust API settings.', 'torro-forms' );
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
			'allow_submissions_by_token' => array(
				'type'        => 'radio',
				'label'       => __( 'Allow submissions via Token.', 'torro-forms' ),
				'description' => __( 'Opens the Rest API submissions endpoint with a token.', 'torro-forms' ),
				'default'     => $token,
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
	 * Adjusting submission endpoint.
	 *
	 * @since 1.1.0
	 *
	 * @param bool                                     $enabled Wether submussion endpoint cann be accessed or not.
	 * @param awsmug\Torro_Forms\DB_Objects\Forms\Form $form Form object.
	 * @return bool                                    $enabled Wether submussion endpoint cann be accessed or not.
	 */
	public function check_submission_token( $enabled, $form ) {
		$allow = $this->get_form_option( $form->id, 'allow_submissions_by_token' );

		if ( 'yes' === $allow ) {
			$token = filter_input( INPUT_POST, 'torro_token' );

			if ( $this->get_form_option( $form->id, 'token' ) === $token ) {
				return true;
			}
			return false;
		}

		return false;
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
			'num_args' => 2,
		);

		$this->filters[] = array(
			'name'     => "{$prefix}rest_api_can_update_submission",
			'callback' => array( $this, 'check_submission_token' ),
			'priority' => 10,
			'num_args' => 2,
		);

		$this->filters[] = array(
			'name'     => "{$prefix}rest_api_can_create_submission_value",
			'callback' => array( $this, 'check_submission_token' ),
			'priority' => 10,
			'num_args' => 2,
		);

		$this->filters[] = array(
			'name'     => "{$prefix}rest_api_can_update_submission_value",
			'callback' => array( $this, 'check_submission_token' ),
			'priority' => 10,
			'num_args' => 2,
		);
	}
}
