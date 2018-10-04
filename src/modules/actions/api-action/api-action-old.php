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
use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Multi_Field_Element_Type_Interface;
use awsmug\Torro_Forms\APIAPI_Config;
use APIAPI\Core\Structures\Structure;
use APIAPI\Core\Structures\Route;
use APIAPI\Core\Request\API;
use APIAPI\Core\Request\Route_Request;
use APIAPI\Core\Request\Route_Response;
use APIAPI\Core\Exception as APIAPIException;
use Exception;
use WP_Error;

/**
 * Base class for an action.
 *
 * @since 1.0.0
 */
abstract class API_Action extends Action implements API_Action_Interface, Assets_Submodule_Interface {

	/**
	 * Name of the API structure this action uses.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $api_structure_name;

	/**
	 * Route URI of the API structure this action uses.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $api_route_uri;

	/**
	 * API request method.
	 *
	 * Either 'GET', 'POST', 'PUT', 'PATCH' or 'DELETE'. Default 'POST'.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $api_request_method = 'POST';

	/**
	 * The API structure this action uses.
	 *
	 * Do not use this property, but instead the api_structure() method.
	 *
	 * @since 1.0.0
	 * @var Structure|null
	 */
	protected $lazyloaded_api_structure = null;

	/**
	 * The API route this action uses.
	 *
	 * Do not use this property, but instead the api_route() method.
	 *
	 * @since 1.0.0
	 * @var Route|null
	 */
	protected $lazyloaded_api_route = null;

	/**
	 * The configured API instance this action uses.
	 *
	 * Do not use this property, but instead the api() method.
	 *
	 * @since 1.0.0
	 * @var API|null
	 */
	protected $lazyloaded_api = null;

	/**
	 * Internal flag for whether the API script used for all API actions has been registered.
	 *
	 * @since 1.0.0
	 * @static
	 * @var bool
	 */
	protected static $script_registered = false;

	/**
	 * Internal flag for whether the API script used for all API actions has been enqueued.
	 *
	 * @since 1.0.0
	 * @static
	 * @var bool
	 */
	protected static $script_enqueued = false;

	/**
	 * Handles the action for a specific form submission.
	 *
	 * @since 1.0.0
	 *
	 * @param Submission $submission Submission to handle by the action.
	 * @param Form       $form       Form the submission applies to.
	 * @return bool|WP_Error True on success, error object on failure.
	 */
	public function handle( $submission, $form ) {
		$form_options      = $this->get_form_options( $form->id );
		$submission_values = $submission->get_submission_values();

		$meta_map_fields = $this->get_meta_map_fields();

		$mappings = $this->get_mappings( $form->id );

		try {
			$request = $this->api()->get_request_object( $this->api_route_uri, $this->api_request_method );

			foreach ( $form_options as $key => $value ) {
				if ( ! isset( $meta_map_fields[ $key ] ) ) {
					continue;
				}

				if ( empty( $value ) ) {
					continue;
				}

				$request->set_param( $key, $value );
			}

			foreach ( $submission_values as $submission_value ) {
				if ( ! isset( $mappings[ $submission_value->element_id ] ) ) {
					continue;
				}

				$field = ! empty( $submission_value->field ) ? $submission_value->field : '_main';
				if ( ! isset( $mappings[ $submission_value->element_id ][ $field ] ) ) {
					continue;
				}

				if ( empty( $submission_value->value ) ) {
					continue;
				}

				$request->set_param( $mappings[ $submission_value->element_id ][ $field ], $submission_value->value );
			}

			$response = $this->module->apiapi()->send_request( $request );
		} catch ( Exception $e ) {
			return $this->process_error_response( $e, $submission, $form );
		}

		return $this->process_response( $response, $request, $submission, $form );
	}

	/**
	 * Returns the available meta fields for the submodule.
	 *
	 * @since 1.0.0
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
	 * Returns the element mappings for a given form ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id Form ID.
	 * @return array Multidimensional array, where the first level is `$element_id => $field_slugs` pairs and
	 *               the second level is `$field_slug => $mapped_param` pairs.
	 */
	final public function get_mappings( $form_id ) {
		$mappings = $this->module->manager()->meta()->get( 'post', $form_id, $this->module->manager()->get_prefix() . $this->slug . '_mappings', true );

		if ( is_array( $mappings ) ) {
			return $mappings;
		}

		return $mappings;
	}

