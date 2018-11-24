<?php
/**
 * Extensions class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Components;

use Leaves_And_Love\Plugin_Lib\Service;
use Leaves_And_Love\Plugin_Lib\Traits\Hook_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Traits\Translations_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Translations\Translations_Extensions;
use Leaves_And_Love_Plugin;
use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Components\Extensions' ) ) :

	/**
	 * Class for Extensions API
	 *
	 * @since 1.0.0
	 */
	class Extensions extends Service {
		use Hook_Service_Trait, Translations_Service_Trait;

		/**
		 * Array of registered extensions.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $extensions = array();

		/**
		 * Name of the base class all extensions must inherit.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $base_class = Extension::class;

		/**
		 * The main plugin instance.
		 *
		 * @since 1.0.0
		 * @var Leaves_And_Love_Plugin
		 */
		protected $plugin = null;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string                  $prefix       The prefix.
		 * @param Translations_Extensions $translations Translations instance.
		 */
		public function __construct( $prefix, $translations ) {
			$this->set_prefix( $prefix );
			$this->set_translations( $translations );

			$this->setup_hooks();
		}

		/**
		 * Registers a new extension.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name             Identifier for the extension.
		 * @param string $class_name       Name of the extension class.
		 * @param string $main_file        Path to the extension's main file.
		 * @param string $basedir_relative Optional. The relative base path to the other files of this
		 *                                 extension. Default empty.
		 * @return bool|WP_Error True on success, error object on failure.
		 */
		public function register( $name, $class_name, $main_file, $basedir_relative = '' ) {
			if ( ! class_exists( $class_name ) ) {
				return new WP_Error( 'extension_class_not_exist', sprintf( $this->get_translation( 'extension_class_not_exist' ), $class_name ) );
			}

			if ( ! is_subclass_of( $class_name, $this->base_class ) ) {
				return new WP_Error( 'extension_class_invalid', sprintf( $this->get_translation( 'extension_class_invalid' ), $class_name, $this->base_class ) );
			}

			if ( $this->is_registered( $name ) ) {
				return new WP_Error( 'extension_already_registered', sprintf( $this->get_translation( 'extension_already_registered' ), $name ) );
			}

			$extension = new $class_name( $name, $this->plugin, $main_file, $basedir_relative );

			$compatibility = $extension->check();
			if ( is_wp_error( $compatibility ) ) {
				return $compatibility;
			}

			$extension->load();

			$this->extensions[ $name ] = $extension;

			return true;
		}

		/**
		 * Returns a specific extension class.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Identifier of the extension.
		 * @return Extension|null Extension instance, or null if it is not registered.
		 */
		public function get( $name ) {
			if ( ! $this->is_registered( $name ) ) {
				return null;
			}

			return $this->extensions[ $name ];
		}

		/**
		 * Checks whether a specific extension is registered.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Identifier of the extension.
		 * @return bool True if the extension is registered, false otherwise.
		 */
		public function is_registered( $name ) {
			return isset( $this->extensions[ $name ] );
		}

		/**
		 * Sets the main plugin instance.
		 *
		 * @since 1.0.0
		 *
		 * @param Leaves_And_Love_Plugin $plugin Plugin instance.
		 */
		public function set_plugin( $plugin ) {
			$this->plugin = $plugin;
		}

		/**
		 * Adds the service hooks.
		 *
		 * @since 1.0.0
		 */
		public function add_hooks() {
			if ( $this->hooks_added ) {
				return false;
			}

			foreach ( $this->extensions as $extension ) {
				$extension->add_hooks();
			}

			$this->hooks_added = true;

			return true;
		}

		/**
		 * Removes the service hooks.
		 *
		 * @since 1.0.0
		 */
		public function remove_hooks() {
			if ( ! $this->hooks_added ) {
				return false;
			}

			foreach ( $this->extensions as $extension ) {
				$extension->remove_hooks();
			}

			$this->hooks_added = false;

			return true;
		}

		/**
		 * Sets up all action and filter hooks for the service.
		 *
		 * This method must be implemented and then be called from the constructor.
		 *
		 * @since 1.0.0
		 */
		protected function setup_hooks() {
			// Empty method body.
		}
	}

endif;
