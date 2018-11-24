<?php
/**
 * Error handler class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib;

use Leaves_And_Love\Plugin_Lib\Traits\Translations_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Translations\Translations_Error_Handler;
use Leaves_And_Love\Plugin_Lib\Translations\Translations_Base_Error_Handler;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Error_Handler' ) ) :

	/**
	 * Class for error handling
	 *
	 * This class handles errors triggered by incorrect plugin usage.
	 *
	 * @since 1.0.0
	 *
	 * @codeCoverageIgnore
	 */
	class Error_Handler extends Service {
		use Translations_Service_Trait;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string                     $prefix       The instance prefix.
		 * @param Translations_Error_Handler $translations Translations instance.
		 */
		public function __construct( $prefix, $translations ) {
			$this->set_prefix( $prefix );
			$this->set_translations( $translations );
		}

		/**
		 * Marks a function as deprecated and inform when it has been used.
		 *
		 * @since 1.0.0
		 *
		 * @param string $function    The function that was called.
		 * @param string $version     The version of the plugin that deprecated the function.
		 * @param string $replacement Optional. The function that should have been called. Default null.
		 */
		public function deprecated_function( $function, $version, $replacement = null ) {
			do_action( 'deprecated_function_run', $function, $replacement, $version );

			if ( WP_DEBUG && apply_filters( 'deprecated_function_trigger_error', true ) ) {
				if ( ! is_null( $replacement ) ) {
					trigger_error( sprintf( $this->get_translation( 'deprecated_function' ), $function, $version, $replacement ) ); // phpcs:ignore
				} else {
					trigger_error( sprintf( $this->get_translation( 'deprecated_function_no_alt' ), $function, $version ) ); // phpcs:ignore
				}
			}
		}

		/**
		 * Marks a function argument as deprecated and inform when it has been used.
		 *
		 * @since 1.0.0
		 *
		 * @param string $function The function that was called.
		 * @param string $version  The version of the plugin that deprecated the argument used.
		 * @param string $message  Optional. A message regarding the change. Default null.
		 */
		public function deprecated_argument( $function, $version, $message = null ) {
			do_action( 'deprecated_argument_run', $function, $message, $version );

			if ( WP_DEBUG && apply_filters( 'deprecated_argument_trigger_error', true ) ) {
				if ( ! is_null( $message ) ) {
					trigger_error( sprintf( $this->get_translation( 'deprecated_argument' ), $function, $version, $message ) ); // phpcs:ignore
				} else {
					trigger_error( sprintf( $this->get_translation( 'deprecated_argument_no_alt' ), $function, $version ) ); // phpcs:ignore
				}
			}
		}

		/**
		 * Marks a deprecated action or filter hook as deprecated and throws a notice.
		 *
		 * @since 1.0.0
		 *
		 * @param string $hook        The hook that was used.
		 * @param string $version     The version of the plugin that deprecated the hook.
		 * @param string $replacement Optional. The hook that should have been used.
		 * @param string $message     Optional. A message regarding the change.
		 */
		public function deprecated_hook( $hook, $version, $replacement = null, $message = null ) {
			do_action( 'deprecated_hook_run', $hook, $replacement, $version, $message );

			if ( WP_DEBUG && apply_filters( 'deprecated_hook_trigger_error', true ) ) {
				$message = empty( $message ) ? '' : ' ' . $message;

				if ( ! is_null( $replacement ) ) {
					trigger_error( sprintf( $this->get_translation( 'deprecated_hook' ), $hook, $version, $replacement ) . $message ); // phpcs:ignore
				} else {
					trigger_error( sprintf( $this->get_translation( 'deprecated_hook_no_alt' ), $hook, $version ) . $message ); // phpcs:ignore
				}
			}
		}

		/**
		 * Marks something as being incorrectly called.
		 *
		 * @since 1.0.0
		 *
		 * @param string $function The function that was called.
		 * @param string $message  A message explaining what has been done incorrectly.
		 * @param string $version  The version of the plugin where the message was added.
		 */
		public function doing_it_wrong( $function, $message, $version ) {
			do_action( 'doing_it_wrong_run', $function, $message, $version );

			if ( WP_DEBUG && apply_filters( 'doing_it_wrong_trigger_error', true ) ) {
				if ( is_null( $version ) ) {
					$version = '';
				} else {
					$version = ' ' . sprintf( $this->get_translation( 'added_in_version' ), $version );
				}

				trigger_error( sprintf( $this->get_translation( 'called_incorrectly' ), $function, $message . $version ) ); // phpcs:ignore
			}
		}

		/**
		 * Marks missing services passed to a service.
		 *
		 * @since 1.0.0
		 *
		 * @param string $function      The function that was called.
		 * @param array  $service_names The names of the services that are missing.
		 */
		public function missing_services( $function, $service_names ) {
			$this->doing_it_wrong( $function, sprintf( $this->get_translation( 'missing_services' ), implode( ', ', $service_names ) ), null );
		}

		/**
		 * Returns the base handler instance.
		 *
		 * This method should not be used outside of the library,
		 * as the base handler is necessary for fallback notices.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @return Leaves_And_Love\Plugin_Lib\Error_Handler Base handler instance.
		 */
		public static function get_base_handler() {
			static $base_handler = null;

			if ( null === $base_handler ) {
				$base_handler = new Error_Handler( '', new Translations_Base_Error_Handler() );
			}

			return $base_handler;
		}
	}

endif;
