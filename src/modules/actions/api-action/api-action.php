<?php
/**
 * Action base class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Actions\API_Action;

use awsmug\Torro_Forms\Components\Template_Tag_Handler;
use awsmug\Torro_Forms\Modules\Assets_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Settings_Assets_Submodule_Interface;
use awsmug\Torro_Forms\Assets;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use awsmug\Torro_Forms\Modules\Actions\Action;
use awsmug\Torro_Forms\Modules\Actions\API_Action\Connections\Connection;
use awsmug\Torro_Forms\Modules\Actions\API_Action\Connections\OAuth2_Connection;
use awsmug\Torro_Forms\Modules\Actions\API_Action\Connections\OAuth1_Connection;
use awsmug\Torro_Forms\Modules\Actions\API_Action\Connections\X_Account_Connection;
use awsmug\Torro_Forms\Modules\Actions\API_Action\Connections\X_Connection;
use awsmug\Torro_Forms\Modules\Actions\API_Action\Connections\Basic_Connection;
use awsmug\Torro_Forms\Modules\Actions\API_Action\Connections\Key_Connection;
use APIAPI\Core\Structures\Structure;
use APIAPI\Core\Structures\Route;
use APIAPI\Core\Request\API;
use Exception;
use WP_Error;

/**
 * Base class for an action.
 *
 * @since 1.1.0
 */
abstract class API_Action extends Action implements API_Action_Interface, Assets_Submodule_Interface, Settings_Assets_Submodule_Interface {

	/**
	 * The available API structures and their routes.
	 *
	 * @since 1.1.0
	 * @var array|null
	 */
	protected $available_structures = null;

	/**
	 * The structure instances, for internal use.
	 *
	 * @since 1.1.0
	 * @var array
	 */
	private $structure_instances = array();

	/**
	 * The structure API instances, for internal use.
	 *
	 * @since 1.1.0
	 * @var array
	 */
	private $structure_api_instances = array();

	/**
	 * Template tag handler for API actions.
	 *
	 * @since 1.1.0
	 * @static
	 * @var Template_Tag_Handler
	 */
	private static $template_tag_handler;

	/**
	 * Internal flag for whether the API script used for all API actions has been registered.
	 *
	 * @since 1.1.0
	 * @static
	 * @var bool
	 */
	private static $script_registered = false;

	/**
	 * Internal flag for whether the API script used for all API actions has been enqueued.
	 *
	 * @since 1.1.0
	 * @static
	 * @var bool
	 */
	private static $script_enqueued = false;

	/**
	 * Internal flag for whether the API template tag handlers have been registered.
	 *
	 * @since 1.1.0
	 * @static
	 * @var bool
	 */
	private static $template_tag_handlers_registered = false;

	/**
	 * Constructor.
	 *
	 * In addition to calling the parent constructor, it registers the template tag handlers used for all API actions.
	 *
	 * @since 1.1.0
	 *
	 * @param Module $module The submodule's parent module instance.
	 */
	public function __construct( $module ) {
		parent::__construct( $module );

		$this->register_template_tag_handlers();
	}

