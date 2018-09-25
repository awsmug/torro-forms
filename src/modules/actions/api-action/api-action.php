<?php
/**
 * Action base class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Actions\API_Action;

use awsmug\Torro_Forms\Modules\Assets_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Settings_Assets_Submodule_Interface;
use awsmug\Torro_Forms\Assets;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use awsmug\Torro_Forms\Modules\Actions\Action;
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

				if ( isset( $settings_fields['connections']['fields'][ $field_slug ] ) ) {
					$settings_fields['connections']['fields'][ $field_slug ]['data-authenticator'] .= ',' . $authenticator_slug;
					continue;
				}

				// Display is handled via JS.
				$field_data['display']            = false;
				$field_data['required']           = false;
				$field_data['data-authenticator'] = $authenticator_slug;

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

	}

	/**
	 * Enqueues scripts and stylesheets on the form editing screen.
	 *
	 * @since 1.1.0
	 *
	 * @param Assets $assets The plugin assets instance.
	 */
	public function enqueue_form_builder_assets( $assets ) {

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
				return $connection->get( 'title' );
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

			$connection = new $connection_class( $connection );

			$connections[ $connection->get( 'slug' ) ] = $connection;
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
					if ( ! is_array( $field_data ) || ! isset( $field_data['value'] ) && ! isset( $field_data['value_callback'] ) && ! isset( $field_data['default'] ) ) {
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
	 *               'title' and 'routes'. 'routes' must be an associative array of $route_slug => $route_data pairs.
	 *               $route_data must be an associative array with keys 'title' and 'fields'. 'fields' must be an
	 *               associative array of $field_slug => $field_data pairs where details are specified for each route
	 *               field that requires special handling. Possible keys are 'value', 'value_callback', and 'default'.
	 */
	abstract protected function get_available_structures_and_routes();

	/**
	 * Gets the registered connection types and their classes.
	 *
	 * @since 1.1.0
	 * @static
	 *
	 * @return array Associative array of $connection_slug => $classname pairs.
	 */
	final public static function get_registered_connection_types() {
		return array(
			'oauth2'    => OAuth2_Connection::class,
			'oauth1'    => OAuth1_Connection::class,
			'x-account' => X_Account_Connection::class,
			'x'         => X_Connection::class,
			'basic'     => Basic_Connection::class,
			'key'       => Key_Connection::class,
		);
	}
}
