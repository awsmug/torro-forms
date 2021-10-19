<?php
/**
 * Plugin main file
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

if ( ! class_exists( 'Leaves_And_Love_Plugin' ) ) :

	/**
	 * Main plugin class.
	 *
	 * Takes care of initializing the plugin.
	 *
	 * @since 1.0.0
	 */
	abstract class Leaves_And_Love_Plugin {
		/**
		 * The plugin version.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $version;

		/**
		 * The plugin prefix.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $prefix;

		/**
		 * The plugin vendor name.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $vendor_name;

		/**
		 * The plugin project name.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $project_name;

		/**
		 * The minimum required PHP version.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $minimum_php;

		/**
		 * The minimum required WordPress version.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $minimum_wp;

		/**
		 * Path to the plugin's main file.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $main_file;

		/**
		 * Relative base path to the other files of this plugin.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $basedir_relative;

		/**
		 * Messages printed to the user.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $messages = array();

		/**
		 * Error object if the class cannot be initialized.
		 *
		 * @since 1.0.0
		 * @var WP_Error|null
		 */
		protected $error;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $main_file        Path to the plugin's main file.
		 * @param string $basedir_relative The relative base path to the other files of this plugin.
		 *
		 * @codeCoverageIgnore
		 */
		public function __construct( $main_file, $basedir_relative = '' ) {
			$this->main_file        = $main_file;
			$this->basedir_relative = $basedir_relative;
			$this->minimum_php      = '5.5';
			$this->minimum_wp       = '4.7';

			$this->load_base_properties();
			$this->load_textdomain();
			$this->load_messages();

			$this->error = $this->check();
		}

		/**
		 * Dummy magic method to prevent Content Organizer from being cloned.
		 *
		 * @since 1.0.0
		 *
		 * @codeCoverageIgnore
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, $this->messages['cheatin_huh'], '1.0.0' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Dummy magic method to prevent Content Organizer from being unserialized.
		 *
		 * @since 1.0.0
		 *
		 * @codeCoverageIgnore
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, $this->messages['cheatin_huh'], '1.0.0' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Returns class instances for the plugin.
		 *
		 * This magic method allows you to call methods with the name of a class property, which will
		 * then return the respective instance.
		 *
		 * @since 1.0.0
		 *
		 * @param string $method_name Name of the method to call.
		 * @param array  $args        Method arguments.
		 * @return object|null Either the class instance denoted by the method name, or null if it doesn't exist.
		 */
		public function __call( $method_name, $args ) {
			if ( in_array(
				$method_name,
				array(
					'get_activation_hook',
					'get_deactivation_hook',
					'get_uninstall_hook',
				),
				true
			) ) {
				return false;
			}

			if ( isset( $this->$method_name ) && is_a( $this->$method_name, 'Leaves_And_Love\Plugin_Lib\Service' ) ) {
				return $this->$method_name;
			}

			if ( 'error' === $method_name && is_wp_error( $this->error ) ) {
				return $this->error;
			}

			return null;
		}

		/**
		 * Returns the plugin version number.
		 *
		 * @since 1.0.0
		 *
		 * @return string Version number.
		 */
		public function version() {
			return $this->version;
		}

		/**
		 * Returns the full path to a relative path for a plugin file or directory.
		 *
		 * @since 1.0.0
		 *
		 * @param string $rel_path Relative path.
		 * @return string Full path.
		 */
		public function path( $rel_path ) {
			return plugin_dir_path( $this->main_file ) . $this->basedir_relative . ltrim( $rel_path, '/' );
		}

		/**
		 * Returns the full URL to a relative path for a plugin file or directory.
		 *
		 * @since 1.0.0
		 *
		 * @param string $rel_path Relative path.
		 * @return string Full URL.
		 */
		public function url( $rel_path ) {
			return plugin_dir_url( $this->main_file ) . $this->basedir_relative . ltrim( $rel_path, '/' );
		}

		/**
		 * Loads the plugin by registering the autoloader and instantiating the general classes.
		 *
		 * This method can only be executed once.
		 *
		 * @since 1.0.0
		 */
		public function load() {
			if ( did_action( $this->prefix . 'loaded' ) ) {
				return;
			}

			if ( ! $this->dependencies_loaded() ) {
				$vendor_autoload = $this->path( 'vendor/autoload.php' );
				if ( file_exists( $vendor_autoload ) ) {
					require_once $vendor_autoload;
				}
			}

			Leaves_And_Love_Autoloader::register_namespace( $this->vendor_name, $this->project_name, $this->path( 'src/' ) );

			$this->instantiate_services();

			/**
			 * Fires after the plugin has loaded.
			 *
			 * @since 1.0.0
			 *
			 * @param Leaves_And_Love_Plugin $plugin The plugin instance.
			 */
			do_action( $this->prefix . 'loaded', $this );
		}

		/**
		 * Starts the plugin by adding the necessary hooks.
		 *
		 * This method can only be executed once.
		 *
		 * @since 1.0.0
		 */
		public function start() {
			if ( did_action( $this->prefix . 'started' ) ) {
				return;
			}

			$this->add_hooks();

			/**
			 * Fires after the plugin has started.
			 *
			 * @since 1.0.0
			 *
			 * @param Leaves_And_Love_Plugin $plugin The plugin instance.
			 */
			do_action( $this->prefix . 'started', $this );
		}

		/**
		 * Checks whether the plugin can run on this setup.
		 *
		 * @since 1.0.0
		 *
		 * @return WP_Error|null Error object if the plugin cannot run on this setup, null otherwise.
		 */
		protected function check() {
			if ( version_compare( phpversion(), $this->minimum_php, '<' ) ) {
				return new WP_Error( $this->prefix . 'outdated_php', sprintf( $this->messages['outdated_php'], $this->minimum_php ) );
			}

			if ( version_compare( get_bloginfo( 'version' ), $this->minimum_wp, '<' ) ) {
				return new WP_Error( $this->prefix . 'outdated_wordpress', sprintf( $this->messages['outdated_wp'], $this->minimum_wp ) );
			}

			return null;
		}

		/**
		 * Instantiates a specific plugin service.
		 *
		 * @since 1.0.0
		 *
		 * @param string $class_name The class name, without basic namespace.
		 * @param mixed  $args,...   Optional arguments to pass to the constructor. It is recommended to pass
		 *                           the plugin prefix as first of these arguments.
		 *
		 * @return Leaves_And_Love\Plugin_Lib\Service|null The plugin service instance, or null if invalid.
		 */
		protected function instantiate_plugin_service( $class_name ) {
			$params = func_get_args();

			$params[0] = $this->vendor_name . '\\' . $this->project_name . '\\' . $class_name;

			return $this->instantiate_service( $params );
		}

		/**
		 * Instantiates a specific library service.
		 *
		 * @since 1.0.0
		 *
		 * @param string $class_name The class name, without basic namespace.
		 * @param mixed  $args,...   Optional arguments to pass to the constructor. It is recommended to pass
		 *                           the plugin prefix as first of these arguments.
		 *
		 * @return Leaves_And_Love\Plugin_Lib\Service|null The library service instance, or null if invalid.
		 */
		protected function instantiate_library_service( $class_name ) {
			$params = func_get_args();

			$params[0] = 'Leaves_And_Love\\Plugin_Lib\\' . $class_name;

			return $this->instantiate_service( $params );
		}

		/**
		 * Instantiates a specific plugin class.
		 *
		 * @since 1.0.0
		 *
		 * @param string $class_name The class name, without basic namespace.
		 * @param mixed  $args,...   Optional arguments to pass to the constructor.
		 * @return object The class instance.
		 */
		protected function instantiate_plugin_class( $class_name ) {
			$params = func_get_args();

			$params[0] = $this->vendor_name . '\\' . $this->project_name . '\\' . $class_name;

			return $this->instantiate_class( $params );
		}

		/**
		 * Instantiates a specific library class.
		 *
		 * @since 1.0.0
		 *
		 * @param string $class_name The class name, without basic namespace.
		 * @param mixed  $args,...   Optional arguments to pass to the constructor.
		 * @return object The class instance.
		 */
		protected function instantiate_library_class( $class_name ) {
			$params = func_get_args();

			$params[0] = 'Leaves_And_Love\\Plugin_Lib\\' . $class_name;

			return $this->instantiate_class( $params );
		}

		/**
		 * Loads the base properties of the class.
		 *
		 * @since 1.0.0
		 */
		abstract protected function load_base_properties();

		/**
		 * Loads the plugin's textdomain.
		 *
		 * @since 1.0.0
		 */
		abstract protected function load_textdomain();

		/**
		 * Loads the class messages.
		 *
		 * @since 1.0.0
		 */
		abstract protected function load_messages();

		/**
		 * Instantiates the plugin services.
		 *
		 * @since 1.0.0
		 */
		abstract protected function instantiate_services();

		/**
		 * Adds the necessary plugin hooks.
		 *
		 * @since 1.0.0
		 */
		abstract protected function add_hooks();

		/**
		 * Loads the plugin textdomain file.
		 *
		 * This is a helper method that wraps WordPress' `load_plugin_textdomain()`.
		 *
		 * @since 1.0.0
		 *
		 * @param string $textdomain Textdomain to load.
		 * @param string $rel_path   Optional. Relative path to the languages directory. Default empty.
		 * @return bool True if the textdomain file could be loaded, false otherwise.
		 */
		protected function load_plugin_textdomain( $textdomain, $rel_path = '' ) {
			if ( empty( $rel_path ) ) {
				return load_plugin_textdomain( $textdomain );
			}

			$plugin_rel_path = dirname( plugin_basename( $this->main_file ) ) . '/' . $this->basedir_relative . trim( $rel_path, '/' );

			return load_plugin_textdomain( $textdomain, false, $plugin_rel_path );
		}

		/**
		 * Checks whether the dependencies have been loaded.
		 *
		 * If this method returns false, the plugin will attempt to require the composer-generated
		 * autoloader script. If your plugin uses additional dependencies, override this method with
		 * a check whether these dependencies already exist.
		 *
		 * @since 1.0.0
		 *
		 * @return bool True if the dependencies are loaded, false otherwise.
		 */
		protected function dependencies_loaded() {
			return true;
		}

		/**
		 * Instantiates a specific service.
		 *
		 * This private method is called only internally.
		 *
		 * @since 1.0.0
		 *
		 * @param array $params Array of parameters for the originally called method.
		 * @return Leaves_And_Love\Plugin_Lib\Service|null The service instance, or null if invalid.
		 */
		private function instantiate_service( $params ) {
			if ( count( $params ) === 1 ) {
				array_push( $params, $this->prefix );
			} else {
				$key = array_search( $this->prefix, $params, true );
				if ( false === $key ) {
					array_splice( $params, 1, 0, $this->prefix );
				} elseif ( 1 !== $key ) {
					$value = array_splice( $params, $key, 1 );
					array_splice( $params, 1, 0, $value );
				}
			}

			return call_user_func_array( array( 'Leaves_And_Love\Plugin_Lib\Service_Instantiator', 'instantiate' ), $params );
		}

		/**
		 * Instantiates a specific class.
		 *
		 * This private method is called only internally.
		 *
		 * @since 1.0.0
		 *
		 * @param array $params Array of parameters for the originally called method.
		 * @return object The class instance.
		 */
		private function instantiate_class( $params ) {
			$class_name = array_shift( $params );

			if ( ! empty( $params ) ) {
				$reflected_class = new ReflectionClass( $class_name );
				return $reflected_class->newInstanceArgs( $params );
			}

			return new $class_name();
		}
	}

endif;