	/**
	 * Checks whether the action is enabled for a specific form.
	 *
	 * @since 1.1.0
	 *
	 * @param Form $form Form object to check.
	 * @return bool True if the action is enabled, false otherwise.
	 */
	public function enabled( $form ) {
		$integrations = $this->get_form_option( $form->id, 'integrations', array() );

		$integrations = array_filter(
			$integrations,
			function( $integration ) {
				return ! empty( $integration['connection'] ) && ! empty( $integration['route'] ) && ! empty( $integration['mappings'] );
			}
		);

		if ( ! empty( $integrations ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Handles the action for a specific form submission.
	 *
	 * @since 1.1.0
	 *
	 * @param Submission $submission Submission to handle by the action.
	 * @param Form       $form       Form the submission applies to.
	 * @return bool|WP_Error True on success, error object on failure.
	 */
	public function handle( $submission, $form ) {
		$integrations = $this->get_form_option( $form->id, 'integrations', array() );
		if ( empty( $integrations ) ) {
			return true;
		}

		$dynamic_template_tags = $this->get_dynamic_template_tags( $form );

		foreach ( $dynamic_template_tags as $slug => $data ) {
			self::$template_tag_handler->add_tag( $slug, $data );
		}

		$error = new WP_Error();

		$connections = $this->get_available_connections();
		foreach ( $integrations as $integration ) {
			if ( empty( $integration['connection'] ) || empty( $integration['route'] ) || empty( $integration['mappings'] ) ) {
				continue;
			}

			if ( ! isset( $connections[ $integration['connection'] ] ) ) {
				continue;
			}

			$result = $this->handle_single_integration(
				$connections[ $integration['connection'] ],
				$integration['route'],
				$integration['mappings'],
				$submission
			);
			if ( is_wp_error( $result ) ) {
				foreach ( $result->errors as $error_code => $error_messages ) {
					foreach ( $error_messages as $error_message ) {
						$error->add( $error_code, $error_message );
					}
				}
			}
		}

		foreach ( $dynamic_template_tags as $slug => $data ) {
			self::$template_tag_handler->remove_tag( $slug );
		}

		if ( ! empty( $error->errors ) ) {
			return $error;
		}

		return true;
	}

	/**
	 * Handles a single action integration for a specific form submission.
	 *
	 * @since 1.1.0
	 *
	 * @param Connection $connection API connection instance with structure and authentication details.
	 * @param string     $route_slug API-API route identifier.
	 * @param array      $mappings   Mapping definitions for the route, as specified via the form editing screen.
	 * @param Submission $submission Submission to handle by the action.
	 * @return bool|WP_Error True on success, error object on failure.
	 */
	protected function handle_single_integration( $connection, $route_slug, $mappings, $submission ) {
		$structure_slug = $connection->get_structure();
		$method         = 'POST';
		if ( preg_match( '/^(GET|POST|PUT|PATCH|DELETE)\:/', $route_slug, $matches ) ) {
			$method     = $matches[1];
			$route_slug = substr( $route_slug, strlen( $method ) + 1 );
		}

		$api_structure = $this->api_structure( $structure_slug );
		$routes        = $this->get_available_routes( $structure_slug );

		try {
			$request = $this->api( $structure_slug )->get_request_object( $route_slug, $method );

			// TODO.
		} catch ( Exception $e ) {
			return new WP_Error( 'apiapi_' . $structure_slug . '_error', $e->getMessage() );
		}

		return true;
	}

	/**
	 * Returns the available meta fields for the submodule.
	 *
	 * @since 1.1.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$meta_fields = parent::get_meta_fields();

		unset( $meta_fields['enabled'] );

		$connection_choices = array_merge(
			array(
				'' => __( 'Select a connection...', 'torro-forms' ),
			),
			$this->get_available_connection_choices()
		);

		$settings_url = add_query_arg(
			array(
				'page'   => torro()->forms()->get_prefix() . 'form_settings',
				'tab'    => $this->module->manager()->get_prefix() . 'module_actions',
				'subtab' => $this->slug,
			),
			admin_url( 'edit.php?post_type=' . torro()->post_types()->get_prefix() . 'form' )
		);

		$meta_fields['integrations'] = array(
			'type'        => 'group',
			'label'       => __( 'Integrations', 'torro-forms' ),
			'description' => __( 'Add one or more integrations for the API.', 'torro-forms' ),
			'repeatable'  => true,
			'fields'      => array(
				'connection' => array(
					'type'        => 'select',
					'label'       => __( 'Connection', 'torro-forms' ),
					/* translators: %s: settings page URL */
					'description' => sprintf( __( 'Select one of the connections here that you have created in the <a href="%s">plugin settings</a>.', 'torro-forms' ), $settings_url ),
					'choices'     => $connection_choices,
					'required'    => true,
				),
				'route'      => array(
					'type'         => 'select',
					'label'        => __( 'Route', 'torro-forms' ),
					'description'  => __( 'Select the API connection route to submit the data to.', 'torro-forms' ),
					'choices'      => array(),
					'required'     => true,
					'dependencies' => array(
						array(
							'prop'     => 'choices',
							'callback' => 'torro_get_api_connection_routes',
							'fields'   => array( 'connection' ),
							'args'     => array(
								'api_action' => $this->slug,
							),
						),
					),
				),
				'mappings'   => array(
					'type'         => 'fieldmappings',
					'label'        => __( 'Field Mappings', 'torro-forms' ),
					'description'  => __( 'Map form elements to the fields needed for the API route.', 'torro-forms' ),
					'fields'       => array(),
					'required'     => true,
					'dependencies' => array(
						array(
							'prop'     => 'fields',
							'callback' => 'torro_get_api_connection_route_fields',
							'fields'   => array( 'connection', 'route' ),
							'args'     => array(
								'api_action' => $this->slug,
							),
						),
					),
				),
			),
		);

