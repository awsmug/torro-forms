<?php
/**
 * Shortcode class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Components;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Components\Shortcode' ) ) :

	/**
	 * Class for a shortcode
	 *
	 * This class represents a shortcode.
	 *
	 * @since 1.0.0
	 */
	final class Shortcode {
		/**
		 * Shortcode tag.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		private $tag;

		/**
		 * Hook to run for the shortcode.
		 *
		 * @since 1.0.0
		 * @var callable
		 */
		private $func;

		/**
		 * Additional arguments for this shortcode.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		private $args;

		/**
		 * Shortcode manager instance.
		 *
		 * @since 1.0.0
		 * @var Shortcodes
		 */
		private $manager;

		/**
		 * Whether assets for this shortcode have been enqueued.
		 *
		 * @since 1.0.0
		 * @var bool
		 */
		private $enqueued = false;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string       $tag     Shortcode tag to be searched in content.
		 * @param callable     $func    Hook to run when shortcode is found.
		 * @param array|string $args    {
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
		 * @param Shortcodes   $manager The shortcode manager instance.
		 */
		public function __construct( $tag, $func, $args, $manager ) {
			$this->tag  = $tag;
			$this->func = $func;
			$this->args = wp_parse_args(
				$args,
				array(
					'enqueue_callback' => null,
					'defaults'         => false,
					'cache'            => false,
					'cache_expiration' => DAY_IN_SECONDS,
				)
			);

			$this->manager = $manager;
		}

		/**
		 * Runs the shortcode hook for given attributes and content.
		 *
		 * @since 1.0.0
		 *
		 * @param array       $atts    Attributes to pass to the hook.
		 * @param string|null $content Content wrapped by the shortcode, or null if a self-contained shortcode.
		 * @return string The rendered shortcode output.
		 */
		public function run( $atts, $content ) {
			$cache_key = false;

			if ( $this->args['cache'] ) {
				$cache_key = $this->get_cache_key( $atts, $content );

				$cached = $this->manager->cache()->get( $cache_key, 'shortcodes' );
				if ( false !== $cached ) {
					return $cached;
				}
			}

			if ( is_array( $this->args['defaults'] ) ) {
				$atts = shortcode_atts( $this->args['defaults'], $atts, $this->tag );
			}

			$output = call_user_func( $this->func, $atts, $content, $this->tag, $this->manager->template() );

			if ( $cache_key ) {
				$this->manager->cache()->set( $cache_key, $output, 'shortcodes', absint( $this->args['cache_expiration'] ) );
			}

			return $output;
		}

		/**
		 * Returns the tag name of the shortcode.
		 *
		 * @since 1.0.0
		 *
		 * @return string Shortcode tag.
		 */
		public function get_tag() {
			return $this->tag;
		}

		/**
		 * Runs the enqueue callback if one exists.
		 *
		 * This method will ensure that the callback will only be called once per script lifetime.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_assets() {
			if ( $this->enqueued ) {
				return;
			}

			$this->enqueued = true;

			if ( ! $this->has_enqueue_callback() ) {
				return;
			}

			call_user_func( $this->args['enqueue_callback'] );
		}

		/**
		 * Checks whether an enqueue callback exists for this shortcode.
		 *
		 * @since 1.0.0
		 *
		 * @return bool True if an enqueue callback exists, otherwise false.
		 */
		public function has_enqueue_callback() {
			return null !== $this->args['enqueue_callback'] && is_callable( $this->args['enqueue_callback'] );
		}

		/**
		 * Creates a cache key from given attributes and content input.
		 *
		 * @since 1.0.0
		 *
		 * @param array       $atts    Attributes passed to the shortcode hook.
		 * @param string|null $content Content wrapped by the shortcode, or null if self-contained.
		 * @return string The cache key created from the input.
		 */
		private function get_cache_key( $atts, $content ) {
			if ( null !== $content ) {
				$atts['__content'] = $content;
			}

			return $this->tag . ':' . md5( serialize( $atts ) ); // phpcs:ignore
		}
	}

endif;
