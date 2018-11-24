<?php
/**
 * Plugin initialization file
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

if ( ! class_exists( 'Leaves_And_Love_Plugin_Loader' ) ) :

	/**
	 * Plugin loader class.
	 *
	 * Contains static methods to load and manage plugins.
	 *
	 * @since 1.0.0
	 */
	final class Leaves_And_Love_Plugin_Loader {
		/**
		 * Version number of the library.
		 *
		 * @since 1.0.0
		 */
		const VERSION = '1.0.4';

		/**
		 * Whether the loader has been initialized.
		 *
		 * @since 1.0.0
		 * @static
		 * @var bool
		 */
		private static $initialized = false;

		/**
		 * Plugin class instances.
		 *
		 * @since 1.0.0
		 * @static
		 * @var array
		 */
		private static $instances = array();

		/**
		 * Loads a specific plugin.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $class_name       Name of the plugin's main class.
		 * @param string $main_file        Path to the plugin's main file.
		 * @param string $basedir_relative The relative path to the other files of the plugin.
		 * @return bool True if the plugin has been loaded successfully, false otherwise.
		 */
		public static function load( $class_name, $main_file, $basedir_relative = '' ) {
			if ( isset( self::$instances[ $class_name ] ) ) {
				return false;
			}

			if ( ! is_subclass_of( $class_name, 'Leaves_And_Love_Plugin' ) ) {
				return false;
			}

			if ( ! self::$initialized ) {
				Leaves_And_Love_Autoloader::register_namespace( 'Leaves_And_Love', 'Plugin_Lib', dirname( __FILE__ ) . '/src/' );

				self::$initialized = true;
			}

			self::$instances[ $class_name ] = new $class_name( $main_file, $basedir_relative );

			if ( is_wp_error( self::$instances[ $class_name ]->error() ) ) {
				if ( ! has_action( 'admin_notices', array( __CLASS__, 'error_notice' ) ) ) {
					add_action( 'admin_notices', array( __CLASS__, 'error_notice' ) );
				}

				return false;
			}

			self::bootstrap_instance( self::$instances[ $class_name ], $main_file );

			return true;
		}

		/**
		 * Returns a specific plugin's main class.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $class_name Name of the plugin's main class.
		 * @return Leaves_And_Love_Plugin|WP_Error|null The plugin instance on success, an error object if the plugin
		 *                                              could not be started, or null if the plugin was not found.
		 */
		public static function get( $class_name ) {
			if ( ! isset( self::$instances[ $class_name ] ) ) {
				return null;
			}

			if ( is_wp_error( self::$instances[ $class_name ]->error() ) ) {
				return self::$instances[ $class_name ]->error();
			}

			return self::$instances[ $class_name ];
		}

		/**
		 * Renders an error notice for all plugins that could not be started.
		 *
		 * @since 1.0.0
		 * @static
		 */
		public static function error_notice() {
			foreach ( self::$instances as $plugin ) {
				if ( ! is_wp_error( $plugin->error() ) ) {
					continue;
				}

				?>
				<div class="notice notice-error">
					<p><?php echo wp_kses_data( $plugin->error()->get_error_message() ); ?></p>
				</div>
				<?php
			}
		}

		/**
		 * Adds the necessary hooks to bootstrap a plugin instance.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param Leaves_And_Love_Plugin $instance  The plugin instance.
		 * @param string                 $main_file Path to the plugin's main file.
		 */
		private static function bootstrap_instance( $instance, $main_file ) {
			$instance->load();

			add_action( 'plugins_loaded', array( $instance, 'start' ) );

			$activation_hook = $instance->get_activation_hook();
			if ( $activation_hook ) {
				register_activation_hook( $main_file, $activation_hook );
			}

			$deactivation_hook = $instance->get_deactivation_hook();
			if ( $deactivation_hook ) {
				register_deactivation_hook( $main_file, $deactivation_hook );
			}

			$uninstall_hook = $instance->get_uninstall_hook();
			if ( $uninstall_hook ) {
				register_uninstall_hook( $main_file, $uninstall_hook );
			}
		}
	}

endif;

require_once dirname( __FILE__ ) . '/src/plugin.php';
require_once dirname( __FILE__ ) . '/src/autoloader.php';
