<?php
/**
 * Assets manager class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib;

use Leaves_And_Love\Plugin_Lib\Traits\Args_Service_Trait;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Assets' ) ) :

	/**
	 * Class for managing assets.
	 *
	 * @since 1.0.0
	 */
	class Assets extends Service {
		use Args_Service_Trait;

		/**
		 * Internal lookup for third party scripts.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $third_party_scripts = array();

		/**
		 * Internal lookup for third party stylesheets.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $third_party_styles = array();

		/**
		 * Assets instance for the library itself.
		 *
		 * @since 1.0.0
		 * @static
		 * @var Leaves_And_Love\Plugin_Lib\Assets
		 */
		protected static $library_instance = null;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
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
			$this->set_prefix( $prefix );
			$this->set_args( $args );
		}

		/**
		 * Registers a script.
		 *
		 * @since 1.0.0
		 *
		 * @param string $handle Script handle.
		 * @param string $src    Relative path to the script from the plugin's base directory.
		 * @param array  $args   {
		 *     Optional. Array of additional arguments.
		 *
		 *     @type array       $deps          Array of registered script handles the script depends on.
		 *                                      Default empty array.
		 *     @type string|bool $ver           Script version, or false to ignore. Default false.
		 *     @type bool        $in_footer     Whether to load this script in the footer. Default false.
		 *     @type bool        $enqueue       Whether to immediately enqueue the script. Default false.
		 *     @type string|null $localize_name Object name for localization data, if necessary. Default
		 *                                      null.
		 *     @type array       $localize_data Localization data for the script. Only valid if an object
		 *                                      name is specified in $localize_name. Default empty array.
		 * }
		 */
		public function register_script( $handle, $src, $args = array() ) {
			$args = wp_parse_args(
				$args,
				array(
					'deps'          => array(),
					'ver'           => false,
					'in_footer'     => false,
					'enqueue'       => false,
					'localize_name' => null,
					'localize_data' => array(),
				)
			);

			$handle = $this->check_handle( $handle, $src );
			$src    = $this->get_full_url( $src );

			wp_register_script( $handle, $src, $args['deps'], $args['ver'], $args['in_footer'] );

			if ( $args['localize_name'] ) {
				wp_localize_script( $handle, $args['localize_name'], $args['localize_data'] );
			}

			if ( $args['enqueue'] ) {
				wp_enqueue_script( $handle );
			}
		}

		/**
		 * Enqueues a previously registered script.
		 *
		 * @since 1.0.0
		 *
		 * @param string $handle Script handle.
		 */
		public function enqueue_script( $handle ) {
			if ( ! in_array( $handle, $this->third_party_scripts, true ) ) {
				$handle = $this->prefix_handle( $handle );
			}

			wp_enqueue_script( $handle );
		}

		/**
		 * Registers a stylesheet.
		 *
		 * @since 1.0.0
		 *
		 * @param string $handle Stylesheet handle.
		 * @param string $src    Relative path to the stylesheet from the plugin's base directory.
		 * @param array  $args   {
		 *     Optional. Array of additional arguments.
		 *
		 *     @type array       $deps    Array of registered stylesheet handles the stylesheet
		 *                                depends on. Default empty array.
		 *     @type string|bool $ver     Stylesheet version, or false to ignore. Default false.
		 *     @type string      $media   The media for which the stylesheet has been defined. Default
		 *                                'all'.
		 *     @type bool        $enqueue Whether to immediately enqueue the stylesheet. Default false.
		 * }
		 */
		public function register_style( $handle, $src, $args = array() ) {
			$args = wp_parse_args(
				$args,
				array(
					'deps'    => array(),
					'ver'     => false,
					'media'   => 'all',
					'enqueue' => false,
				)
			);

			$handle = $this->check_handle( $handle, $src );
			$src    = $this->get_full_url( $src );

			wp_register_style( $handle, $src, $args['deps'], $args['ver'], $args['media'] );

			if ( $args['enqueue'] ) {
				wp_enqueue_style( $handle );
			}
		}

		/**
		 * Enqueues a previously registered stylesheet.
		 *
		 * @since 1.0.0
		 *
		 * @param string $handle Stylesheet handle.
		 */
		public function enqueue_style( $handle ) {
			if ( ! in_array( $handle, $this->third_party_styles, true ) ) {
				$handle = $this->prefix_handle( $handle );
			}

			wp_enqueue_style( $handle );
		}

		/**
		 * Checks whether a specific asset file exists in the filesystem.
		 *
		 * @since 1.0.0
		 *
		 * @param string $src Relative asset path.
		 * @return bool True if the file exists, false otherwise.
		 */
		public function file_exists( $src ) {
			$full_path = $this->get_full_path( $src );
			if ( ! $full_path ) {
				// Assume that files at external locations exist.
				return true;
			}

			// If the file name was changed, existence of the file has already been verified.
			if ( strlen( $full_path ) - strlen( $src ) !== strpos( $full_path, $src ) ) {
				return true;
			}

			return file_exists( $full_path );
		}

		/**
		 * Checks an asset handle and possibly prefixes it.
		 *
		 * Only assets that are recognized as third-party assets are not prefixed.
		 *
		 * @since 1.0.0
		 *
		 * @param string $handle Asset handle.
		 * @param string $src    Relative asset handle.
		 * @return string Possibly prefixed asset handle.
		 */
		protected function check_handle( $handle, $src ) {
			$vendor_directory_names = implode( '|', array( 'vendor', 'node_modules', 'bower_components' ) );

			if ( ( preg_match( '/^(http|https):\/\//', $src ) || 0 === strpos( $src, '//' ) ) && ! strpos( $src, home_url() ) ) {
				$property_name = '.css' === substr( $src, -4 ) ? 'third_party_styles' : 'third_party_scripts';
				array_push( $this->$property_name, $handle );
				return $handle;
			}

			if ( preg_match( "/(^|\/)($vendor_directory_names)\//", $src ) ) {
				$property_name = '.css' === substr( $src, -4 ) ? 'third_party_styles' : 'third_party_scripts';
				array_push( $this->$property_name, $handle );
				return $handle;
			}

			return $this->prefix_handle( $handle );
		}

		/**
		 * Prefixes a handle.
		 *
		 * @since 1.0.0
		 *
		 * @param string $handle Asset handle.
		 * @return string Prefixed asset handle.
		 */
		protected function prefix_handle( $handle ) {
			return str_replace( '_', '-', $this->get_prefix() ) . $handle;
		}

		/**
		 * Transforms a relative asset path into a full URL.
		 *
		 * The method also automatically handles loading a minified vs non-minified file.
		 *
		 * @since 1.0.0
		 *
		 * @param string $src Relative asset path.
		 * @return string|bool Full asset URL, or false if the path
		 *                     is requested for a full $src URL.
		 */
		protected function get_full_url( $src ) {
			return $this->get_full_path( $src, true );
		}

		/**
		 * Transforms a relative asset path into a full path.
		 *
		 * The method also automatically handles loading a minified vs non-minified file.
		 *
		 * @since 1.0.0
		 *
		 * @param string $src Relative asset path.
		 * @param bool   $url Whether to return the URL instead of the path. Default false.
		 * @return string|bool Full asset path or URL, depending on the $url parameter, or false
		 *                     if the path is requested for a full $src URL.
		 */
		protected function get_full_path( $src, $url = false ) {
			if ( preg_match( '/^(http|https):\/\//', $src ) || 0 === strpos( $src, '//' ) ) {
				if ( $url ) {
					return $src;
				}

				return false;
			}

			$extension = '';
			if ( false !== strpos( $src, '.' ) ) {
				$parts     = explode( '.', $src );
				$extension = '.' . $parts[ count( $parts ) - 1 ];
			}

			$min_extension = '.min' . $extension;

			$extension_length     = strlen( $extension );
			$min_extension_length = $extension_length + 4;

			if ( substr( $src, - $min_extension_length ) === $min_extension && defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$uncompressed = substr( $src, 0, - $min_extension_length ) . $extension;
				if ( file_exists( call_user_func( $this->path_callback, $uncompressed ) ) ) {
					$src = $uncompressed;
				}
			} elseif ( substr( $src, - $min_extension_length ) !== $min_extension && ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) ) {
				$compressed = substr( $src, 0, - $extension_length ) . $min_extension;
				if ( file_exists( call_user_func( $this->path_callback, $compressed ) ) ) {
					$src = $compressed;
				}
			}

			if ( $url ) {
				return call_user_func( $this->url_callback, $src );
			}

			return call_user_func( $this->path_callback, $src );
		}

		/**
		 * Returns the assets instance for the library itself.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @return Leaves_And_Love\Plugin_Lib\Assets The library assets instance.
		 */
		public static function get_library_instance() {
			if ( null === self::$library_instance ) {
				self::$library_instance = new Assets(
					'plugin_lib_',
					array(
						'path_callback' => function( $rel_path ) {
							return plugin_dir_path( dirname( __FILE__ ) ) . ltrim( $rel_path, '/' );
						},
						'url_callback'  => function( $rel_path ) {
							return plugin_dir_url( dirname( __FILE__ ) ) . ltrim( $rel_path, '/' );
						},
					)
				);
			}

			return self::$library_instance;
		}

		/**
		 * Parses the path callback.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param mixed $value The input value.
		 * @return string The parsed value.
		 */
		protected static function parse_arg_path_callback( $value ) {
			return $value;
		}

		/**
		 * Parses the URL callback.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param mixed $value The input value.
		 * @return string The parsed value.
		 */
		protected static function parse_arg_url_callback( $value ) {
			return $value;
		}
	}

endif;
