<?php
/**
 * Admin pages manager class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Components;

use Leaves_And_Love\Plugin_Lib\Service;
use Leaves_And_Love\Plugin_Lib\Traits\Container_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Traits\Hook_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Assets;
use Leaves_And_Love\Plugin_Lib\AJAX;
use Leaves_And_Love\Plugin_Lib\Error_Handler;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Components\Admin_Pages' ) ) :

	/**
	 * Class for the Admin Pages API
	 *
	 * This class manages admin pages.
	 *
	 * @since 1.0.0
	 */
	class Admin_Pages extends Service {
		use Container_Service_Trait, Hook_Service_Trait;

		/**
		 * Added admin pages.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $pages = array();

		/**
		 * Hook suffixes of the added admin pages.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $hook_suffix_map = array();

		/**
		 * Assets service definition.
		 *
		 * @since 1.0.0
		 * @static
		 * @var string
		 */
		protected static $service_assets = Assets::class;

		/**
		 * AJAX service definition.
		 *
		 * @since 1.0.0
		 * @static
		 * @var string
		 */
		protected static $service_ajax = AJAX::class;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prefix   The prefix for all shortcodes.
		 * @param array  $services {
		 *     Array of service instances.
		 *
		 *     @type Assets        $assets        The Assets API instance.
		 *     @type AJAX          $ajax          The AJAX API instance.
		 *     @type Error_Handler $error_handler The error handler instance.
		 * }
		 */
		public function __construct( $prefix, $services ) {
			$this->set_prefix( $prefix );
			$this->set_services( $services );

			$this->setup_hooks();
		}

		/**
		 * Adds an admin page.
		 *
		 * This method must be called before the 'admin_menu' action.
		 *
		 * @since 1.0.0
		 *
		 * @param string            $slug                 Page slug.
		 * @param string|Admin_Page $class_name           Either the name of the class to handle the
		 *                                                page, or an already instantiated object of
		 *                                                that class.
		 * @param string|null       $parent_slug          Optional. Parent page slug. Default null.
		 * @param int|null          $position             Optional. Page position index. Default null.
		 * @param string            $administration_panel Optional. Either 'site', 'network' or 'user'.
		 *                                                Default 'site'.
		 * @param bool              $skip_menu            Optional. Whether to not add a menu or submenu
		 *                                                item. Default false.
		 * @return bool True on success, false on failure.
		 */
		public function add( $slug, $class_name, $parent_slug = null, $position = null, $administration_panel = 'site', $skip_menu = false ) {
			if ( ! is_subclass_of( $class_name, 'Leaves_And_Love\Plugin_Lib\Components\Admin_Page' ) ) {
				return false;
			}

			if ( ! in_array( $administration_panel, array( 'site', 'network', 'user' ), true ) ) {
				return false;
			}

			if ( $this->exists( $slug, $administration_panel ) ) {
				return false;
			}

			$slug = $this->get_prefix() . $slug;

			if ( is_object( $class_name ) ) {
				$page = $class_name;
				if ( $page->slug !== $slug ) {
					return false;
				}
			} else {
				$page = new $class_name( $slug, $this );
			}

			if ( $parent_slug && isset( $this->pages[ $administration_panel ][ $this->get_prefix() . $parent_slug ] ) ) {
				$parent_slug = $this->get_prefix() . $parent_slug;
			}

			$page->administration_panel = $administration_panel;
			$page->parent_slug          = $parent_slug;
			$page->position             = $position;
			$page->skip_menu            = $skip_menu;

			if ( ! isset( $this->pages[ $administration_panel ] ) ) {
				$this->pages[ $administration_panel ] = array();
			}

			if ( ! isset( $this->hook_suffix_map[ $administration_panel ] ) ) {
				$this->hook_suffix_map[ $administration_panel ] = array();
			}

			$this->pages[ $administration_panel ][ $slug ] = $page;

			return true;
		}

		/**
		 * Gets a specific admin page.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug                 Page slug.
		 * @param string $administration_panel Optional. Either 'site', 'network' or 'user'.
		 *                                     Default 'site'.
		 * @return Admin_Page Admin page instance, or null if it does not exist.
		 */
		public function get( $slug, $administration_panel = 'site' ) {
			if ( ! $this->exists( $slug, $administration_panel ) ) {
				return null;
			}

			$slug = $this->get_prefix() . $slug;

			return $this->pages[ $administration_panel ][ $slug ];
		}

		/**
		 * Checks whether a specific admin page exists.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug                 Page slug.
		 * @param string $administration_panel Optional. Either 'site', 'network' or 'user'.
		 *                                     Default 'site'.
		 * @return bool True if the admin page exists, false otherwise.
		 */
		public function exists( $slug, $administration_panel = 'site' ) {
			$slug = $this->get_prefix() . $slug;

			return isset( $this->pages[ $administration_panel ][ $slug ] );
		}

		/**
		 * Removes an admin page.
		 *
		 * This method must be called before the 'admin_menu' action.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug                 Page slug.
		 * @param string $administration_panel Optional. Either 'site', 'network' or 'user'.
		 *                                     Default 'site'.
		 * @return bool True on success, false on failure.
		 */
		public function remove( $slug, $administration_panel = 'site' ) {
			if ( ! $this->exists( $slug, $administration_panel ) ) {
				return false;
			}

			$slug = $this->get_prefix() . $slug;

			unset( $this->pages[ $administration_panel ][ $slug ] );

			return true;
		}

		/**
		 * Adds all the available admin pages to the WordPress menu.
		 *
		 * @since 1.0.0
		 */
		protected function add_pages() {
			$administration_panel = 'site';
			if ( is_network_admin() ) {
				$administration_panel = 'network';
			} elseif ( is_user_admin() ) {
				$administration_panel = 'user';
			}

			if ( ! isset( $this->pages[ $administration_panel ] ) ) {
				return;
			}

			foreach ( $this->pages[ $administration_panel ] as $slug => $page ) {
				$callback = 'add_menu_page';
				$args     = array(
					$page->title,
					$page->menu_title,
					$page->capability,
					$slug,
					array( $page, 'render' ),
				);

				if ( $page->parent_slug ) {
					$callback = 'add_submenu_page';
					array_unshift( $args, $page->parent_slug );
				} elseif ( $page->skip_menu ) {
					$callback = 'add_submenu_page';
					array_unshift( $args, null );
				} else {
					$args[] = $page->icon_url;
					$args[] = $page->position;
				}

				$hook_suffix = call_user_func_array( $callback, $args );

				add_action( 'load-' . $hook_suffix, array( $page, 'handle_request' ), 10, 0 );

				$this->hook_suffix_map[ $administration_panel ][ $hook_suffix ] = $slug;
				$page->hook_suffix = $hook_suffix;
			}
		}

		/**
		 * Registers all available settings pages content.
		 *
		 * @since 1.0.0
		 */
		protected function register_settings() {
			$administration_panel = 'site';
			if ( is_network_admin() ) {
				$administration_panel = 'network';
			} elseif ( is_user_admin() ) {
				$administration_panel = 'user';
			}

			if ( ! isset( $this->pages[ $administration_panel ] ) ) {
				return;
			}

			foreach ( $this->pages[ $administration_panel ] as $slug => $page ) {
				if ( ! is_a( $page, Settings_Page::class ) ) {
					continue;
				}

				$page->register();
			}
		}

		/**
		 * Enqueues assets for the current admin page.
		 *
		 * @since 1.0.0
		 *
		 * @param string $hook_suffix Hook suffix of the current admin page.
		 */
		protected function enqueue_assets( $hook_suffix ) {
			$administration_panel = 'site';
			if ( is_network_admin() ) {
				$administration_panel = 'network';
			} elseif ( is_user_admin() ) {
				$administration_panel = 'user';
			}

			if ( ! isset( $this->hook_suffix_map[ $administration_panel ][ $hook_suffix ] ) ) {
				return;
			}

			$slug = $this->hook_suffix_map[ $administration_panel ][ $hook_suffix ];

			$this->pages[ $administration_panel ][ $slug ]->enqueue_assets();
		}

		/**
		 * Sets up all action and filter hooks for the service.
		 *
		 * This method must be implemented and then be called from the constructor.
		 *
		 * @since 1.0.0
		 */
		protected function setup_hooks() {
			$this->actions = array(
				array(
					'name'     => 'admin_menu',
					'callback' => array( $this, 'add_pages' ),
					'priority' => 10,
					'num_args' => 0,
				),
				array(
					'name'     => 'network_admin_menu',
					'callback' => array( $this, 'add_pages' ),
					'priority' => 10,
					'num_args' => 0,
				),
				array(
					'name'     => 'user_admin_menu',
					'callback' => array( $this, 'add_pages' ),
					'priority' => 10,
					'num_args' => 0,
				),
				array(
					'name'     => 'admin_init',
					'callback' => array( $this, 'register_settings' ),
					'priority' => 10,
					'num_args' => 0,
				),
				array(
					'name'     => 'admin_enqueue_scripts',
					'callback' => array( $this, 'enqueue_assets' ),
					'priority' => 10,
					'num_args' => 1,
				),
			);
		}
	}

endif;
