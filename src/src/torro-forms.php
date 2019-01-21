<?php
/**
 * Plugin main class
 *
 * @package TorroForms
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main class for Torro Forms.
 *
 * Takes care of initializing the plugin.
 *
 * This file must always be parseable by PHP 5.2.
 *
 * @since 1.0.0
 *
 * @method awsmug\Torro_Forms\DB_Objects\Forms\Form_Manager                         forms()
 * @method awsmug\Torro_Forms\DB_Objects\Form_Categories\Form_Category_Manager      form_categories()
 * @method awsmug\Torro_Forms\DB_Objects\Containers\Container_Manager               containers()
 * @method awsmug\Torro_Forms\DB_Objects\Elements\Element_Manager                   elements()
 * @method awsmug\Torro_Forms\DB_Objects\Element_Choices\Element_Choice_Manager     element_choices()
 * @method awsmug\Torro_Forms\DB_Objects\Element_Settings\Element_Setting_Manager   element_settings()
 * @method awsmug\Torro_Forms\DB_Objects\Submissions\Submission_Manager             submissions()
 * @method awsmug\Torro_Forms\DB_Objects\Submission_Values\Submission_Value_Manager submission_values()
 * @method awsmug\Torro_Forms\DB_Objects\Post_Type_Manager                          post_types()
 * @method awsmug\Torro_Forms\DB_Objects\Taxonomy_Manager                           taxonomies()
 * @method awsmug\Torro_Forms\Components\Form_Upload_Manager                        form_uploads()
 * @method awsmug\Torro_Forms\Components\Template_Tag_Handler_Manager               template_tag_handlers()
 * @method Leaves_And_Love\Plugin_Lib\Components\Admin_Pages                        admin_pages()
 * @method awsmug\Torro_Forms\Components\Extensions                                 extensions()
 * @method awsmug\Torro_Forms\Modules\Module_Manager                                modules()
 * @method Leaves_And_Love\Plugin_Lib\Options                                       options()
 * @method Leaves_And_Love\Plugin_Lib\Cache                                         cache()
 * @method awsmug\Torro_Forms\DB                                                    db()
 * @method Leaves_And_Love\Plugin_Lib\Meta                                          meta()
 * @method awsmug\Torro_Forms\Assets                                                assets()
 * @method Leaves_And_Love\Plugin_Lib\Template                                      template()
 * @method Leaves_And_Love\Plugin_Lib\AJAX                                          ajax()
 * @method awsmug\Torro_Forms\Error_Handler                                         error_handler()
 */
class Torro_Forms extends Leaves_And_Love_Plugin {

	/**
	 * The forms manager instance.
	 *
	 * @since 1.0.0
	 * @var awsmug\Torro_Forms\DB_Objects\Forms\Form_Manager
	 */
	protected $forms;

	/**
	 * The form categories manager instance.
	 *
	 * @since 1.0.0
	 * @var awsmug\Torro_Forms\DB_Objects\Form_Categories\Form_Category_Manager
	 */
	protected $form_categories;

	/**
	 * The containers manager instance.
	 *
	 * @since 1.0.0
	 * @var awsmug\Torro_Forms\DB_Objects\Containers\Container_Manager
	 */
	protected $containers;

	/**
	 * The elements manager instance.
	 *
	 * @since 1.0.0
	 * @var awsmug\Torro_Forms\DB_Objects\Elements\Element_Manager
	 */
	protected $elements;

	/**
	 * The element choices manager instance.
	 *
	 * @since 1.0.0
	 * @var awsmug\Torro_Forms\DB_Objects\Element_Choices\Element_Choice_Manager
	 */
	protected $element_choices;

	/**
	 * The element settings manager instance.
	 *
	 * @since 1.0.0
	 * @var awsmug\Torro_Forms\DB_Objects\Element_Settings\Element_Setting_Manager
	 */
	protected $element_settings;

	/**
	 * The submissions manager instance.
	 *
	 * @since 1.0.0
	 * @var awsmug\Torro_Forms\DB_Objects\Submissions\Submission_Manager
	 */
	protected $submissions;

