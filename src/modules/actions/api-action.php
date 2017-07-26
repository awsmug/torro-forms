<?php
/**
 * Action base class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Actions;

use awsmug\Torro_Forms\Modules\Assets_Submodule_Interface;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use awsmug\Torro_Forms\WP_Error;
use APIAPI\Core\Request\API;

/**
 * Base class for an action.
 *
 * @since 1.0.0
 */
abstract class API_Action extends Action implements Assets_Submodule_Interface {

	/**
	 * Name of the API structure this action uses.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $api_structure;

	/**
	 * Route URI of the API structure this action uses.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $api_route;

	/**
	 * The API structure instance this action uses.
	 *
	 * Do not use this property, but instead the api() method.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var API|null
	 */
	protected $lazyloaded_api = null;

	/**
	 * Returns the API structure instance this action uses.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return API The API structure instance.
	 */
	public function api() {
		if ( null === $this->lazyloaded_api ) {
			$this->lazyloaded_api = $this->module->apiapi()->get_api_object( $this->api_structure );
		}

		return $this->lazyloaded_api;
	}

	/**
	 * Handles the action for a specific form submission.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Submission $submission Submission to handle by the action.
	 * @param Form       $form       Form the submission applies to.
	 * @return bool|WP_Error True on success, error object on failure.
	 */
	public abstract function handle( $submission, $form );

	/**
	 * Returns the available meta fields for the submodule.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		return array_merge( parent::get_meta_fields(), $this->get_meta_map_fields() );
	}

	/**
	 * Returns the available settings sections for the submodule.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Associative array of `$section_slug => $section_args` pairs.
	 */
	public function get_settings_sections() {
		$settings_sections = parent::get_settings_sections();

		$authentication_fields = $this->get_authentication_fields();
		if ( empty( $authentication_fields ) ) {
			return $settings_sections;
		}

		$settings_sections['authentication'] = array(
			'title' => __( 'Authentication', 'torro-forms' ),
		);

		return $settings_sections;
	}

	/**
	 * Returns the available settings fields for the submodule.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_settings_fields() {
		$authentication_fields = $this->get_authentication_fields();
		foreach ( $authentication_fields as $field_slug => $field_args ) {
			$authentication_fields[ $field_slug ]['section'] = 'authentication';
		}

		return array_merge( parent::get_settings_fields(), $authentication_fields );
	}

	/**
	 * Returns the API fields that an element can map to.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	protected abstract function get_element_map_fields();

	/**
	 * Returns the API fields that a meta value should exist for to map to.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	protected abstract function get_meta_map_fields();

	/**
	 * Returns the settings fields for the API's authentication data.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	protected function get_authentication_fields() {
		$authenticator       = $this->api()->get_authenticator();

		// TODO: Use authentication data to detect what has been set programmatically.
		$authenticator_defaults = $this->api()->get_authentication_data_defaults();

		$title = $this->api()->get_title();

		$fields = array();

		switch ( $authenticator ) {
			case 'oauth1':
				$fields = array(
					'consumer_key'    => array(
						'type'        => 'text',
						'label'       => __( 'API Consumer Key', 'torro-forms' ),
						/* translators: %s: an API title */
						'description' => sprintf( __( 'Enter the consumer key for the %s API.', 'torro-forms' ), $title ),
					),
					'consumer_secret' => array(
						'type'        => 'text',
						'label'       => __( 'API Consumer Secret', 'torro-forms' ),
						/* translators: %s: an API title */
						'description' => sprintf( __( 'Enter the consumer secret for the %s API.', 'torro-forms' ), $title ),
					),
				);
				break;
			case 'x':
				if ( empty( $authenticator_defaults['header_name'] ) ) {
					$fields['header_name'] = array(
						'type'        => 'text',
						'label'       => __( 'Authorization Header Name', 'torro-forms' ),
						/* translators: %s: an API title */
						'description' => sprintf( __( 'Enter the name of the authorization header that is sent to verify %s API requests. It will be prefixed with &#8220;X-&#8221;.', 'torro-forms' ), $title ),
						'default'     => 'Authorization',
					);
				}
				$fields['token'] = array(
					'type'        => 'text',
					'label'       => __( 'Authorization Token', 'torro-forms' ),
					/* translators: %s: an API title */
					'description' => sprintf( __( 'Enter the authorization token for the %s API.', 'torro-forms' ), $title ),
				);
				break;
			case 'x-account':
				if ( empty( $authenticator_defaults['placeholder_name'] ) ) {
					$fields['placeholder_name'] = array(
						'type'        => 'text',
						'label'       => __( 'Account Placeholder Name', 'torro-forms' ),
						/* translators: %s: an API title */
						'description' => sprintf( __( 'Enter the name of the placeholder in the URI used to verify %s API requests.', 'torro-forms' ), $title ),
						'default'     => 'account',
					);
				}
				$fields['account'] = array(
					'type'        => 'text',
					'label'       => __( 'Account Identifier', 'torro-forms' ),
					/* translators: %s: an API title */
					'description' => sprintf( __( 'Enter the account identifier for the %s API.', 'torro-forms' ), $title ),
				);
				if ( empty( $authenticator_defaults['header_name'] ) ) {
					$fields['header_name'] = array(
						'type'        => 'text',
						'label'       => __( 'Authorization Header Name', 'torro-forms' ),
						/* translators: %s: an API title */
						'description' => sprintf( __( 'Enter the name of the authorization header that is sent to verify %s API requests. It will be prefixed with &#8220;X-&#8221;.', 'torro-forms' ), $title ),
						'default'     => 'Authorization',
					);
				}
				$fields['token'] = array(
					'type'        => 'text',
					'label'       => __( 'Authorization Token', 'torro-forms' ),
					/* translators: %s: an API title */
					'description' => sprintf( __( 'Enter the authorization token for the %s API.', 'torro-forms' ), $title ),
				);
				break;
			case 'basic':
				$fields = array(
					'username' => array(
						'type'        => 'text',
						'label'       => __( 'API Username', 'torro-forms' ),
						/* translators: %s: an API title */
						'description' => sprintf( __( 'Enter the username for the %s API.', 'torro-forms' ), $title ),
					),
					'password' => array(
						'type'        => 'text',
						'label'       => __( 'API Password', 'torro-forms' ),
						/* translators: %s: an API title */
						'description' => sprintf( __( 'Enter the password for the %s API.', 'torro-forms' ), $title ),
					),
				);
				break;
			case 'key':
				if ( empty( $authenticator_defaults['parameter_name'] ) ) {
					$fields['parameter_name'] = array(
						'type'        => 'text',
						'label'       => __( 'API Key Parameter Name', 'torro-forms' ),
						/* translators: %s: an API title */
						'description' => sprintf( __( 'Enter the name of the request parameter that is sent to verify %s API requests.', 'torro-forms' ), $title ),
						'default'     => 'key',
					);
				}
				$fields['key'] = array(
					'type'        => 'text',
					'label'       => __( 'API Key', 'torro-forms' ),
					/* translators: %s: an API title */
					'description' => sprintf( __( 'Enter the API key for the %s API.', 'torro-forms' ), $title ),
				);
				break;
		}

		return $fields;
	}
}
