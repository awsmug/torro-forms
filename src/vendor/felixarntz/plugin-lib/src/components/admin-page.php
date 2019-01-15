<?php
/**
 * Admin page class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Components;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Components\Admin_Page' ) ) :

	/**
	 * Class for an admin page
	 *
	 * This class represents a menu page in the admin.
	 *
	 * @since 1.0.0
	 *
	 * @property string      $administration_panel Administration panel the page belongs to.
	 * @property string|null $parent_slug          Parent page slug.
	 * @property int         $position             Page position index.
	 * @property bool        $skip_menu            Whether to not add a menu or submenu item for the page.
	 * @property string      $hook_suffix          Page hook suffix.
	 *
	 * @property-read string $slug       Page slug.
	 * @property-read string $title      Page title.
	 * @property-read string $capability Required capability to access the page.
	 * @property-read string $icon_url   Icon URL for the page.
	 * @property-read string $url        URL to the page.
	 */
	abstract class Admin_Page {
		/**
		 * Page slug.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $slug = '';

		/**
		 * Page title.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $title = '';

		/**
		 * Menu title.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $menu_title = '';

		/**
		 * Required capability to access the page.
		 *
		 * May be an array if a hierarchy of fallback capabilities should be used.
		 *
		 * @since 1.0.0
		 * @var string|array
		 */
		protected $capability = '';

		/**
		 * Icon URL for the page.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $icon_url = '';

		/**
		 * Administration panel the page belongs to.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $administration_panel = 'site';

		/**
		 * Parent page slug.
		 *
		 * @since 1.0.0
		 * @var string|null
		 */
		protected $parent_slug = null;

		/**
		 * Page position index.
		 *
		 * @since 1.0.0
		 * @var int
		 */
		protected $position = null;

		/**
		 * Whether to not add a menu or submenu item for the page.
		 *
		 * @since 1.0.0
		 * @var bool
		 */
		protected $skip_menu = false;

		/**
		 * Page hook suffix.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $hook_suffix = '';

		/**
		 * URL to the page.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $url = '';

		/**
		 * Parent manager for admin pages.
		 *
		 * @since 1.0.0
		 * @var Admin_Pages
		 */
		protected $manager = null;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string      $slug    Page slug.
		 * @param Admin_Pages $manager Admin page manager instance.
		 */
		public function __construct( $slug, $manager ) {
			$this->slug    = $slug;
			$this->manager = $manager;

			if ( ! empty( $this->title ) && empty( $this->menu_title ) ) {
				$this->menu_title = $this->title;
			}
		}

		/**
		 * Handles a request to the page.
		 *
		 * @since 1.0.0
		 */
		public function handle_request() {
			// Empty method body.
		}

		/**
		 * Enqueues assets to load on the page.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_assets() {
			// Empty method body.
		}

		/**
		 * Renders the page content.
		 *
		 * @since 1.0.0
		 */
		abstract public function render();

		/**
		 * Magic isset-er.
		 *
		 * Checks whether a property is set.
		 *
		 * @since 1.0.0
		 *
		 * @param string $property Property to check for.
		 * @return bool True if the property is set, false otherwise.
		 */
		public function __isset( $property ) {
			return in_array( $property, $this->get_read_properties(), true );
		}

		/**
		 * Magic getter.
		 *
		 * Returns a property value.
		 *
		 * @since 1.0.0
		 *
		 * @param string $property Property to get.
		 * @return mixed Property value, or null if property is not set.
		 */
		public function __get( $property ) {
			if ( ! in_array( $property, $this->get_read_properties(), true ) ) {
				return null;
			}

			if ( 'capability' === $property && is_array( $this->capability ) ) {
				$page_capability = '';

				foreach ( $this->capability as $capability ) {
					$page_capability = $capability;
					if ( current_user_can( $capability ) ) {
						break;
					}
				}

				return $page_capability;
			}

			return $this->$property;
		}

		/**
		 * Magic setter.
		 *
		 * Sets a property value.
		 *
		 * @since 1.0.0
		 *
		 * @param string $property Property to set.
		 * @param mixed  $value    Property value.
		 */
		public function __set( $property, $value ) {
			if ( ! in_array( $property, $this->get_write_properties(), true ) ) {
				return;
			}

			$this->$property = $value;

			if ( in_array( $property, array( 'slug', 'parent_slug', 'skip_menu', 'administration_panel' ), true ) ) {
				$this->update_url();
			}
		}

		/**
		 * Sets the URL to the admin page based on other class properties.
		 *
		 * The URL can be retrieved by accessing the class property $url.
		 *
		 * @since 1.0.0
		 */
		protected function update_url() {
			$parent_file = 'admin.php';

			if ( $this->parent_slug ) {
				if ( false !== strpos( $this->parent_slug, '?' ) ) {
					list( $base_slug, $query ) = explode( '?', $this->parent_slug, 2 );
				} else {
					$base_slug = $this->parent_slug;
				}

				if ( '.php' === substr( $base_slug, -4 ) ) {
					$parent_file = $this->parent_slug;
				}
			}

			$base_url = '';
			switch ( $this->administration_panel ) {
				case 'user':
					$base_url = user_admin_url( $parent_file );
					break;
				case 'network':
					$base_url = network_admin_url( $parent_file );
					break;
				default:
					$base_url = admin_url( $parent_file );
			}

			$this->url = add_query_arg( 'page', $this->slug, $base_url );
		}

		/**
		 * Checks whether the current user can access the page.
		 *
		 * @since 1.0.0
		 *
		 * @return bool True if the current user can access the page, false otherwise.
		 */
		protected function current_user_can() {
			if ( is_array( $this->capability ) ) {
				$has_cap = false;
				foreach ( $this->capability as $capability ) {
					if ( current_user_can( $capability ) ) {
						$has_cap = true;
						break;
					}
				}

				return $has_cap;
			}

			return current_user_can( $this->capability );
		}

		/**
		 * Gets the names of properties with read-access.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of property names.
		 */
		protected function get_read_properties() {
			return array( 'slug', 'title', 'menu_title', 'capability', 'icon_url', 'position', 'skip_menu', 'administration_panel', 'parent_slug', 'hook_suffix', 'url' );
		}

		/**
		 * Gets the names of properties with write-access.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of property names.
		 */
		protected function get_write_properties() {
			return array( 'administration_panel', 'parent_slug', 'position', 'skip_menu', 'hook_suffix' );
		}
	}

endif;