	/**
	 * The submission values manager instance.
	 *
	 * @since 1.0.0
	 * @var awsmug\Torro_Forms\DB_Objects\Submission_Values\Submission_Value_Manager
	 */
	protected $submission_values;

	/**
	 * The post types API instance.
	 *
	 * @since 1.0.0
	 * @var awsmug\Torro_Forms\DB_Objects\Post_Type_Manager
	 */
	protected $post_types;

	/**
	 * The taxonomies API instance.
	 *
	 * @since 1.0.0
	 * @var awsmug\Torro_Forms\DB_Objects\Taxonomy_Manager
	 */
	protected $taxonomies;

	/**
	 * The form uploads instance.
	 *
	 * @since 1.0.0
	 * @var awsmug\Torro_Forms\Components\Form_Upload_Manager
	 */
	protected $form_uploads;

	/**
	 * The template tag handlers instance.
	 *
	 * @since 1.0.0
	 * @var awsmug\Torro_Forms\Components\Template_Tag_Handler_Manager
	 */
	protected $template_tag_handlers;

	/**
	 * The Admin Pages instance.
	 *
	 * @since 1.0.0
	 * @var Leaves_And_Love\Plugin_Lib\Components\Admin_Pages
	 */
	protected $admin_pages;

	/**
	 * The Extensions instance.
	 *
	 * @since 1.0.0
	 * @var awsmug\Torro_Forms\Components\Extensions
	 */
	protected $extensions;

	/**
	 * The module manager instance.
	 *
	 * @since 1.0.0
	 * @var awsmug\Torro_Forms\Modules\Module_Manager
	 */
	protected $modules;

	/**
	 * The Option API instance.
	 *
	 * @since 1.0.0
	 * @var Leaves_And_Love\Plugin_Lib\Options
	 */
	protected $options;

	/**
	 * The cache instance.
	 *
	 * @since 1.0.0
	 * @var Leaves_And_Love\Plugin_Lib\Cache
	 */
	protected $cache;

	/**
	 * The database instance.
	 *
	 * @since 1.0.0
	 * @var awsmug\Torro_Forms\DB
	 */
	protected $db;

	/**
	 * The Metadata API instance.
	 *
	 * @since 1.0.0
	 * @var Leaves_And_Love\Plugin_Lib\Meta
	 */
	protected $meta;

	/**
	 * The Assets manager instance.
	 *
	 * @since 1.0.0
	 * @var awsmug\Torro_Forms\Assets
	 */
	protected $assets;

	/**
	 * The Template instance.
	 *
	 * @since 1.0.0
	 * @var Leaves_And_Love\Plugin_Lib\Template
	 */
	protected $template;

	/**
	 * The AJAX handler instance.
	 *
	 * @since 1.0.0
	 * @var Leaves_And_Love\Plugin_Lib\AJAX
	 */
	protected $ajax;

	/**
	 * The error handler instance.
	 *
	 * @since 1.0.0
	 * @var awsmug\Torro_Forms\Error_Handler
	 */
	protected $error_handler;

	/**
	 * The plugin's API-API instance.
	 *
	 * @since 1.0.0
	 * @var APIAPI\Core\APIAPI
	 */
	protected $apiapi;

	/**
	 * The plugin's logger instance.
	 *
	 * @since 1.0.0
	 * @var Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * The plugin's API-API config.
	 *
	 * @since 1.0.0
	 * @var awsmug\Torro_Forms\APIAPI_Config
	 */
	protected $apiapi_config;

	/**
	 * Deactivates the plugin.
	 *
	 * Clears the cron task scheduled.
	 *
	 * @since 1.0.0
	 * @static
	 *
	 * @param bool $network_wide Optional. Whether the plugin is being deactivated for the whole network.
	 *                           Default false.
	 *
	 * @codeCoverageIgnore
	 */
	public static function deactivate( $network_wide = false ) {
		if ( $network_wide ) {
			$sites = get_sites(
				array(
					'network_id'    => get_current_network_id(),
					'no_found_rows' => true,
				)
			);
			foreach ( $sites as $site ) {
				switch_to_blog( $site->id );
				torro()->submissions()->clear_cron_task();
				restore_current_blog();
			}
		} else {
			torro()->submissions()->clear_cron_task();
		}
	}

