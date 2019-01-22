<?php
/**
 * Router class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib;

use Leaves_And_Love\Plugin_Lib\Traits\Hook_Service_Trait;
use WP_Query;
use WP;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Router' ) ) :

	/**
	 * Router class for frontend requests.
	 *
	 * This class allows defining custom routes that will override WordPress' default rewrite system if the
	 * current URL is affected. This class is heavily inspired by https://roots.io/routing-wp-requests/
	 *
	 * @since 1.0.0
	 */
	class Router extends Service {
		use Hook_Service_Trait;

		/**
		 * The registered routes.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $routes = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prefix The instance prefix.
		 */
		public function __construct( $prefix ) {
			$this->set_prefix( $prefix );

			$this->setup_hooks();
		}

		/**
		 * Adds a new route.
		 *
		 * @since 1.0.0
		 *
		 * @param string   $slug            Unique identifier for this route.
		 * @param string   $pattern         Regular expression to match.
		 * @param callable $map_callback    Callback to generate query variables if the request matches the pattern.
		 * @param array    $query_vars      Optional. Query variables to listen to. Default empty.
		 * @param callable $handle_callback Optional. Action callback to execute to manually handle the request and
		 *                                  bypass WP_Query. Default none.
		 */
		public function add_route( $slug, $pattern, $map_callback, $query_vars = array(), $handle_callback = null ) {
			$this->routes[ $slug ] = array(
				'pattern'         => $pattern,
				'map_callback'    => $map_callback,
				'handle_callback' => $handle_callback,
				'query_vars'      => $query_vars,
			);
		}

		/**
		 * Returns the current request URL.
		 *
		 * @since 1.0.0
		 *
		 * @return string The request URL.
		 */
		public function get_current_request_url() {
			$current_url = trim( esc_url_raw( add_query_arg( array() ) ), '/' );
			$home_path   = trim( wp_parse_url( home_url(), PHP_URL_PATH ), '/' );

			if ( $home_path && 0 === strpos( $current_url, $home_path ) ) {
				$current_url = trim( substr( $current_url, strlen( $home_path ) ), '/' );
			}

			return $current_url;
		}

		/**
		 * Routes the request if one of the registered routes is matched.
		 *
		 * @since 1.0.0
		 *
		 * @param bool  $parse_request    Whether WordPress should parse the request.
		 * @param WP    $wp               The WordPress main class instance.
		 * @param array $extra_query_vars Extra passed query variables.
		 * @return bool Whether WordPress should parse the request.
		 */
		protected function maybe_route_request( $parse_request, $wp, $extra_query_vars ) {
			if ( is_admin() || ! $parse_request ) {
				return $parse_request;
			}

			if ( ! is_array( $extra_query_vars ) && ! empty( $extra_query_vars ) ) {
				$extra_query_vars_str = $extra_query_vars;
				$extra_query_vars     = array();
				parse_str( $extra_query_vars_str, $extra_query_vars );
			}

			$routes          = $this->routes;
			$query_vars      = null;
			$handle_callback = null;
			$url_path        = '';

			if ( '' !== (string) get_option( 'permalink_structure' ) ) {
				uasort( $routes, array( $this, 'sort_callback_pattern_length' ) );

				$current_url = $this->get_current_request_url();

				$url_parts = explode( '?', $current_url, 2 );
				$url_path  = trim( $url_parts[0], '/' );

				foreach ( $routes as $route ) {
					if ( empty( $route['pattern'] ) || empty( $route['map_callback'] ) ) {
						continue;
					}

					if ( preg_match( '~' . trim( $route['pattern'], '/' ) . '~', $url_path, $matches ) ) {
						$query_vars = call_user_func( $route['map_callback'], $matches );

						if ( is_array( $query_vars ) ) {
							foreach ( $route['query_vars'] as $query_var ) {
								if ( isset( $extra_query_vars[ $query_var ] ) ) {
									$query_vars[ $query_var ] = $extra_query_vars[ $query_var ];
								} else {
									$query_var_value = filter_input( INPUT_POST, $query_var );
									if ( empty( $query_var_value ) ) {
										$query_var_value = filter_input( INPUT_GET, $query_var );
									}

									if ( ! empty( $query_var_value ) ) {
										$query_vars[ $query_var ] = $query_var_value;
									}
								}
							}
						}

						$handle_callback = $route['handle_callback'];

						break;
					}
				}
			} else {
				uasort( $routes, array( $this, 'sort_callback_query_vars_length' ) );

				foreach ( $routes as $route ) {
					if ( empty( $route['query_vars'] ) ) {
						continue;
					}

					$query_vars = array();
					foreach ( $route['query_vars'] as $query_var ) {
						if ( isset( $extra_query_vars[ $query_var ] ) ) {
							$query_vars[ $query_var ] = $extra_query_vars[ $query_var ];
						} else {
							$query_var_value = filter_input( INPUT_POST, $query_var );
							if ( empty( $query_var_value ) ) {
								$query_var_value = filter_input( INPUT_GET, $query_var );
							}

							if ( ! empty( $query_var_value ) ) {
								$query_vars[ $query_var ] = $query_var_value;
							} else {
								$query_vars = null;
								break;
							}
						}
					}

					if ( is_array( $query_vars ) ) {
						$handle_callback = $route['handle_callback'];
						break;
					}
				}
			}

			if ( ! is_array( $query_vars ) ) {
				return $parse_request;
			}

			if ( ! empty( $handle_callback ) ) {
				$wp->query_vars = array();

				$success = call_user_func( $handle_callback, $query_vars );

				$this->add_filter( 'posts_pre_query', array( $this, 'override_main_query' ), 1, 2 );
				if ( $success ) {
					$this->add_filter( 'pre_handle_404', array( $this, 'send_200_header' ), 1, 2 );
				} else {
					$this->add_filter( 'pre_handle_404', array( $this, 'send_404_header' ), 1, 2 );
				}
			} else {
				$wp->request          = $url_path;
				$wp->query_vars       = $query_vars;
				$wp->extra_query_vars = $extra_query_vars;
			}

			$this->remove_action( 'wp_head', 'feed_links_extra', 3 );
			$this->remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
			$this->remove_filter( 'wp_head', 'rel_canonical', 10 );
			$this->remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
			$this->remove_action( 'template_redirect', 'redirect_canonical', 10 );

			return false;
		}

		/**
		 * Filter that short-circuits the main query, returning an empty result.
		 *
		 * @since 1.0.0
		 *
		 * @param null|array $posts    Query results to set. If null, the SQL query will be executed.
		 * @param WP_Query   $wp_query Current query instance.
		 * @return array Empty array if the current query is the main query, null otherwise.
		 */
		protected function override_main_query( $posts, $wp_query ) {
			if ( ! $wp_query->is_main_query() ) {
				return $posts;
			}

			$wp_query->found_posts   = 0;
			$wp_query->max_num_pages = 0;

			$wp_query->is_page     = false;
			$wp_query->is_singular = false;
			$wp_query->is_home     = false;
			$wp_query->is_archive  = false;

			return array();
		}

		/**
		 * Filter to short-circuit the `WP::handle_404()` method and send a 200 status header.
		 *
		 * @since 1.0.0
		 *
		 * @param bool     $handle_404 Whether to short-circuit the 404 handler.
		 * @param WP_Query $wp_query   Current query instance.
		 * @return true True to short-circuit the 404 handler.
		 */
		protected function send_200_header( $handle_404, $wp_query ) {
			if ( is_404() ) {
				return true;
			}

			status_header( 200 );

			return true;
		}

		/**
		 * Filter to short-circuit the `WP::handle_404()` method and send a 404 status header.
		 *
		 * @since 1.0.0
		 *
		 * @param bool     $handle_404 Whether to short-circuit the 404 handler.
		 * @param WP_Query $wp_query   Current query instance.
		 * @return true True to short-circuit the 404 handler.
		 */
		protected function send_404_header( $handle_404, $wp_query ) {
			if ( is_404() ) {
				return true;
			}

			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();

			return true;
		}

		/**
		 * Sorts routes by their pattern length in descending order.
		 *
		 * @since 1.0.0
		 *
		 * @param array $a One route to compare.
		 * @param array $b The other route to compare.
		 * @return int 0 if both routes equal. -1 if second route should come first, 1 otherwise.
		 */
		protected function sort_callback_pattern_length( $a, $b ) {
			$size_a = strlen( $a['pattern'] );
			$size_b = strlen( $b['pattern'] );

			if ( $size_a === $size_b ) {
				return 0;
			}

			return $size_a < $size_b ? 1 : -1;
		}

		/**
		 * Sorts routes by their amount of raw query variables in descending order.
		 *
		 * @since 1.0.0
		 *
		 * @param array $a One route to compare.
		 * @param array $b The other route to compare.
		 * @return int 0 if both routes equal. -1 if second route should come first, 1 otherwise.
		 */
		protected function sort_callback_query_vars_length( $a, $b ) {
			$size_a = count( $a['query_vars'] );
			$size_b = count( $b['query_vars'] );

			if ( $size_a === $size_b ) {
				return 0;
			}

			return $size_a < $size_b ? 1 : -1;
		}

		/**
		 * Sets up all action and filter hooks for the service.
		 *
		 * This method must be implemented and then be called from the constructor.
		 *
		 * @since 1.0.0
		 */
		protected function setup_hooks() {
			$this->filters = array(
				array(
					'name'     => 'do_parse_request',
					'callback' => array( $this, 'maybe_route_request' ),
					'priority' => 1,
					'num_args' => 3,
				),
			);
		}
	}

endif;
