<?php
/**
 * Module base class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules;

use Leaves_And_Love\Plugin_Lib\Service;
use Leaves_And_Love\Plugin_Lib\Traits\Container_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Traits\Hook_Service_Trait;

/**
 * Base class for a module.
 *
 * @since 1.0.0
 *
 * @method awsmug\Torro_Forms\Modules\Module_Manager manager()
 */
abstract class Module extends Service {
	use Container_Service_Trait, Hook_Service_Trait;

	/**
	 * The module slug. Must match the slug when registering the module.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $slug = '';

	/**
	 * The module title.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $title = '';

	/**
	 * The module description.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $description = '';

	/**
	 * The module manager service definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var string
	 */
	protected static $service_manager = 'awsmug\Torro_Forms\Modules\Module_Manager';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $prefix   The instance prefix.
	 * @param array  $services {
	 *     Array of service instances.
	 *
	 *     @type Leaves_And_Love\Plugin_Lib\Options       $options       The Option API class instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Error_Handler $error_handler The error handler instance.
	 * }
	 */
	public function __construct( $prefix, $services ) {
		$this->set_prefix( $prefix );
		$this->set_services( $services );

		$this->bootstrap();
		$this->setup_hooks();
	}

	/**
	 * Returns the module slug.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Module slug.
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Returns the module title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Module title.
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Returns the module description.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Module description.
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Checks whether this module is active.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool True if the module is active, false otherwise.
	 */
	public function is_active() {
		$options = $this->manager()->options()->get( 'general_settings', array() );
		if ( isset( $options['modules'] ) && is_array( $options['modules'] ) ) {
			return in_array( $this->slug, $options['modules'], true );
		}

		return true;
	}

	/**
	 * Retrieves the value of a specific module option.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $option  Name of the option to retrieve.
	 * @param mixed  $default Optional. Value to return if the option doesn't exist. Default false.
	 * @return mixed Value set for the option.
	 */
	public function get_option( $option, $default = false ) {
		$options = $this->manager()->options()->get( $this->get_settings_identifier(), array() );

		if ( isset( $options[ $option ] ) ) {
			return $options[ $option ];
		}

		return $default;
	}

	/**
	 * Adds settings subtabs, sections and fields for the module to the plugin settings page.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param awsmug\Torro_Forms\DB_Objects\Forms\Form_Settings_Page $settings_page Settings page instance.
	 */
	protected final function add_settings( $settings_page ) {
		$subtabs = $this->get_settings_subtabs();
		if ( empty( $subtabs ) ) {
			return;
		}

		$sections = $this->get_settings_sections();
		$fields   = $this->get_settings_fields();

		$tab_id = $this->get_settings_identifier();

		$settings_page->add_tab( $tab_id, array(
			'title' => $this->get_title(),
		) );

		foreach ( $subtabs as $slug => $args ) {
			$args['tab'] = $tab_id;

			$settings_page->add_subtab( $slug, $args );
		}

		foreach ( $sections as $slug => $args ) {
			$settings_page->add_section( $slug, $args );
		}

		foreach ( $fields as $slug => $args ) {
			$type = 'text';
			if ( isset( $args['type'] ) ) {
				$type = $args['type'];
				unset( $args['type'] );
			}

			$settings_page->add_field( $slug, $type, $args );
		}
	}

	/**
	 * Returns the settings identifier for the module.
	 *
	 * This identifier must be used to access module options.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string Module settings identifier.
	 */
	protected final function get_settings_identifier() {
		return 'module_' . $this->slug;
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * This method must be implemented and then be called from the constructor.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function setup_hooks() {
		$this->actions = array(
			array(
				'name'     => "{$this->get_prefix()}add_form_settings_content",
				'callback' => array( $this, 'add_settings' ),
				'priority' => 1,
				'num_args' => 1,
			),
			array(
				'name'     => "{$this->get_prefix()}register_assets",
				'callback' => array( $this, 'register_assets' ),
				'priority' => 1,
				'num_args' => 1,
			),
		);
	}

	/**
	 * Bootstraps the module by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function bootstrap();

	/**
	 * Returns the available settings sub-tabs for the module.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$subtab_slug => $subtab_args` pairs.
	 */
	protected abstract function get_settings_subtabs();

	/**
	 * Returns the available settings sections for the module.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$section_slug => $section_args` pairs.
	 */
	protected abstract function get_settings_sections();

	/**
	 * Returns the available settings fields for the module.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	protected abstract function get_settings_fields();

	/**
	 * Registers the available module scripts and stylesheets.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param awsmug\Torro_Forms\Assets $assets Assets API instance.
	 */
	protected abstract function register_assets( $assets );
}