	/**
	 * Saves the element mappings for a given form.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $form_id     Form ID.
	 * @param array $id_mappings Array of ID mappings from the elements that have just been saved.
	 */
	final public function save_mappings( $form_id, $id_mappings ) {
		$mappings = array();

		if ( isset( $_POST[ $this->module->manager()->get_prefix() . $this->slug . '_mappings' ] ) ) {
			$element_map_fields = $this->get_element_map_fields();

			$raw_mappings = wp_unslash( $_POST[ $this->module->manager()->get_prefix() . $this->slug . '_mappings' ] );

			foreach ( $raw_mappings as $element_id => $field_slugs ) {
				$real_fields = array();
				foreach ( $field_slugs as $field_slug => $mapped_param ) {
					if ( empty( $mapped_param ) ) {
						continue;
					}

					if ( ! isset( $element_map_fields[ $mapped_param ] ) ) {
						continue;
					}

					$real_fields[ $field_slug ] = $mapped_param;
				}

				if ( empty( $real_fields ) ) {
					continue;
				}

				$real_element_id = isset( $id_mappings[ $element_id ] ) ? $id_mappings[ $element_id ] : $element_id;

				$mappings[ $real_element_id ] = $real_fields;
			}
		}

		$this->module->manager()->meta()->update( 'post', $form_id, $this->module->manager()->get_prefix() . $this->slug . '_mappings', $mappings );
	}

	/**
	 * Registers the API-API hook for adding the necessary configuration data.
	 *
	 * @since 1.0.0
	 */
	public function register_config_data_hook() {
		$this->module->apiapi()->hook_on(
			'setup_config',
			function( $config ) {
				$this->add_config_data( $config );
			},
			5
		);
	}

	/**
	 * Registers all assets the submodule provides.
	 *
	 * @since 1.0.0
	 *
	 * @param Assets $assets The plugin assets instance.
	 */
	public function register_assets( $assets ) {
		if ( ! self::$script_registered ) {
			$assets->register_script(
				'admin-api-element-mapping',
				'assets/dist/js/admin-api-element-mapping.js',
				array(
					'deps'      => array( str_replace( '_', '-', $assets()->get_prefix() ) . 'admin-form-builder', 'jquery' ),
					'in_footer' => true,
				)
			);

			self::$script_registered = true;
		}
	}

	/**
	 * Enqueues scripts and stylesheets on the form editing screen.
	 *
	 * @since 1.0.0
	 *
	 * @param Assets $assets The plugin assets instance.
	 */
	public function enqueue_form_builder_assets( $assets ) {
		$prefixed_script_handle = str_replace( '_', '-', $assets()->get_prefix() ) . 'admin-api-element-mapping';

		if ( ! self::$script_enqueued ) {
			$assets->enqueue_script( 'admin-api-element-mapping' );

			wp_add_inline_script( $prefixed_script_handle, 'var torroAPIElementMappings = [];', 'before' );

			self::$script_enqueued = true;
		}

		$form = null;
		if ( ! empty( $_GET['post'] ) ) {
			$form = $this->module->manager()->forms()->get( absint( $_GET['post'] ) );
		}

		$output = 'torroAPIElementMappings.push(' . wp_json_encode( $this->get_js_data( $form ) ) . ');';
		wp_add_inline_script( $prefixed_script_handle, $output, 'before' );
	}

	/**
	 * Processes a response from an API request for a submission.
	 *
	 * @since 1.0.0
	 *
	 * @param Route_Response $response   API response object to process.
	 * @param Route_Request  $request    Original API request object the response addresses.
	 * @param Submission     $submission Submission for which the API request was made.
	 * @param Form           $form       Form the submission applies to.
	 * @return bool|WP_Error True on success, error object on failure.
	 */
	protected function process_response( $response, $request, $submission, $form ) {
		// The default implementation is to simply return true.
		return true;
	}

	/**
	 * Processes an exception thrown by an API request for a submission.
	 *
	 * @since 1.0.0
	 *
	 * @param Exception  $exception  Exception thrown by the API request.
	 * @param Submission $submission Submission for which the API request was made.
	 * @param Form       $form       Form the submission applies to.
	 * @return bool|WP_Error True on success, error object on failure.
	 */
	protected function process_error_response( $exception, $submission, $form ) {
		// The default implementation simply transforms the exceptions into errors.
		if ( is_a( $exception, APIAPIException::class ) ) {
			/* translators: 1: name of the API, 2: error message */
			return new WP_Error( 'apirequest_apiapi_exception', sprintf( __( 'An API error occurred while trying to call the %1$s API. Original error message: %2$s', 'torro-forms' ), $this->api()->get_title(), $exception->getMessage() ), $exception->getData() );
		}

		/* translators: 1: name of the API, 2: error message */
		return new WP_Error( 'apirequest_exception', sprintf( __( 'An error occurred while trying to call the %1$s API. Original error message: %2$s', 'torro-forms' ), $this->api()->get_title(), $exception->getMessage() ) );
	}

