<?php
/**
 * Autoloader file
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

if ( ! class_exists( 'Leaves_And_Love_Autoloader' ) ) :

	/**
	 * Autoloader class.
	 *
	 * Contains static methods to load classes.
	 *
	 * To resolve the path to a class, its namespace's vendor name and project name are
	 * checked, and if they are registered, the sub-namespaces are resolved to subdirectory
	 * names and the class name itself is resolved to a filename. All underscores are replaced
	 * with hyphens and the directory and file names are all lowercase.
	 *
	 * @since 1.0.0
	 */
	final class Leaves_And_Love_Autoloader {
		/**
		 * Whether the loader has been initialized.
		 *
		 * @since 1.0.0
		 * @static
		 * @var bool
		 */
		private static $initialized = false;

		/**
		 * Registered autoloader namespaces.
		 *
		 * @since 1.0.0
		 * @static
		 * @var array
		 */
		private static $namespaces = array();

		/**
		 * Registers a namespace for autoloading.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $vendor_name  Vendor name of base namespace.
		 * @param string $project_name Project name of base namespace.
		 * @param string $basedir      Base directory to load classes from.
		 * @return bool True on success, false on failure.
		 */
		public static function register_namespace( $vendor_name, $project_name, $basedir ) {
			if ( self::namespace_registered( $vendor_name, $project_name ) ) {
				return false;
			}

			if ( ! self::$initialized ) {
				if ( self::init() ) {
					self::$initialized = true;
				}
			}

			if ( ! isset( self::$namespaces[ $vendor_name ] ) ) {
				self::$namespaces[ $vendor_name ] = array();
			}

			self::$namespaces[ $vendor_name ][ $project_name ] = trailingslashit( $basedir );

			return true;
		}

		/**
		 * Checks whether a namespace is registered.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $vendor_name  Vendor name of base namespace.
		 * @param string $project_name Project name of base namespace.
		 * @return bool True if the namespace is registered, false otherwise.
		 */
		public static function namespace_registered( $vendor_name, $project_name ) {
			if ( ! isset( self::$namespaces[ $vendor_name ] ) ) {
				return false;
			}

			if ( ! isset( self::$namespaces[ $vendor_name ][ $project_name ] ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Unregisters a namespace from autoloading.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $vendor_name  Vendor name of base namespace.
		 * @param string $project_name Project name of base namespace.
		 * @return bool True on success, false on failure.
		 */
		public static function unregister_namespace( $vendor_name, $project_name ) {
			if ( ! self::namespace_registered( $vendor_name, $project_name ) ) {
				return false;
			}

			unset( self::$namespaces[ $vendor_name ][ $project_name ] );

			if ( empty( self::$namespaces[ $vendor_name ] ) ) {
				unset( self::$namespaces[ $vendor_name ] );
			}

			return true;
		}

		/**
		 * Returns the array of registered namespaces.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @return array Registered namespaces.
		 */
		public static function get_registered_namespaces() {
			return self::$namespaces;
		}

		/**
		 * Tries to autoload a class.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $class_name Class name.
		 * @return bool True on success, false on failure.
		 */
		public static function load_class( $class_name ) {
			$parts = explode( '\\', $class_name );

			$vendor_name = array_shift( $parts );
			if ( ! isset( self::$namespaces[ $vendor_name ] ) ) {
				return false;
			}

			$project_name = array_shift( $parts );
			if ( ! isset( self::$namespaces[ $vendor_name ][ $project_name ] ) ) {
				return false;
			}

			$path = self::$namespaces[ $vendor_name ][ $project_name ] . strtolower( str_replace( '_', '-', implode( '/', $parts ) ) ) . '.php';
			if ( ! file_exists( $path ) ) {
				return false;
			}

			require_once $path;

			return true;
		}

		/**
		 * Initializes the autoloader.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @return bool True on success, false on failure.
		 *
		 * @codeCoverageIgnore
		 */
		private static function init() {
			if ( ! function_exists( 'spl_autoload_register' ) ) {
				return false;
			}

			spl_autoload_register( array( __CLASS__, 'load_class' ) );

			return true;
		}
	}

endif;
