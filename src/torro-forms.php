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
 * @method awsmug\Torro_Forms\DB_Objects\Containers\Container_Manager               containers()
 * @method awsmug\Torro_Forms\DB_Objects\Elements\Element_Manager                   elements()
 * @method awsmug\Torro_Forms\DB_Objects\Element_Choices\Element_Choice_Manager     element_choices()
 * @method awsmug\Torro_Forms\DB_Objects\Element_Settings\Element_Setting_Manager   element_settings()
 * @method awsmug\Torro_Forms\DB_Objects\Submissions\Submission_Manager             submissions()
 * @method awsmug\Torro_Forms\DB_Objects\Submission_Values\Submission_Value_Manager submission_values()
 * @method awsmug\Torro_Forms\DB_Objects\Participants\Participant_Manager           participants()
 * @method awsmug\Torro_Forms\Post_Types                                            post_types()
 * @method awsmug\Torro_Forms\Taxonomies                                            taxonomies()
 * @method Leaves_And_Love\Plugin_Lib\Options                                       options()
 * @method Leaves_And_Love\Plugin_Lib\Cache                                         cache()
 * @method awsmug\Torro_Forms\DB                                                    db()
 * @method Leaves_And_Love\Plugin_Lib\Meta                                          meta()
 * @method Leaves_And_Love\Plugin_Lib\Assets                                        assets()
 * @method Leaves_And_Love\Plugin_Lib\Template                                      template()
 * @method Leaves_And_Love\Plugin_Lib\AJAX                                          ajax()
 * @method Leaves_And_Love\Plugin_Lib\Error_Handler                                 error_handler()
 * @method Leaves_And_Love\Plugin_Lib\Components\Admin_Pages                        admin_pages()
 * @method Leaves_And_Love\Plugin_Lib\Components\Extensions                         extensions()
 */
class Torro_Forms extends Leaves_And_Love_Plugin {

	/**
	 * The forms manager instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var awsmug\Torro_Forms\DB_Objects\Forms\Form_Manager
	 */
	protected $forms;

	/**
	 * The forms manager instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var awsmug\Torro_Forms\DB_Objects\Containers\Container_Manager
	 */
	protected $containers;

	/**
	 * The forms manager instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var awsmug\Torro_Forms\DB_Objects\Elements\Element_Manager
	 */
	protected $elements;

	/**
	 * The forms manager instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var awsmug\Torro_Forms\DB_Objects\Element_Choices\Element_Choice_Manager
	 */
	protected $element_choices;

	/**
	 * The forms manager instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var awsmug\Torro_Forms\DB_Objects\Element_Settings\Element_Setting_Manager
	 */
	protected $element_settings;

	/**
	 * The forms manager instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var awsmug\Torro_Forms\DB_Objects\Submissions\Submission_Manager
	 */
	protected $submissions;

	/**
	 * The forms manager instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var awsmug\Torro_Forms\DB_Objects\Submission_Values\Submission_Value_Manager
	 */
	protected $submission_values;

	/**
	 * The forms manager instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var awsmug\Torro_Forms\DB_Objects\Participants\Participant_Manager
	 */
	protected $participants;

	/**
	 * The post types API instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var awsmug\Torro_Forms\Post_Types
	 */
	protected $post_types;

	/**
	 * The taxonomies API instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var awsmug\Torro_Forms\Taxonomies
	 */
	protected $taxonomies;

	/**
	 * The Option API instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Options
	 */
	protected $options;

	/**
	 * The cache instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Cache
	 */
	protected $cache;

	/**
	 * The database instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var awsmug\Torro_Forms\DB
	 */
	protected $db;

	/**
	 * The Metadata API instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Meta
	 */
	protected $meta;

	/**
	 * The Assets manager instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Assets
	 */
	protected $assets;

	/**
	 * The Template instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Template
	 */
	protected $template;

	/**
	 * The AJAX handler instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\AJAX
	 */
	protected $ajax;

	/**
	 * The error handler instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Error_Handler
	 */
	protected $error_handler;

	/**
	 * The Admin Pages instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Components\Admin_Pages
	 */
	protected $admin_pages;

	/**
	 * The Extensions instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Components\Extensions
	 */
	protected $extensions;

	/**
	 * Uninstalls the plugin.
	 *
	 * Drops all database tables and related content.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @codeCoverageIgnore
	 */
	public static function uninstall() {
		//TODO: uninstall routine
	}

	/**
	 * Returns the uninstall callback.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return callable Uninstall callback.
	 */
	public function get_uninstall_hook() {
		return array( __CLASS__, 'uninstall' );
	}

