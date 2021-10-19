<?php
/**
 * Shortcodes abstraction class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Components;

use Leaves_And_Love\Plugin_Lib\Service;
use Leaves_And_Love\Plugin_Lib\Traits\Container_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Cache;
use Leaves_And_Love\Plugin_Lib\Template;
use Leaves_And_Love\Plugin_Lib\Error_Handler;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Components\Shortcodes' ) ) :

	/**
	 * Class for Shortcodes API
	 *
	 * The class is a wrapper for the WordPress Shortcodes API.
	 *
	 * @since 1.0.0
	 *
	 * @method Leaves_And_Love\Plugin_Lib\Cache    cache()
	 * @method Leaves_And_Love\Plugin_Lib\Template template()
	 */
	class Shortcodes extends Service {
		use Container_Service_Trait;

		/**
		 * Added shortcodes.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $shortcode_tags = array();

		/**
		 * Cache service definition.
		 *
		 * @since 1.0.0
		 * @static
		 * @var string
		 */
		protected static $service_cache = Cache::class;

		/**
		 * Template service definition.
		 *
		 * @since 1.0.0
		 * @static
		 * @var string
		 */
		protected static $service_template = Template::class;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prefix   The prefix for all shortcodes.
		 * @param array  $services {
		 *     Array of service instances.
		 *
		 *     @type Cache         $cache         The Cache API instance.
		 *     @type Template      $template      The Template API instance.
		 *     @type Error_Handler $error_handler The error handler instance.
		 * }
		 */
		public function __construct( $prefix, $services ) {
			$this->set_prefix( $prefix );
			$this->set_services( $services );
		}

		/**
		 * Adds a shortcode tag.
		 *
		 * Compared to regular WordPress shortcodes, the callback will receive an additional fourth parameter,
		 * the Template API instance. This allows the shortcode to use it for rendering.
		 *
		 * The shortcode tag will automatically be prefixed with the plugin-wide prefix.
		 *
		 * @since 1.0.0
		 *
		 * @param string       $tag  Shortcode tag to be searched in content.
		 * @param callable     $func Hook to run when shortcode is found.
		 * @param array|string $args {
		 *     Array or string of additional shortcode arguments.
		 *
		 *     @type callable $enqueue_callback Function to enqueue scripts and stylesheets this shortcode requires.
		 *                                      Default null.
		 *     @type array    $defaults         Array of default attribute values. If passed, the shortcode attributes
		 *                                      will be parsed with these before executing the callback hook so that
		 *                                      you do not need to take care of that in the shortcode hook. Default
		 *                                      false.
		 *     @type bool     $cache            Whether to cache the output of this shortcode. Default false.
		 *     @type int      $cache_expiration Time in seconds for which the shortcode should be cached. This only
		 *                                      takes effect if $cache is true. Default is 86400 (one day).
		 * }
		 * @return bool True on success, false on failure.
		 */
		public function add( $tag, $func, $args = array() ) {
			if ( empty( $tag ) ) {
				return false;
			}

			$tag = $this->get_prefix() . $tag;

			$this->shortcode_tags[ $tag ] = new Shortcode( $tag, $func, $args, $this );
			add_shortcode( $tag, array( $this->shortcode_tags[ $tag ], 'run' ) );

			return true;
		}

		/**
		 * Checks whether a specific shortcode tag exists.
		 *
		 * @since 1.0.0
		 *
		 * @param string $tag Shortcode tag to check for.
		 * @return bool True if the shortcode tag exists, otherwise false.
		 */
		public function has( $tag ) {
			$tag = $this->get_prefix() . $tag;

			return isset( $this->shortcode_tags[ $tag ] ) && shortcode_exists( $tag );
		}

		/**
		 * Retrieves a shortcode object.
		 *
		 * @since 1.0.0
		 *
		 * @param string $tag Shortcode tag to retrieve object for.
		 * @return Shortcode|null Shortcode object, or null if not exists.
		 */
		public function get( $tag ) {
			if ( ! $this->has( $tag ) ) {
				return null;
			}

			$tag = $this->get_prefix() . $tag;

			return $this->shortcode_tags[ $tag ];
		}

		/**
		 * Removes a shortcode tag.
		 *
		 * @since 1.0.0
		 *
		 * @param string $tag Shortcode tag to remove.
		 * @return bool True on success, false on failure.
		 */
		public function remove( $tag ) {
			$tag = $this->get_prefix() . $tag;

			if ( ! isset( $this->shortcode_tags[ $tag ] ) ) {
				return false;
			}

			remove_shortcode( $tag );
			unset( $this->shortcode_tags[ $tag ] );

			return true;
		}
	}

endif;
