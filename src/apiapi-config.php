<?php
/**
 * API-API configuration class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms;

use APIAPI\Core\APIAPI;
use APIAPI\Core\Config;
use Leaves_And_Love\Plugin_Lib\Traits\Hook_Service_Trait;

/**
 * Class for setting up the configuration for the plugin's API-API instance.
 *
 * @since 1.0.0
 */
class APIAPI_Config extends Config {
	use Hook_Service_Trait;

	/**
	 * Plugin prefix.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * Constructor.
	 *
	 * Allows to set the config parameters.
	 *
	 * @since 1.0.0
	 *
	 * @param string $prefix Plugin config prefix.
	 * @param array  $params Optional. Associative array of config parameters with their values. Default empty.
	 */
	public function __construct( $prefix, $params = null ) {
		$this->prefix = $prefix;

		parent::__construct( $params );

		$this->setup_hooks();
	}

	/**
	 * Returns the default parameters with their values.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of default config parameters with their values.
	 */
	protected function get_defaults() {
		return array(
			'transporter'            => 'wordpress',
			'config_updater'         => true,
			'config_updater_storage' => 'wordpress-option',
			'config_updater_args'    => array(
				'listener_query_var' => 'structure',
				'auth_basename'      => $this->prefix . 'apiapi_auth',
				'callback_base_url'  => add_query_arg( 'action', $this->prefix . 'apiapi_callback', admin_url( 'admin.php' ) ),
				'setup_config_hook'  => 'setup_config',
				'listener_hook'      => 'listen_for_callback',
			),
		);
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * This method must be implemented and then be called from the constructor.
	 *
	 * @since 1.0.0
	 */
	protected function setup_hooks() {
		$this->actions = array(
			array(
				'name'     => 'wp_loaded',
				'callback' => array( $this, 'trigger_setup_config' ),
				'priority' => 10,
				'num_args' => 0,
			),
			array(
				'name'     => "admin_action_{$this->prefix}apiapi_callback",
				'callback' => array( $this, 'trigger_listen_for_callback' ),
				'priority' => 10,
				'num_args' => 0,
			),
		);
	}

	/**
	 * Triggers the API-API instance's 'setup_config' hook.
	 *
	 * @since 1.0.0
	 */
	protected function trigger_setup_config() {
		torro()->apiapi()->trigger_hook( 'setup_config', $this );
	}

	/**
	 * Triggers the API-API instance's 'listen_for_callback' hook.
	 *
	 * @since 1.0.0
	 */
	protected function trigger_listen_for_callback() {
		torro()->apiapi()->trigger_hook( 'listen_for_callback' );

		/* TODO: Redirect to the correct location. */
	}
}