	/**
	 * Loads the base properties of the class.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function load_base_properties() {
		$this->version = '1.0.0';
		$this->prefix = 'torro_';
		$this->vendor_name = 'awsmug';
		$this->project_name = 'Torro_Forms';
		$this->minimum_php = '5.6';
		$this->minimum_wp = '4.7';
	}

	/**
	 * Loads the plugin's textdomain.
	 *
	 * @since 1.0.0
	 * @access protected
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
	 * @access protected
	 */
	protected function load_messages() {
		$this->messages['cheatin_huh']  = __( 'Cheatin&#8217; huh?', 'torro-forms' );
		$this->messages['outdated_php'] = __( 'Torro Forms cannot be initialized because your setup uses a PHP version older than %s.', 'torro-forms' );
		$this->messages['outdated_wp']  = __( 'Torro Forms cannot be initialized because your setup uses a WordPress version older than %s.', 'torro-forms' );
	}

	/**
	 * Checks whether the dependencies have been loaded.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return bool True if the dependencies are loaded, false otherwise.
	 */
	protected function dependencies_loaded() {
		return class_exists( 'EDD_SL_Plugin_Updater' ) && class_exists( 'PHPExcel' );
	}

	/**
	 * Instantiates the plugin services.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function instantiate_services() {
		$this->instantiate_core_services();
		$this->instantiate_db_object_managers();
		$this->connect_db_object_managers();
	}

	/**
	 * Instantiates the plugin core services.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function instantiate_core_services() {
		call_user_func( array( 'Leaves_And_Love\Plugin_Lib\Fields\Field_Manager', 'set_translations' ), $this->instantiate_plugin_class( 'Translations\Translations_Field_Manager' ) );

		$this->error_handler = $this->instantiate_library_service( 'Error_Handler', $this->prefix, $this->instantiate_plugin_class( 'Translations\Translations_Error_Handler' ) );

		$this->options = $this->instantiate_library_service( 'Options', $this->prefix );

		$this->cache = $this->instantiate_library_service( 'Cache', $this->prefix );

		$this->db = $this->instantiate_plugin_service( 'DB', $this->prefix, array(
			'options'       => $this->options,
			'error_handler' => $this->error_handler,
		), $this->instantiate_plugin_class( 'Translations\Translations_DB' ) );

		$this->meta = $this->instantiate_library_service( 'Meta', $this->prefix, array(
			'db'            => $this->db,
			'error_handler' => $this->error_handler,
		) );

		$this->assets = $this->instantiate_library_service( 'Assets', $this->prefix, array(
			'path_callback' => array( $this, 'path' ),
			'url_callback'  => array( $this, 'url' ),
		) );

		$this->template = $this->instantiate_library_service( 'Template', $this->prefix, array(
			'default_location' => $this->path( 'templates/' ),
		) );

		$this->ajax = $this->instantiate_library_service( 'AJAX', $this->prefix, $this->instantiate_plugin_class( 'Translations\Translations_AJAX' ) );

		$this->admin_pages = $this->instantiate_library_service( 'Components\Admin_Pages', $this->prefix, array(
			'ajax'          => $this->ajax,
			'assets'        => $this->assets,
			'error_handler' => $this->error_handler,
		) );

		$this->extensions = $this->instantiate_library_service( 'Components\Extensions', $this->prefix, $this->instantiate_plugin_class( 'Translations\Translations_Extensions' ) );
		$this->extensions->set_plugin( $this );

		$this->post_types = $this->instantiate_plugin_service( 'Post_Types', $this->prefix, array(
			'options'       => $this->options,
			'error_handler' => $this->error_handler,
		) );

		$this->taxonomies = $this->instantiate_plugin_service( 'Taxonomies', $this->prefix, array(
			'options'       => $this->options,
			'error_handler' => $this->error_handler,
		) );
	}

	/**
	 * Instantiates the plugin DB object managers.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function instantiate_db_object_managers() {
		$this->forms = $this->instantiate_plugin_service( 'DB_Objects\Forms\Form_Manager', $this->prefix, array(
			'capabilities'  => $this->instantiate_plugin_service( 'DB_Objects\Forms\Form_Capabilities', $this->prefix ),
			'db'            => $this->db,
			'cache'         => $this->cache,
			'meta'          => $this->meta,
			'error_handler' => $this->error_handler,
		), $this->instantiate_plugin_class( 'Translations\Translations_Form_Manager' ) );

		$this->containers = $this->instantiate_plugin_service( 'DB_Objects\Containers\Container_Manager', $this->prefix, array(
			'capabilities'  => $this->instantiate_plugin_service( 'DB_Objects\Containers\Container_Capabilities', $this->prefix ),
			'db'            => $this->db,
			'cache'         => $this->cache,
			'error_handler' => $this->error_handler,
		), $this->instantiate_plugin_class( 'Translations\Translations_Container_Manager' ) );

		$this->elements = $this->instantiate_plugin_service( 'DB_Objects\Elements\Element_Manager', $this->prefix, array(
			'capabilities'  => $this->instantiate_plugin_service( 'DB_Objects\Elements\Element_Capabilities', $this->prefix ),
			'db'            => $this->db,
			'cache'         => $this->cache,
			'error_handler' => $this->error_handler,
		), $this->instantiate_plugin_class( 'Translations\Translations_Element_Manager' ) );

		$this->element_choices = $this->instantiate_plugin_service( 'DB_Objects\Element_Choices\Element_Choice_Manager', $this->prefix, array(
			'capabilities'  => $this->instantiate_plugin_service( 'DB_Objects\Element_Choices\Element_Choice_Capabilities', $this->prefix ),
			'db'            => $this->db,
			'cache'         => $this->cache,
			'error_handler' => $this->error_handler,
		), $this->instantiate_plugin_class( 'Translations\Translations_Element_Choice_Manager' ) );

		$this->element_settings = $this->instantiate_plugin_service( 'DB_Objects\Element_Settings\Element_Setting_Manager', $this->prefix, array(
			'capabilities'  => $this->instantiate_plugin_service( 'DB_Objects\Element_Settings\Element_Setting_Capabilities', $this->prefix ),
			'db'            => $this->db,
			'cache'         => $this->cache,
			'error_handler' => $this->error_handler,
		), $this->instantiate_plugin_class( 'Translations\Translations_Element_Setting_Manager' ) );

		$this->submissions = $this->instantiate_plugin_service( 'DB_Objects\Submissions\Submission_Manager', $this->prefix, array(
			'capabilities'  => $this->instantiate_plugin_service( 'DB_Objects\Submissions\Submission_Capabilities', $this->prefix ),
			'db'            => $this->db,
			'cache'         => $this->cache,
			'error_handler' => $this->error_handler,
		), $this->instantiate_plugin_class( 'Translations\Translations_Submission_Manager' ) );

		$this->submission_values = $this->instantiate_plugin_service( 'DB_Objects\Submission_Values\Submission_Value_Manager', $this->prefix, array(
			'capabilities'  => $this->instantiate_plugin_service( 'DB_Objects\Submission_Values\Submission_Value_Capabilities', $this->prefix ),
			'db'            => $this->db,
			'cache'         => $this->cache,
			'error_handler' => $this->error_handler,
		), $this->instantiate_plugin_class( 'Translations\Translations_Submission_Value_Manager' ) );

		$this->participants = $this->instantiate_plugin_service( 'DB_Objects\Participants\Participant_Manager', $this->prefix, array(
			'capabilities'  => $this->instantiate_plugin_service( 'DB_Objects\Participants\Participant_Capabilities', $this->prefix ),
			'db'            => $this->db,
			'cache'         => $this->cache,
			'error_handler' => $this->error_handler,
		), $this->instantiate_plugin_class( 'Translations\Translations_Participant_Manager' ) );

		$this->db->set_version( 20170422 );
	}

	/**
	 * Connects the plugin DB object managers through hierarchical relationships.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function connect_db_object_managers() {
		$this->forms->add_child_manager( 'containers', $this->containers );
		$this->forms->add_child_manager( 'submissions', $this->submissions );
		$this->forms->add_child_manager( 'participants', $this->participants );

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

		$this->participants->add_parent_manager( 'forms', $this->forms );
	}

	/**
	 * Adds the necessary plugin hooks.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function add_hooks() {
		$this->add_core_service_hooks();
		$this->add_db_object_manager_hooks();
	}

	/**
	 * Adds the necessary plugin core service hooks.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function add_core_service_hooks() {
		$this->options->add_hooks();
		$this->db->add_hooks();
		$this->ajax->add_hooks();
		$this->admin_pages->add_hooks();
		$this->extensions->add_hooks();
		$this->post_types->add_hooks();
		$this->taxonomies->add_hooks();
	}

	/**
	 * Adds the necessary plugin DB object manager hooks.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function add_db_object_manager_hooks() {
		$this->forms->add_hooks();
		$this->containers->add_hooks();
		$this->elements->add_hooks();
		$this->element_choices->add_hooks();
		$this->element_settings->add_hooks();
		$this->submissions->add_hooks();
		$this->submission_values->add_hooks();
		$this->participants->add_hooks();
	}
}
