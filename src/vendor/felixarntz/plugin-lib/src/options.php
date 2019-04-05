<?php
/**
 * Options abstraction class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib;

use Leaves_And_Love\Plugin_Lib\Traits\Hook_Service_Trait;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Options' ) ) :

	/**
	 * Class for Options API
	 *
	 * The class is a wrapper for the WordPress Option API with one significant difference.
	 * It supports specific site options being stored in the network settings (if Multisite
	 * is enabled). This can be useful in some cases for various reasons.
	 *
	 * @since 1.0.0
	 */
	class Options extends Service {
		use Hook_Service_Trait;

		/**
		 * An array of options stored in network.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $network_stored = array();

		/**
		 * Constructor.
		 *
		 * This sets the option prefix and initializes the default options stored in network.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prefix The prefix for all options.
		 */
		public function __construct( $prefix ) {
			$this->set_prefix( $prefix );

			$this->setup_hooks();
		}

		/**
		 * Retrieves an option value based on the option name.
		 *
		 * @since 1.0.0
		 *
		 * @param string  $option  Name of the option to retrieve.
		 * @param boolean $default Optional. Value to return if the option doesn't exist. Default false.
		 * @return mixed Value set for the option.
		 */
		public function get( $option, $default = false ) {
			if ( $this->is_stored_in_network( $option ) && is_multisite() ) {
				$site_id = get_current_blog_id();

				$options = get_network_option( null, $this->get_prefix() . $option, array() );
				if ( ! isset( $options[ $site_id ] ) ) {
					return $default;
				}

				return $options[ $site_id ];
			}

			return get_option( $this->get_prefix() . $option, $default );
		}

		/**
		 * Adds a new option.
		 *
		 * Existing options will not be updated.
		 *
		 * @since 1.0.0
		 *
		 * @param string $option Name of the option to add.
		 * @param mixed  $value  Option value, can be anything.
		 * @return bool True if option was added, otherwise false.
		 */
		public function add( $option, $value ) {
			if ( $this->is_stored_in_network( $option ) && is_multisite() ) {
				$site_id = get_current_blog_id();

				$options = get_network_option( null, $this->get_prefix() . $option, array() );
				if ( isset( $options[ $site_id ] ) ) {
					return false;
				}

				$options[ $site_id ] = $value;

				return update_network_option( null, $this->get_prefix() . $option, $options );
			}

			return add_option( $this->get_prefix() . $option, $value );
		}

		/**
		 * Updates the value of an option that was already added.
		 *
		 * If not existing, it will be added.
		 *
		 * @since 1.0.0
		 *
		 * @param string $option Name of the option to update.
		 * @param mixed  $value  Option value, can be anything.
		 * @return bool True if option was updated, otherwise false.
		 */
		public function update( $option, $value ) {
			if ( $this->is_stored_in_network( $option ) && is_multisite() ) {
				$site_id = get_current_blog_id();

				$options             = get_network_option( null, $this->get_prefix() . $option, array() );
				$options[ $site_id ] = $value;

				return update_network_option( null, $this->get_prefix() . $option, $options );
			}

			return update_option( $this->get_prefix() . $option, $value );
		}

		/**
		 * Removes an option by name.
		 *
		 * Returns false if option was already removed.
		 *
		 * @since 1.0.0
		 *
		 * @param string $option Name of the option to remove.
		 * @return bool True if option was removed, otherwise false.
		 */
		public function delete( $option ) {
			if ( $this->is_stored_in_network( $option ) && is_multisite() ) {
				$site_id = get_current_blog_id();

				$options = get_network_option( null, $this->get_prefix() . $option, array() );
				if ( ! isset( $options[ $site_id ] ) ) {
					return false;
				}

				if ( count( $options ) > 1 ) {
					unset( $options[ $site_id ] );
					return update_network_option( null, $this->get_prefix() . $option, $options );
				}

				return delete_network_option( null, $this->get_prefix() . $option );
			}

			return delete_option( $this->get_prefix() . $option );
		}

		/**
		 * Retrieves the option values based on the option name for all sites in a network.
		 *
		 * @since 1.0.0
		 *
		 * @param string $option     Name of the option to retrieve.
		 * @param int    $network_id Optional. Network ID to receive the option values for. Default is
		 *                           the current network.
		 * @return array Array of site IDs with their values for the option name.
		 */
		public function get_for_all_sites( $option, $network_id = null ) {
			if ( ! $this->is_stored_in_network( $option ) || ! is_multisite() ) {
				$value = $this->get( $option );
				if ( false === $value ) {
					return array();
				}
				return array( 1 => $value );
			}

			return get_network_option( $network_id, $this->get_prefix() . $option, array() );
		}

		/**
		 * Retrieves the networks where a specific option is stored.
		 *
		 * @since 1.0.0
		 *
		 * @global wpdb $wpdb WordPress database abstraction object.
		 *
		 * @param string $option Name of the option to check.
		 * @return array Array of network IDs.
		 */
		public function get_networks_with_option( $option ) {
			global $wpdb;

			if ( ! $this->is_stored_in_network( $option ) || ! is_multisite() ) {
				$value = $this->get( $option );
				if ( false === $value ) {
					return array();
				}
				return array( 1 );
			}

			return array_map( 'absint', $wpdb->get_col( $wpdb->prepare( "SELECT site_id FROM $wpdb->sitemeta WHERE meta_key = %s", $this->get_prefix() . $option ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

		/**
		 * Flushes an option's storage by name.
		 *
		 * For a non-Multisite setup, this will do essentially the same as the `delete()` method.
		 * On Multisite however, it will remove all values of that option name for the entire network.
		 *
		 * @since 1.0.0
		 *
		 * @param string $option     Name of the option to flush.
		 * @param int    $network_id Optional. Network ID to flush the option in. Default is the current network.
		 * @return bool True if option was flushed, otherwise false.
		 */
		public function flush( $option, $network_id = null ) {
			if ( $this->is_stored_in_network( $option ) && is_multisite() ) {
				return delete_network_option( $network_id, $this->get_prefix() . $option );
			}

			return delete_option( $this->get_prefix() . $option );
		}

		/**
		 * Checks whether an option is registered to be stored in network.
		 *
		 * @since 1.0.0
		 *
		 * @param string $option Name of the option to check for.
		 * @return boolean True if option is registered to be stored in network, otherwise false.
		 */
		public function is_stored_in_network( $option ) {
			return in_array( $option, $this->network_stored, true );
		}

		/**
		 * Registers an option to be stored in network.
		 *
		 * @since 1.0.0
		 *
		 * @param string|array $option Name of the option (or an array of option names) to register.
		 */
		public function store_in_network( $option ) {
			$option_names = (array) $option;

			$this->network_stored = array_unique( array_merge( $this->network_stored, $option_names ) );
		}

		/**
		 * Migrates network stored options to a network when switching from single to multisite.
		 *
		 * The method checks whether the options exist on the single site, and if they do, it
		 * adds them to the network options array and removes the option from the single site.
		 *
		 * @since 1.0.0
		 *
		 * @param array $network_options The array of network options to add to the network.
		 * @return array The modified network options including the options to migrate.
		 */
		protected function migrate_to_network( $network_options ) {
			// Only migrate when switching from single to multisite.
			if ( is_multisite() ) {
				return $network_options;
			}

			foreach ( $this->network_stored as $option ) {
				$value = get_option( $this->get_prefix() . $option );
				if ( false === $value ) {
					continue;
				}

				$network_options[ $this->get_prefix() . $option ] = array( 1 => $value );

				delete_option( $this->get_prefix() . $option );
			}

			return $network_options;
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
					'name'     => 'populate_network_meta',
					'callback' => array( $this, 'migrate_to_network' ),
					'priority' => 10,
					'num_args' => 1,
				),
			);
		}
	}

endif;
