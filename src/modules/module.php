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
	 * Bootstraps the module by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function bootstrap();
}