	/**
	 * Uninstalls the plugin.
	 *
	 * Drops all database tables and related content.
	 *
	 * @since 1.0.0
	 * @static
	 *
	 * @codeCoverageIgnore
	 */
	public static function uninstall() {
		torro()->db()->uninstall();
	}

	/**
	 * Returns the current version of Torro Forms.
	 *
	 * @since 1.0.0
	 *
	 * @return string Version number.
	 */
	public function version() {
		return $this->version;
	}

	/**
	 * Returns the deactivation callback.
	 *
	 * @since 1.0.0
	 *
	 * @return callable Deactivation callback.
	 */
	public function get_deactivation_hook() {
		return array( __CLASS__, 'deactivate' );
	}

	/**
	 * Returns the uninstall callback.
	 *
	 * @since 1.0.0
	 *
	 * @return callable Uninstall callback.
	 */
	public function get_uninstall_hook() {
		return array( __CLASS__, 'uninstall' );
	}

	/**
	 * Returns the plugin's API-API instance.
	 *
	 * @since 1.0.0
	 *
	 * @return APIAPI\Core\APIAPI The API-API instance.
	 */
	public function apiapi() {
		if ( ! $this->apiapi ) {
			$this->apiapi = apiapi( $this->prefix . 'forms', $this->apiapi_config );
		}

		return $this->apiapi;
	}

	/**
	 * Returns the plugin's logger instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Psr\Log\LoggerInterface The logger instance.
	 */
	public function logger() {
		if ( ! $this->logger ) {

			/**
			 * Filters initializing the plugin's logger instance.
			 *
			 * An implementation of Psr\Log\LoggerInterface may be returned to use
			 * that instead of the regular logger, which simply uses the typical
			 * PHP error handler controlled by WordPress.
			 *
			 * @since 1.0.0
			 *
			 * @param Psr\Log\LoggerInterface|null Logger instance to use, or null to not override (default).
			 */
			$logger = apply_filters( "{$this->prefix}set_logger", null );
			if ( $logger && is_a( $logger, 'Psr\Log\LoggerInterface' ) ) {
				$this->logger = $logger;
			} else {
				$this->logger = $this->instantiate_plugin_class( 'Logger' );
			}
		}

		return $this->logger;
	}

	/**
	 * Loads the base properties of the class.
	 *
	 * @since 1.0.0
	 */
	protected function load_base_properties() {
		$this->version      = '1.0.4';
		$this->prefix       = 'torro_';
		$this->vendor_name  = 'awsmug';
		$this->project_name = 'Torro_Forms';
		$this->minimum_php  = '5.6';
		$this->minimum_wp   = '4.8';
	}

	/**
	 * Loads the plugin's textdomain.
	 *
	 * @since 1.0.0
	 */
	protected function load_textdomain() {
		/** This filter is documented in wp-includes/l10n.php */
		$locale = apply_filters( 'plugin_locale', get_locale(), 'torro-forms' );

		$mofile = WP_LANG_DIR . '/plugins/torro-forms/torro-forms-' . $locale . '.mo';
		if ( file_exists( $mofile ) ) {
			return load_textdomain( 'torro-forms', $mofile );
		}

		$this->load_plugin_textdomain( 'torro-forms' );
	}

	/**
	 * Loads the class messages.
	 *
	 * @since 1.0.0
	 */
	protected function load_messages() {
		$this->messages['cheatin_huh'] = __( 'Cheatin&#8217; huh?', 'torro-forms' );

		/* translators: %s: PHP version number */
		$this->messages['outdated_php'] = __( 'Torro Forms cannot be initialized because your setup uses a PHP version older than %s.', 'torro-forms' );

		/* translators: %s: WordPress version number */
		$this->messages['outdated_wp'] = __( 'Torro Forms cannot be initialized because your setup uses a WordPress version older than %s.', 'torro-forms' );
	}

