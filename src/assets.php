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
	 * Transforms a relative asset path into a full URL.
	 *
	 * The method also automatically handles loading a minified vs non-minified file.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $src Relative asset path.
	 * @return string|bool Full asset URL, or false if the path
	 *                     is requested for a full $src URL.
	 */
	public function get_full_url( $src ) {
		return $this->get_full_path( $src, true );
	}

	/**
	 * Transforms a relative asset path into a full path.
	 *
	 * The method also automatically handles loading a minified vs non-minified file.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $src Relative asset path.
	 * @param bool   $url Whether to return the URL instead of the path. Default false.
	 * @return string|bool Full asset path or URL, depending on the $url parameter, or false
	 *                     if the path is requested for a full $src URL.
	 */
	public function get_full_path( $src, $url = false ) {
		if ( preg_match( '/^(http|https):\/\//', $src ) || 0 === strpos( $src, '//' ) ) {
			if ( $url ) {
				return $src;
			}

			return false;
		}

		if ( '.js' !== substr( $src, -3 ) && '.css' !== substr( $src, -4 ) ) {
			if ( $url ) {
				return call_user_func( $this->url_callback, $src );
			}

			return call_user_func( $this->path_callback, $src );
		}

		return parent::get_full_path( $src, $url );
	}

	/**
	 * Registers all default plugin assets.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_assets() {
		$this->register_style( 'frontend', 'assets/dist/css/frontend.css', array(
			'deps' => array(),
			'ver'  => $this->plugin_version,
		) );

		$this->register_script( 'util', 'assets/dist/js/util.js', array(
			'deps'      => array( 'jquery', 'underscore', 'wp-util', 'wp-api' ),
			'ver'       => $this->plugin_version,
			'in_footer' => true,
		) );

		$this->register_style( 'admin-icons', 'assets/dist/css/admin-icons.css', array(
			'deps' => array(),
			'ver'  => $this->plugin_version,
		) );

		$this->register_script( 'admin-fixed-sidebar', 'assets/dist/js/admin-fixed-sidebar.js', array(
			'deps'      => array( 'jquery' ),
			'ver'       => $this->plugin_version,
			'in_footer' => true,
		) );

		$this->register_style( 'admin-fixed-sidebar', 'assets/dist/css/admin-fixed-sidebar.css', array(
			'deps' => array(),
			'ver'  => $this->plugin_version,
		) );

		$this->register_script( 'admin-form-builder', 'assets/dist/js/admin-form-builder.js', array(
			'deps'          => array( $this->prefix_handle( 'util' ), 'jquery', 'underscore', 'backbone', 'wp-backbone' ),
			'ver'           => $this->plugin_version,
			'in_footer'     => true,
			'localize_name' => 'torroBuilderI18n',
			'localize_data' => array(
				'couldNotInitCanvas'    => __( 'Could not initialize form canvas as the selector points to an element that does not exist.', 'torro-forms' ),
				'couldNotLoadData'      => __( 'Could not load form builder data. Please verify that the REST API is correctly enabled on your site.', 'torro-forms' ),
				/* translators: %s: container index number */
				'defaultContainerLabel' => __( 'Page %s', 'torro-forms' ),
			),
		) );

		$this->register_style( 'admin-form-builder', 'assets/dist/css/admin-form-builder.css', array(
			'deps' => array(),
			'ver'  => $this->plugin_version,
		) );

		$this->register_script( 'admin-settings', 'assets/dist/js/admin-settings.js', array(
			'deps'      => array( 'jquery' ),
			'ver'       => $this->plugin_version,
			'in_footer' => true,
		) );

		$this->register_style( 'admin-settings', 'assets/dist/css/admin-settings.css', array(
			'deps' => array(),
			'ver'  => $this->plugin_version,
		) );

		$this->register_script( 'template-tag-fields', 'assets/dist/js/template-tag-fields.js', array(
			'deps'      => array( 'plugin-lib-fields', 'jquery' ),
			'ver'       => $this->plugin_version,
			'in_footer' => true,
		) );

		$this->register_style( 'template-tag-fields', 'assets/dist/css/template-tag-fields.css', array(
			'deps' => array( 'plugin-lib-fields' ),
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
		 * @param Assets $assets The assets manager instance.
		 */
		do_action( "{$this->get_prefix()}register_assets", $this );
	}

	/**
	 * Enqueues the icons stylesheet.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function enqueue_icons() {
		$this->enqueue_style( 'admin-icons' );
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
			array(
				'name'     => 'admin_enqueue_scripts',
				'callback' => array( $this, 'enqueue_icons' ),
				'priority' => 10,
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