		return $meta_fields;
	}

	/**
	 * Returns the available settings sections for the submodule.
	 *
	 * @since 1.1.0
	 *
	 * @return array Associative array of `$section_slug => $section_args` pairs.
	 */
	public function get_settings_sections() {
		$settings_sections = parent::get_settings_sections();

		$settings_sections['authentication'] = array(
			'title' => __( 'Authentication', 'torro-forms' ),
		);

		return $settings_sections;
	}

	/**
	 * Returns the available settings fields for the submodule.
	 *
	 * @since 1.1.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_settings_fields() {
		$settings_fields = parent::get_settings_fields();

		$structure_choices = $this->get_available_structure_choices();

		$settings_fields['connections'] = array(
			'section'     => 'authentication',
			'type'        => 'group',
			'label'       => __( 'Connections', 'torro-forms' ),
			'description' => __( 'Add API connections that you can then use when setting up a form integration.', 'torro-forms' ),
			'repeatable'  => true,
			'fields'      => array(
				'title'     => array(
					'type'          => 'text',
					'label'         => __( 'Title', 'torro-forms' ),
					'description'   => __( 'Enter a title for that connection to be used internally. You can access that connection via that title.', 'torro-forms' ),
					'input_classes' => array( 'regular-text' ),
					'required'      => true,
				),
				'slug'      => array(
					'type'    => 'text',
					'label'   => __( 'Slug', 'torro-forms' ),
					'display' => false,
				),
				'structure' => array(
					'type'        => 'select',
					'label'       => __( 'API Structure', 'torro-forms' ),
					'description' => __( 'Select the API this connection applies to.', 'torro-forms' ),
					'choices'     => $structure_choices,
					'default'     => key( $structure_choices ),
					'display'     => count( $structure_choices ) > 1,
					'required'    => count( $structure_choices ) > 1,
				),
			),
		);

		foreach ( static::get_registered_connection_types() as $authenticator_slug => $classname ) {
			$authenticator_fields = call_user_func( array( $classname, 'get_authenticator_fields' ) );

			foreach ( $authenticator_fields as $field_slug => $field_data ) {
				if ( ! empty( $field_data['readonly'] ) ) {
					continue;
				}

				// Display is handled via JS.
				$field_data['display']  = false;
				$field_data['required'] = false;

				$settings_fields['connections']['fields'][ $field_slug ] = $field_data;
			}
		}

		return $settings_fields;
	}

	/**
	 * Registers all assets the submodule provides.
	 *
	 * @since 1.1.0
	 *
	 * @param Assets $assets The plugin assets instance.
	 */
	public function register_assets( $assets ) {
		if ( self::$script_registered ) {
			return;
		}

		$asset_prefix = str_replace( '_', '-', $assets->get_prefix() );

		$assets->register_script(
			'admin-form-api-actions',
			'assets/dist/js/admin-form-api-actions.js',
			array(
				'deps'      => array( $asset_prefix . 'util', $asset_prefix . 'admin-form-builder', 'jquery', 'plugin-lib-fields' ),
				'in_footer' => true,
			)
		);

		$assets->register_script(
			'admin-settings-api-actions',
			'assets/dist/js/admin-settings-api-actions.js',
			array(
				'deps'      => array( $asset_prefix . 'util', 'jquery', 'plugin-lib-fields' ),
				'in_footer' => true,
			)
		);

		self::$script_registered = true;
	}

	/**
	 * Enqueues scripts and stylesheets on the form editing screen.
	 *
	 * @since 1.1.0
	 *
	 * @param Assets $assets The plugin assets instance.
	 */
	public function enqueue_form_builder_assets( $assets ) {
		if ( self::$script_enqueued ) {
			return;
		}

		$assets->enqueue_script( 'admin-form-api-actions' );

		// Pass all API action slugs to the script.
		$api_actions = array_map(
			function( API_Action $action ) {
				return $action->get_slug();
			},
			array_filter(
				$this->module->get_all(),
				function( Action $action ) {
					return $action instanceof API_Action;
				}
			)
		);
		$data = array(
			'actions' => $api_actions,
		);
		wp_add_inline_script(
			str_replace( '_', '-', $assets->get_prefix() ) . 'admin-form-api-actions',
			'var torroAPIActionsData = ' . wp_json_encode( $data ) . ';',
			'before'
		);

		self::$script_enqueued = true;
	}

	/**
	 * Enqueues scripts and stylesheets on the settings screen.
	 *
	 * @since 1.1.0
	 *
	 * @param Assets $assets            Assets API instance.
	 * @param string $current_tab_id    Identifier of the current tab.
	 * @param string $current_subtab_id Identifier of the current sub-tab.
	 */
	public function enqueue_settings_assets( $assets, $current_tab_id, $current_subtab_id ) {
		if ( "{$assets->get_prefix()}module_actions" !== $current_tab_id ) {
			return;
		}

		if ( $this->slug !== $current_subtab_id ) {
			return;
		}

		if ( self::$script_enqueued ) {
			return;
		}

		$assets->enqueue_script( 'admin-settings-api-actions' );

		$data = array(
			// Pass the current API action slug to the script.
			'action' => $this->slug,
			'i18n'   => array(
				'couldNotLoadData' => __( 'Could not load API action data. Please verify that the REST API is correctly enabled on your site.', 'torro-forms' ),
			),
		);
		wp_add_inline_script(
			str_replace( '_', '-', $assets->get_prefix() ) . 'admin-settings-api-actions',
			'var torroAPIActionsData = ' . wp_json_encode( $data ) . ';',
			'before'
		);

		self::$script_enqueued = true;
	}

	/**
	 * Gets the available connection choices.
	 *
	 * @since 1.1.0
	 *
	 * @return array Connection choices as $value => $label pairs.
	 */
	public function get_available_connection_choices() {
		$connections = $this->get_available_connections();

		return array_map(
			function( $connection ) {
				return $connection->get_title();
			},
			$connections
		);
	}

	/**
	 * Gets the available API connections stored.
	 *
	 * @since 1.1.0
	 *
	 * @return array Array of $connection_slug => $connection pairs.
	 */
	public function get_available_connections() {
		$connections = array();

		$connection_types = static::get_registered_connection_types();

		foreach ( $this->get_option( 'connections', array() ) as $connection ) {
			if ( empty( $connection['structure'] ) ) {
				continue;
			}

			$structure = $this->api_structure( $connection['structure'] );
			if ( ! $structure ) {
				continue;
			}

			$authenticator = $structure->get_authenticator();

			if ( empty( $authenticator ) ) {
				continue;
			}

			if ( empty( $connection_types[ $authenticator ] ) ) {
				continue;
			}

			$connection_class = $connection_types[ $authenticator ];

			$connection = new $connection_class( $this, $connection );

			$connections[ $connection->get_slug() ] = $connection;
		}

		return $connections;
	}

	/**
	 * Gets the available structure choices.
	 *
	 * @since 1.1.0
	 *
	 * @return array Structure choices as $value => $label pairs.
	 */
	public function get_available_structure_choices() {
		$structures = $this->get_available_structures();

		return array_map(
			function( $data ) {
				return $data['title'];
			},
			$structures
		);
	}

	/**
	 * Gets the available route choices for a given structure.
	 *
	 * @since 1.1.0
	 *
	 * @param string $structure_slug Optional. Structure identifier. Default is the first structure.
	 * @return array Route choices as $value => $label pairs.
	 */
	public function get_available_route_choices( $structure_slug = null ) {
		$routes = $this->get_available_routes( $structure_slug );

		return array_map(
			function( $route_data ) {
				return $route_data['title'];
			},
			$routes
		);
	}

	/**
	 * Gets the available API structures with their routes.
	 *
	 * @since 1.1.0
	 *
	 * @return array Associative array of $structure_slug => $data pairs.
	 */
	public function get_available_structures() {
		if ( ! isset( $this->available_structures ) ) {
			$this->available_structures = $this->get_available_structures_and_routes();

			$this->validate_available_structures();
		}

		return $this->available_structures;
	}

	/**
	 * Gets the available API routes for a given structure.
	 *
	 * @since 1.1.0
	 *
	 * @param string $structure_slug Optional. Structure identifier. Default is the first structure.
	 * @return array Associative array of $route_slug => $data pairs.
	 */
	public function get_available_routes( $structure_slug = null ) {
		$structures = $this->get_available_structures();

		if ( null === $structure_slug ) {
			$structure_slug = key( $structures );
		}

		if ( ! isset( $structures[ $structure_slug ] ) ) {
			return array();
		}

		return $structures[ $structure_slug ]['routes'];
	}

	/**
	 * Gets the authentication data definition for a given structure.
	 *
	 * @since 1.1.0
	 *
	 * @param string $structure_slug Optional. Structure identifier. Default is the first structure.
	 * @return array Associative array of $field_slug => $data pairs.
	 */
	public function get_authentication_data( $structure_slug = null ) {
		$structures = $this->get_available_structures();

		if ( null === $structure_slug ) {
			$structure_slug = key( $structures );
		}

		if ( ! isset( $structures[ $structure_slug ] ) ) {
			return array();
		}

		return $structures[ $structure_slug ]['authentication_data'];
	}

	/**
	 * Gets an API structure.
	 *
	 * The API structure is not scoped for the plugin. If you need the configured variant of the API,
	 * use the api() method. If you don't though, this method is more efficient to use then.
	 *
	 * @since 1.1.0
	 *
	 * @param string $structure_slug Optional. Structure identifier. Default is the first structure.
	 * @return Structure The API structure.
	 */
	final public function api_structure( $structure_slug = null ) {
		if ( null === $structure_slug ) {
			$structures     = $this->get_available_structures();
			$structure_slug = key( $structures );
		}

		if ( ! isset( $this->structure_instances[ $structure_slug ] ) ) {
			$this->structure_instances[ $structure_slug ] = apiapi_manager()->structures()->get( $structure_slug );
		}

		return $this->structure_instances[ $structure_slug ];
	}

	/**
	 * Gets an API route for a structure.
	 *
	 * @since 1.1.0
	 *
	 * @param string $route_slug     Optional. Route identifier. Default is the first available route that is part of $structure_slug.
	 * @param string $structure_slug Optional. Structure identifier. Default is the first structure.
	 * @return Route The API route.
	 */
	final public function api_route( $route_slug = null, $structure_slug = null ) {
		$structure = $this->api_structure( $structure_slug );

		if ( null === $route_slug ) {
			$routes     = $this->get_available_routes( $structure->get_name() );
			$route_slug = key( $routes );
		}

		// Strip possibly included request method from route slug.
		$route_slug = preg_replace( '/^(GET|POST|PUT|PATCH|DELETE)\:/', '', $route_slug );

		return $structure->get_route_object( $route_slug );
	}

	/**
	 * Gets a configured API instance for an API structure.
	 *
	 * @since 1.1.0
	 *
	 * @param string $structure_slug Optional. Structure identifier. Default is the first structure.
	 * @return API The configured API instance.
	 */
	final public function api( $structure_slug = null ) {
		if ( null === $structure_slug ) {
			$structures     = $this->get_available_structure_choices();
			$structure_slug = key( $structures );
		}

		if ( ! isset( $this->structure_api_instances[ $structure_slug ] ) ) {
			$this->structure_api_instances[ $structure_slug ] = $this->module->apiapi()->get_api_object( $structure_slug );
		}

		return $this->structure_api_instances[ $structure_slug ];
	}

	/**
	 * Validates the available structures with their routes.
	 *
	 * @since 1.1.0
	 *
	 * @throws Exception Thrown when a validation error occurs.
	 */
	protected function validate_available_structures() {
		if ( empty( $this->available_structures ) || ! is_array( $this->available_structures ) ) {
			/* translators: %s: API action title */
			throw new Exception( sprintf( __( 'No available structures set for API action %s.', 'torro-forms' ), $this->title ) );
		}

		foreach ( $this->available_structures as $structure_slug => $structure_data ) {
			if ( ! is_array( $structure_data ) || empty( $structure_data['title'] ) || empty( $structure_data['routes'] ) ) {
				/* translators: 1: API action title, 2: API structure slug */
				throw new Exception( sprintf( __( 'Invalid or incomplete data set for API action %1$s and structure %2$s.', 'torro-forms' ), $this->title, $structure_slug ) );
			}

			if ( ! isset( $structure_data['authentication_data'] ) ) {
				$structure_data['authentication_data'] = array();
			} elseif ( ! is_array( $structure_data['authentication_data'] ) ) {
				/* translators: 1: API action title, 2: API structure slug */
				throw new Exception( sprintf( __( 'Invalid authentication data set for API action %1$s and structure %2$s.', 'torro-forms' ), $this->title, $structure_slug ) );
			}

			foreach ( $structure_data['authentication_data'] as $field_slug => $field_data ) {
				if ( is_string( $field_data ) || is_callable( $field_data ) ) {
					$field_data = array( 'default' => $field_data );

					$this->available_structures[ $structure_slug ]['authentication_data'][ $field_slug ] = $field_data;
				}

				if ( ! is_array( $field_data ) || ! isset( $field_data['value'] ) && ! isset( $field_data['default'] ) ) {
					/* translators: 1: API action title, 2: API structure slug, 3: API route slug */
					throw new Exception( sprintf( __( 'Invalid or incomplete data set for API action %1$s, structure %2$s and authentication data field %3$s.', 'torro-forms' ), $this->title, $structure_slug, $field_slug ) );
				}
			}

			foreach ( $structure_data['routes'] as $route_slug => $route_data ) {
				if ( is_string( $route_data ) ) {
					$route_data = array( 'title' => $route_data );

					$this->available_structures[ $structure_slug ]['routes'][ $route_slug ] = $route_data;
				}

				if ( ! is_array( $route_data ) || empty( $route_data['title'] ) || isset( $route_data['fields'] ) && ! is_array( $route_data['fields'] ) ) {
					/* translators: 1: API action title, 2: API structure slug, 3: API route slug */
					throw new Exception( sprintf( __( 'Invalid or incomplete data set for API action %1$s, structure %2$s and route %3$s.', 'torro-forms' ), $this->title, $structure_slug, $route_slug ) );
				}

				if ( empty( $route_data['fields'] ) ) {
					$route_data['fields'] = array();

					$this->available_structures[ $structure_slug ]['routes'][ $route_slug ]['fields'] = $route_data['fields'];
				}

				foreach ( $route_data['fields'] as $field_slug => $field_data ) {
					if ( ! is_array( $field_data ) || ! isset( $field_data['value'] ) && ! isset( $field_data['default'] ) ) {
						/* translators: 1: API action title, 2: API structure slug, 3: API route slug, 4: API route field slug */
						throw new Exception( sprintf( __( 'Invalid or incomplete data set for API action %1$s, structure %2$s, route %3$s and field %4$s.', 'torro-forms' ), $this->title, $structure_slug, $route_slug, $field_slug ) );
					}
				}
			}
		}
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
	abstract protected function get_available_structures_and_routes();

	/**
	 * Registers the template tag handler for email notifications.
	 *
	 * @since 1.1.0
	 */
	protected function register_template_tag_handlers() {
		if ( self::$template_tag_handlers_registered ) {
			return;
		}

		$prefix = $this->module->get_prefix();

		$tags = array(
			'sitetitle'          => array(
				'group'       => 'global',
				'label'       => __( 'Site Title', 'torro-forms' ),
				'description' => __( 'Inserts the site title.', 'torro-forms' ),
				'callback'    => function() {
					return get_bloginfo( 'name' );
				},
			),
			'sitetagline'        => array(
				'group'       => 'global',
				'label'       => __( 'Site Tagline', 'torro-forms' ),
				'description' => __( 'Inserts the site tagline.', 'torro-forms' ),
				'callback'    => function() {
					return get_bloginfo( 'description' );
				},
			),
			'siteurl'            => array(
				'group'       => 'global',
				'label'       => __( 'Site URL', 'torro-forms' ),
				'description' => __( 'Inserts the site home URL.', 'torro-forms' ),
				'callback'    => function() {
					return home_url( '/' );
				},
			),
			'adminemail'         => array(
				'group'       => 'global',
				'label'       => __( 'Site Admin Email', 'torro-forms' ),
				'description' => __( 'Inserts the site admin email.', 'torro-forms' ),
				'callback'    => function() {
					return get_option( 'admin_email' );
				},
			),
			'userip'             => array(
				'group'       => 'global',
				'label'       => __( 'User IP', 'torro-forms' ),
				'description' => __( 'Inserts the current user IP address.', 'torro-forms' ),
				'callback'    => function() {
					$validated_ip = filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP );
					if ( empty( $validated_ip ) ) {
						return '0.0.0.0';
					}
					return $validated_ip;
				},
			),
			'refererurl'         => array(
				'group'       => 'global',
				'label'       => __( 'Referer URL', 'torro-forms' ),
				'description' => __( 'Inserts the current referer URL.', 'torro-forms' ),
				'callback'    => function() {
					return wp_get_referer();
				},
			),
			'formtitle'          => array(
				'group'       => 'form',
				'label'       => __( 'Form Title', 'torro-forms' ),
				'description' => __( 'Inserts the form title.', 'torro-forms' ),
				'callback'    => function( $form ) {
					return $form->title;
				},
			),
			'formurl'            => array(
				'group'       => 'form',
				'label'       => __( 'Form URL', 'torro-forms' ),
				'description' => __( 'Inserts the URL to the form.', 'torro-forms' ),
				'callback'    => function( $form ) {
					return get_permalink( $form->id );
				},
			),
			'formediturl'        => array(
				'group'       => 'form',
				'label'       => __( 'Form Edit URL', 'torro-forms' ),
				'description' => __( 'Inserts the edit URL for the form.', 'torro-forms' ),
				'callback'    => function( $form ) {
					return get_edit_post_link( $form->id );
				},
			),
			'submissionurl'      => array(
				'group'       => 'submission',
				'label'       => __( 'Submission URL', 'torro-forms' ),
				'description' => __( 'Inserts the URL to the submission.', 'torro-forms' ),
				'callback'    => function( $form, $submission ) {
					return add_query_arg( 'torro_submission_id', $submission->id, get_permalink( $form->id ) );
				},
			),
			'submissionediturl'  => array(
				'group'       => 'submission',
				'label'       => __( 'Submission Edit URL', 'torro-forms' ),
				'description' => __( 'Inserts the edit URL for the submission.', 'torro-forms' ),
				'callback'    => function( $form, $submission ) {
					return add_query_arg(
						array(
							'post_type' => torro()->post_types()->get_prefix() . 'form',
							'page'      => torro()->admin_pages()->get_prefix() . 'edit_submission',
							'id'        => $submission->id,
						),
						admin_url( 'edit.php' )
					);
				},
			),
			'submissiondatetime' => array(
				'group'       => 'submission',
				'label'       => __( 'Submission Date and Time', 'torro-forms' ),
				'description' => __( 'Inserts the submission date and time.', 'torro-forms' ),
				'callback'    => function( $form, $submission ) {
					$date = $submission->format_datetime( get_option( 'date_format' ), false );
					$time = $submission->format_datetime( get_option( 'time_format' ), false );

					/* translators: 1: formatted date, 2: formatted time */
					return sprintf( _x( '%1$s at %2$s', 'concatenating date and time', 'torro-forms' ), $date, $time );
				},
			),
		);

		$groups = array(
			'global'     => _x( 'Global', 'template tag group', 'torro-forms' ),
			'form'       => _x( 'Form', 'template tag group', 'torro-forms' ),
			'submission' => _x( 'Submission', 'template tag group', 'torro-forms' ),
		);

		/**
		 * Filters API action template tags.
		 *
		 * An array will be returned with all template tags.
		 *
		 * @since 1.1.0
		 *
		 * @param array $tags All API action template tags in an array.
		 */
		$tags = apply_filters( "{$prefix}api_actions_template_tags", $tags );

		/**
		 * Filters API action template tag groups.
		 *
		 * An array will be returned with all template tag groups.
		 *
		 * @since 1.1.0
		 *
		 * @param array $tags All API action template tag groups.
		 */
		$groups = apply_filters( "{$prefix}api_actions_template_tags_groups", $groups );

		self::$template_tag_handler = new Template_Tag_Handler( 'api_actions', $tags, array( Form::class, Submission::class ), $groups );

		$this->module->manager()->template_tag_handlers()->register( self::$template_tag_handler );

		self::$template_tag_handlers_registered = true;
	}

	/**
	 * Gets all the dynamic template tags for a form, consisting of the form's element value tags.
	 *
	 * @since 1.1.0
	 *
	 * @param Form $form Form for which to get the dynamic template tags.
	 * @return array Dynamic tags as `$slug => $data` pairs.
	 */
	protected function get_dynamic_template_tags( $form ) {
		$tags = array();

		foreach ( $form->get_elements() as $element ) {
			$element_type = $element->get_element_type();
			if ( ! $element_type ) {
				continue;
			}

			$tags[ 'value_element_' . $element->id ] = array(
				'group'       => 'submission',
				/* translators: %s: element label */
				'label'       => sprintf( __( 'Value for &#8220;%s&#8221;', 'torro-forms' ), $element->label ),
				/* translators: %s: element label */
				'description' => sprintf( __( 'Inserts the submission value for the element &#8220;%s&#8221;.', 'torro-forms' ), $element->label ),
				'callback'    => function( $form, $submission ) use ( $element, $element_type ) {
					$element_values = $submission->get_element_values_data();
					if ( ! isset( $element_values[ $element->id ] ) ) {
						return '';
					}

					add_filter( "{$this->module->manager()->get_prefix()}use_single_export_column_for_choices", '__return_true' );
					$export_values = $element_type->format_values_for_export( $element_values[ $element->id ], $element, 'html' );
					remove_filter( "{$this->module->manager()->get_prefix()}use_single_export_column_for_choices", '__return_true' );

					if ( ! isset( $export_values[ 'element_' . $element->id . '__main' ] ) ) {
						if ( count( $export_values ) !== 1 ) {
							return '';
						}

						return array_pop( $export_values );
					}

					return $export_values[ 'element_' . $element->id . '__main' ];
				},
			);
		}

		return $tags;
	}

	/**
	 * Gets the registered connection types and their classes.
	 *
	 * Connection types should match API-API authenticator slugs.
	 *
	 * @since 1.1.0
	 * @static
	 *
	 * @return array Associative array of $connection_slug => $classname pairs.
	 */
	final public static function get_registered_connection_types() {
		return array(
			OAuth2_Connection::TYPE    => OAuth2_Connection::class,
			OAuth1_Connection::TYPE    => OAuth1_Connection::class,
			X_Account_Connection::TYPE => X_Account_Connection::class,
			X_Connection::TYPE         => X_Connection::class,
			Basic_Connection::TYPE     => Basic_Connection::class,
			Key_Connection::TYPE       => Key_Connection::class,
		);
	}
}