	/**
	 * Checks whether the dependencies have been loaded.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the dependencies are loaded, false otherwise.
	 */
	protected function dependencies_loaded() {
		if ( ! class_exists( 'PhpOffice\PhpSpreadsheet\Spreadsheet' ) ) {
			return false;
		}

		if ( ! interface_exists( 'Psr\Log\LoggerInterface' ) ) {
			return false;
		}

		if ( ! function_exists( 'apiapi_manager' ) ) {
			return false;
		}

		if ( ! apiapi_manager()->transporters()->is_registered( 'wordpress' ) ) { // WPCS: spelling ok.
			return false;
		}

		if ( ! apiapi_manager()->storages()->is_registered( 'wordpress-option' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Instantiates the plugin services.
	 *
	 * @since 1.0.0
	 */
	protected function instantiate_services() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command(
				substr( $this->prefix, 0, -1 ),
				$this->instantiate_library_class( 'CLI_Command_Aggregate' ),
				array(
					'shortdesc' => 'Manage Torro Forms content.',
				)
			);
		}

		$this->instantiate_core_services();
		$this->instantiate_db_object_managers();
		$this->instantiate_modules();
		$this->instantiate_component_services();

		$this->setup_capabilities();
		$this->connect_db_object_managers();
		$this->setup_admin_pages();
	}

	/**
	 * Instantiates the plugin core services.
	 *
	 * @since 1.0.0
	 */
	protected function instantiate_core_services() {
		call_user_func( array( 'Leaves_And_Love\Plugin_Lib\Fields\Field_Manager', 'set_translations' ), $this->instantiate_plugin_class( 'Translations\Translations_Field_Manager' ) );

		$this->error_handler = $this->instantiate_plugin_service( 'Error_Handler', $this->prefix, $this->instantiate_plugin_class( 'Translations\Translations_Error_Handler' ) );

		$this->options = $this->instantiate_library_service( 'Options', $this->prefix );

		$this->cache = $this->instantiate_library_service( 'Cache', $this->prefix );

		$this->db = $this->instantiate_plugin_service(
			'DB',
			$this->prefix,
			array(
				'options'       => $this->options,
				'error_handler' => $this->error_handler,
			),
			$this->instantiate_plugin_class( 'Translations\Translations_DB' )
		);

		$this->meta = $this->instantiate_library_service(
			'Meta',
			$this->prefix,
			array(
				'db'            => $this->db,
				'error_handler' => $this->error_handler,
			)
		);

		$this->assets = $this->instantiate_plugin_service(
			'Assets',
			$this->prefix,
			array(
				'path_callback'  => array( $this, 'path' ),
				'url_callback'   => array( $this, 'url' ),
				'plugin_version' => $this->version,
			)
		);

		$this->template = $this->instantiate_library_service(
			'Template',
			$this->prefix,
			array(
				'default_location' => $this->path( 'templates/' ),
			)
		);

		$this->ajax = $this->instantiate_library_service( 'AJAX', $this->prefix, $this->instantiate_plugin_class( 'Translations\Translations_AJAX' ) );

		$this->apiapi_config = $this->instantiate_plugin_class( 'APIAPI_Config', $this->prefix );
	}

	/**
	 * Instantiates the plugin DB object managers.
	 *
	 * @since 1.0.0
	 */
	protected function instantiate_db_object_managers() {
		$this->forms = $this->instantiate_plugin_service(
			'DB_Objects\Forms\Form_Manager',
			$this->prefix,
			array(
				'capabilities'  => $this->instantiate_plugin_service( 'DB_Objects\Forms\Form_Capabilities', $this->prefix ),
				'template'      => $this->template,
				'options'       => $this->options,
				'assets'        => $this->assets,
				'ajax'          => $this->ajax,
				'db'            => $this->db,
				'cache'         => $this->cache,
				'meta'          => $this->meta,
				'error_handler' => $this->error_handler,
			),
			$this->instantiate_plugin_class( 'Translations\Translations_Form_Manager' )
		);

		$this->form_categories = $this->instantiate_plugin_service(
			'DB_Objects\Form_Categories\Form_Category_Manager',
			$this->prefix,
			array(
				'capabilities'  => $this->instantiate_plugin_service( 'DB_Objects\Form_Categories\Form_Category_Capabilities', $this->prefix ),
				'db'            => $this->db,
				'cache'         => $this->cache,
				'meta'          => $this->meta,
				'error_handler' => $this->error_handler,
			),
			$this->instantiate_plugin_class( 'Translations\Translations_Form_Category_Manager' )
		);

		$this->containers = $this->instantiate_plugin_service(
			'DB_Objects\Containers\Container_Manager',
			$this->prefix,
			array(
				'capabilities'  => $this->instantiate_plugin_service( 'DB_Objects\Containers\Container_Capabilities', $this->prefix ),
				'db'            => $this->db,
				'cache'         => $this->cache,
				'error_handler' => $this->error_handler,
			),
			$this->instantiate_plugin_class( 'Translations\Translations_Container_Manager' )
		);

		$this->elements = $this->instantiate_plugin_service(
			'DB_Objects\Elements\Element_Manager',
			$this->prefix,
			array(
				'capabilities'  => $this->instantiate_plugin_service( 'DB_Objects\Elements\Element_Capabilities', $this->prefix ),
				'db'            => $this->db,
				'assets'        => $this->assets,
				'ajax'          => $this->ajax,
				'cache'         => $this->cache,
				'error_handler' => $this->error_handler,
			),
			$this->instantiate_plugin_class( 'Translations\Translations_Element_Manager' )
		);

		$this->element_choices = $this->instantiate_plugin_service(
			'DB_Objects\Element_Choices\Element_Choice_Manager',
			$this->prefix,
			array(
				'capabilities'  => $this->instantiate_plugin_service( 'DB_Objects\Element_Choices\Element_Choice_Capabilities', $this->prefix ),
				'db'            => $this->db,
				'cache'         => $this->cache,
				'error_handler' => $this->error_handler,
			),
			$this->instantiate_plugin_class( 'Translations\Translations_Element_Choice_Manager' )
		);

		$this->element_settings = $this->instantiate_plugin_service(
			'DB_Objects\Element_Settings\Element_Setting_Manager',
			$this->prefix,
			array(
				'capabilities'  => $this->instantiate_plugin_service( 'DB_Objects\Element_Settings\Element_Setting_Capabilities', $this->prefix ),
				'db'            => $this->db,
				'cache'         => $this->cache,
				'error_handler' => $this->error_handler,
			),
			$this->instantiate_plugin_class( 'Translations\Translations_Element_Setting_Manager' )
		);

		$this->submissions = $this->instantiate_plugin_service(
			'DB_Objects\Submissions\Submission_Manager',
			$this->prefix,
			array(
				'capabilities'  => $this->instantiate_plugin_service( 'DB_Objects\Submissions\Submission_Capabilities', $this->prefix ),
				'db'            => $this->db,
				'cache'         => $this->cache,
				'meta'          => $this->meta,
				'error_handler' => $this->error_handler,
			),
			$this->instantiate_plugin_class( 'Translations\Translations_Submission_Manager' )
		);

		$this->submission_values = $this->instantiate_plugin_service(
			'DB_Objects\Submission_Values\Submission_Value_Manager',
			$this->prefix,
			array(
				'capabilities'  => $this->instantiate_plugin_service( 'DB_Objects\Submission_Values\Submission_Value_Capabilities', $this->prefix ),
				'db'            => $this->db,
				'cache'         => $this->cache,
				'error_handler' => $this->error_handler,
			),
			$this->instantiate_plugin_class( 'Translations\Translations_Submission_Value_Manager' )
		);

		$this->db->set_version( 20180125 );
	}

	/**
	 * Instantiates the module manager.
	 *
	 * @since 1.0.0
	 */
	protected function instantiate_modules() {
		$this->modules = $this->instantiate_plugin_service(
			'Modules\Module_Manager',
			$this->prefix,
			array(
				'options'               => $this->options,
				'meta'                  => $this->meta,
				'assets'                => $this->assets,
				'ajax'                  => $this->ajax,
				'forms'                 => $this->forms,
				'template_tag_handlers' => $this->template_tag_handlers,
				'error_handler'         => $this->error_handler,
			)
		);
	}

	/**
	 * Instantiates the plugin component services.
	 *
	 * @since 1.0.0
	 */
	protected function instantiate_component_services() {
		$this->admin_pages = $this->instantiate_library_service(
			'Components\Admin_Pages',
			$this->prefix,
			array(
				'ajax'          => $this->ajax,
				'assets'        => $this->assets,
				'error_handler' => $this->error_handler,
			)
		);

		$this->extensions = $this->instantiate_plugin_service( 'Components\Extensions', $this->prefix, $this->instantiate_plugin_class( 'Translations\Translations_Extensions' ) );
		$this->extensions->set_plugin( $this );

		$this->template_tag_handlers = $this->instantiate_plugin_service( 'Components\Template_Tag_Handler_Manager', $this->prefix );

		$this->post_types = $this->instantiate_plugin_service(
			'DB_Objects\Post_Type_Manager',
			$this->prefix,
			array(
				'options'       => $this->options,
				'error_handler' => $this->error_handler,
			)
		);

		$this->taxonomies = $this->instantiate_plugin_service(
			'DB_Objects\Taxonomy_Manager',
			$this->prefix,
			array(
				'options'       => $this->options,
				'error_handler' => $this->error_handler,
			)
		);

		$this->form_uploads = $this->instantiate_plugin_service(
			'Components\Form_Upload_Manager',
			$this->prefix,
			array(
				'taxonomies'    => $this->taxonomies,
				'error_handler' => $this->error_handler,
			)
		);
	}

	/**
	 * Sets up capabilities for the plugin DB object managers.
	 *
	 * @since 1.0.0
	 */
	protected function setup_capabilities() {
		// Map form and its component capabilities to post capabilities.
		$this->forms->capabilities()->map_capabilities( 'posts' );
		$this->containers->capabilities()->map_capabilities( 'posts' );
		$this->elements->capabilities()->map_capabilities( 'posts' );
		$this->element_choices->capabilities()->map_capabilities( 'posts' );
		$this->element_settings->capabilities()->map_capabilities( 'posts' );
		$this->submissions->capabilities()->map_capabilities( 'posts' );
		$this->submission_values->capabilities()->map_capabilities( 'posts' );

		// Map form category capabilities to category capabilities.
		$this->form_categories->capabilities()->map_capabilities( 'categories' );

		// Grant access to plugin settings if the user can manage options.
		$this->forms->capabilities()->grant_capabilities(
			array(
				'manage_item_settings' => 'manage_options',
			)
		);
	}

	/**
	 * Connects the plugin DB object managers through hierarchical relationships.
	 *
	 * @since 1.0.0
	 */
	protected function connect_db_object_managers() {
		$this->forms->add_child_manager( 'form_categories', $this->form_categories );
		$this->forms->add_child_manager( 'containers', $this->containers );
		$this->forms->add_child_manager( 'submissions', $this->submissions );

		$this->form_categories->add_parent_manager( 'forms', $this->forms );

		$this->containers->add_parent_manager( 'forms', $this->forms );
		$this->containers->add_child_manager( 'elements', $this->elements );

		$this->elements->add_parent_manager( 'containers', $this->containers );
		$this->elements->add_child_manager( 'element_choices', $this->element_choices );
		$this->elements->add_child_manager( 'element_settings', $this->element_settings );

		$this->element_choices->add_parent_manager( 'elements', $this->elements );

		$this->element_settings->add_parent_manager( 'elements', $this->elements );

		$this->submissions->add_parent_manager( 'forms', $this->forms );
		$this->submissions->add_child_manager( 'submission_values', $this->submission_values );

		$this->submission_values->add_parent_manager( 'submissions', $this->submissions );
	}

	/**
	 * Sets up the admin pages.
	 *
	 * @since 1.0.0
	 */
	protected function setup_admin_pages() {
		if ( ! is_admin() ) {
			return;
		}

		$submissions_list_class_name = 'awsmug\Torro_Forms\DB_Objects\Submissions\Submissions_List_Page';
		$submission_edit_class_name  = 'awsmug\Torro_Forms\DB_Objects\Submissions\Submission_Edit_Page';

		$submissions_list_page = new $submissions_list_class_name( $this->admin_pages->get_prefix() . 'list_submissions', $this->admin_pages, $this->submissions );
		$submission_edit_page  = new $submission_edit_class_name(
			$this->admin_pages->get_prefix() . 'edit_submission',
			$this->admin_pages,
			$this->submissions,
			array(
				'field_required_markup' => '<span class="screen-reader-text">' . _x( '(required)', 'field required indicator', 'torro-forms' ) . '</span><span class="torro-required-indicator" aria-hidden="true">*</span>',
			)
		);

		$this->admin_pages->add( 'list_submissions', $submissions_list_page, 'edit.php?post_type=' . $this->admin_pages->get_prefix() . 'form', null, 'site' );
		$this->admin_pages->add( 'edit_submission', $submission_edit_page, null, null, 'site', true );

		$form_settings_class_name = 'awsmug\Torro_Forms\DB_Objects\Forms\Form_Settings_Page';
		$form_settings_page       = new $form_settings_class_name( $this->admin_pages->get_prefix() . 'form_settings', $this->admin_pages, $this->forms );

		$this->admin_pages->add( 'form_settings', $form_settings_page, 'edit.php?post_type=torro_form', null, 'site' );
	}

	/**
	 * Adds the necessary plugin hooks.
	 *
	 * @since 1.0.0
	 */
	protected function add_hooks() {
		$this->add_core_service_hooks();
		$this->add_db_object_manager_hooks();
		$this->add_module_hooks();
		$this->add_component_service_hooks();
	}

	/**
	 * Adds the necessary plugin core service hooks.
	 *
	 * @since 1.0.0
	 */
	protected function add_core_service_hooks() {
		$this->options->add_hooks();
		$this->db->add_hooks();
		$this->assets->add_hooks();
		$this->ajax->add_hooks();
		$this->apiapi_config->add_hooks();
	}

	/**
	 * Adds the necessary plugin DB object manager hooks.
	 *
	 * @since 1.0.0
	 */
	protected function add_db_object_manager_hooks() {
		$this->forms->add_hooks();
		$this->form_categories->add_hooks();
		$this->containers->add_hooks();
		$this->elements->add_hooks();
		$this->element_choices->add_hooks();
		$this->element_settings->add_hooks();
		$this->submissions->add_hooks();
		$this->submission_values->add_hooks();

		$this->forms->capabilities()->add_hooks();
		$this->form_categories->capabilities()->add_hooks();
		$this->containers->capabilities()->add_hooks();
		$this->elements->capabilities()->add_hooks();
		$this->element_choices->capabilities()->add_hooks();
		$this->element_settings->capabilities()->add_hooks();
		$this->submissions->capabilities()->add_hooks();
		$this->submission_values->capabilities()->add_hooks();

		$this->post_types->add_hooks();
		$this->taxonomies->add_hooks();
	}

	/**
	 * Adds the necessary module hooks.
	 *
	 * @since 1.0.0
	 */
	protected function add_module_hooks() {
		$this->modules->add_hooks();
	}

	/**
	 * Adds the necessary plugin component service hooks.
	 *
	 * @since 1.0.0
	 */
	protected function add_component_service_hooks() {
		$this->admin_pages->add_hooks();
		$this->extensions->add_hooks();
	}
}
