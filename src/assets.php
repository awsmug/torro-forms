<?php
/**
 * Assets manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms;

use Leaves_And_Love\Plugin_Lib\Assets as Assets_Base;
use Leaves_And_Love\Plugin_Lib\Traits\Hook_Service_Trait;

/**
 * Class for managing assets.
 *
 * @since 1.0.0
 */
class Assets extends Assets_Base {
	use Hook_Service_Trait;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $prefix The prefix for all AJAX actions.
	 * @param array  $args   {
	 *     Array of arguments.
	 *
	 *     @type callable $path_callback Callback to create a full plugin path from a relative path.
	 *     @type callable $url_callback  Callback to create a full plugin URL from a relative path.
	 * }
	 */
	public function __construct( $prefix, $args ) {
		parent::__construct( $prefix, $args );

		$this->setup_hooks();
	}

	/**
	 * Registers all default plugin assets.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_assets() {
		$this->register_script( 'admin-settings', 'assets/dist/js/admin-settings.js', array(
			'deps'      => array( 'jquery' ),
			'ver'       => $this->plugin_version,
			'in_footer' => true,
		) );

		$this->register_style( 'admin-settings', 'assets/dist/css/admin-settings.css', array(
			'deps' => array(),
			'ver'  => $this->plugin_version,
		) );

		/**
		 * Fires after all default plugin assets have been registered.
		 *
		 * Do not use this action to actually enqueue any assets, as it is only
		 * intended for registering them.
		 *
		 * @since 1.0.0
		 *
		 * @param awsmug\Torro_Forms\Assets $assets The assets manager instance.
		 */
		do_action( "{$this->get_prefix()}register_assets", $this );
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
				'name'     => 'wp_enqueue_scripts',
				'callback' => array( $this, 'register_assets' ),
				'priority' => 1,
				'num_args' => 0,
			),
			array(
				'name'     => 'admin_enqueue_scripts',
				'callback' => array( $this, 'register_assets' ),
				'priority' => 1,
				'num_args' => 0,
			),
		);
	}

	/**
	 * Parses the plugin version number.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 *
	 * @param mixed $value The input value.
	 * @return string The parsed value.
	 */
	protected static function parse_arg_plugin_version( $value ) {
		if ( ! $value ) {
			return false;
		}

		return $value;
	}
}