	/**
	 * Returns the API fields that an element can map to.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	abstract protected function get_element_map_fields();

	/**
	 * Returns the API fields that a meta value should exist for to map to.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	abstract protected function get_meta_map_fields();

	/**
	 * Returns all fields for request parameters.
	 *
	 * This method can be used by the get_element_map_fields() and get_meta_map_fields() methods
	 * to get the full list and then filter the parameter fields that should be mappable.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	protected function get_parameter_fields() {
		$structure = $this->api_structure();
		$route     = $this->api_route();
		$method    = $this->api_request_method;

		$mode = $this->api()->get_mode();

		$params = array_merge( $structure->get_base_uri_params( $mode ), $route->get_method_params( $method ) );

		$fields = array();
		foreach ( $params as $param => $param_info ) {
			if ( ! empty( $param_info['internal'] ) ) {
				continue;
			}

			$field = array(
				'label'       => $param,
				'description' => $param_info['description'],
				'default'     => $param_info['default'],
				'required'    => $param_info['required'],
			);

			switch ( $param_info['type'] ) {
				case 'boolean':
					$field['type'] = 'checkbox';
					break;
				case 'float':
				case 'number':
					$field['type'] = 'number';
					$field['step'] = 0.001;
					break;
				case 'integer':
					$field['type'] = 'number';
					$field['step'] = 1;
					break;
				case 'array':
					if ( ! empty( $param_info['enum'] ) ) {
						$field['type']    = 'multiselect';
						$field['choices'] = array_combine( $param_info['enum'], $param_info['enum'] );
					} elseif ( ! empty( $param_info['items']['enum'] ) ) {
						$field['type']    = 'multiselect';
						$field['choices'] = array_combine( $param_info['items']['enum'], $param_info['items']['enum'] );
					} elseif ( ! empty( $param_info['items']['type'] ) ) {
						$field['repeatable'] = true;
						switch ( $param_info['items']['type'] ) {
							case 'boolean':
								$field['type'] = 'checkbox';
								break;
							case 'float':
							case 'number':
								$field['type'] = 'number';
								$field['step'] = 0.001;
								break;
							case 'integer':
								$field['type'] = 'number';
								$field['step'] = 1;
								break;
							case 'string':
							default:
								$field['type'] = 'text';
						}
					} else {
						$field['type']       = 'text';
						$field['repeatable'] = true;
					}
					break;
				case 'string':
				default:
					if ( ! empty( $param_info['enum'] ) ) {
						$field['type']    = 'select';
						$field['choices'] = array_combine( $param_info['enum'], $param_info['enum'] );
					} else {
						$field['type'] = 'text';
					}
			}

			$fields[ $param ] = $field;
		}

		return $fields;
	}

	/**
	 * Returns the settings fields for the API's authentication data.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	protected function get_authentication_fields() {
		$authenticator = $this->api()->get_authenticator();

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

	/**
	 * Returns API action data to pass to the script file.
	 *
	 * @since 1.0.0
	 *
	 * @param Form|null $form Optional. Form for which to generate the data. Default null.
	 * @return array Data to pass to JavaScript.
	 */
	final protected function get_js_data( $form = null ) {
		return array(
			'actionSlug'   => $this->slug,
			'mappingsName' => $this->module->manager()->get_prefix() . $this->slug . '_mappings',
			'metaName'     => $this->get_meta_identifier(),
			'mapFields'    => $this->get_element_map_fields(),
			'enabled'      => $form ? $this->enabled( $form ) : false,
			'mappings'     => $form ? $this->get_mappings( $form->id ) : array(),
		);
	}

	/**
	 * Adds the necessary API-API configuration data.
	 *
	 * @since 1.0.0
	 *
	 * @param APIAPI_Config $config The plugin's API-API configuration.
	 */
	protected function add_config_data( $config ) {
		$authentication_fields = $this->get_authentication_fields();
		if ( empty( $authentication_fields ) ) {
			return;
		}

		$options = $this->get_options();

		$authentication_data = array_filter( array_intersect_key( $options, $authentication_fields ) );
		if ( empty( $authentication_data ) ) {
			return;
		}

		$config_key = $this->api_structure()->get_config_key();

		if ( $config->exists( $config_key, 'authentication_data' ) ) {
			$authentication_data = array_merge( (array) $config->get( $config_key, 'authentication_data' ), $authentication_data );
		}

		$config->set( $config_key, 'authentication_data', $authentication_data );
	}

	/**
	 * Returns the API structure this action uses.
	 *
	 * The API structure is not scoped for the plugin. If you need the configured variant of the API,
	 * use the api() method. If you don't though, this method is more efficient to use then.
	 *
	 * @since 1.0.0
	 *
	 * @return Structure The API structure.
	 */
	protected function api_structure() {
		if ( null === $this->lazyloaded_api_structure ) {
			$this->lazyloaded_api_structure = apiapi_manager()->structures()->get( $this->api_structure_name );
		}

		return $this->lazyloaded_api_structure;
	}

	/**
	 * Returns the API route this action uses.
	 *
	 * @since 1.0.0
	 *
	 * @return Route The API route.
	 */
	protected function api_route() {
		if ( null === $this->lazyloaded_api_route ) {
			$this->lazyloaded_api_route = $this->api_structure()->get_route_object( $this->api_route_uri );
		}

		return $this->lazyloaded_api_route;
	}

	/**
	 * Returns the configured API instance this action uses.
	 *
	 * @since 1.0.0
	 *
	 * @return API The configured API instance.
	 */
	protected function api() {
		if ( null === $this->lazyloaded_api ) {
			$this->lazyloaded_api = $this->module->apiapi()->get_api_object( $this->api_structure_name );
		}

		return $this->lazyloaded_api;
	}
}
