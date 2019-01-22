<?php
/**
 * Network model class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Models;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;
use WP_Network;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Network' ) ) :

	/**
	 * Model class for a network
	 *
	 * This class represents a network. Must only be used in a multisite setup.
	 *
	 * @since 1.0.0
	 *
	 * @property string $domain
	 * @property string $path
	 * @property int    $site_id
	 *
	 * @property-read int    $id
	 * @property-read string $cookie_domain
	 */
	class Network extends Core_Model {
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
			if ( 'site_id' === $property ) {
				return true;
			}

			if ( 'cookie_domain' === $property ) {
				return true;
			}

			return parent::__isset( $property );
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
			if ( 'site_id' === $property ) {
				return (int) $this->original->blog_id;
			}

			if ( 'cookie_domain' === $property ) {
				if ( 'www.' === substr( $this->original->domain, 0, 4 ) ) {
					return substr( $this->original->domain, 4 );
				}

				return $this->original->domain;
			}

			return parent::__get( $property );
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
			$nowrite_properties = array(
				'id',
				'cookie_domain',
				'site_id',
				'blog_id',
			);

			if ( in_array( $property, $nowrite_properties, true ) ) {
				return;
			}

			parent::__set( $property, $value );
		}

		/**
		 * Returns all current values as $property => $value pairs.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $pending_only Whether to only return pending properties. Default false.
		 * @return array Array of $property => $value pairs.
		 */
		protected function get_property_values( $pending_only = false ) {
			$object_vars = parent::get_property_values( $pending_only );
			if ( ! $pending_only && ! isset( $object_vars['id'] ) ) {
				$object_vars['id'] = $this->original->id;
			}

			return $object_vars;
		}

		/**
		 * Fills the $original property with a default object.
		 *
		 * This method is called if a new object has been instantiated.
		 *
		 * @since 1.0.0
		 */
		protected function set_default_object() {
			$this->original = new WP_Network( new \stdClass() );
		}

		/**
		 * Returns the names of all properties that are part of the database object.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of property names.
		 */
		protected function get_db_fields() {
			return array(
				'id',
				'domain',
				'path',
			);
		}
	}

endif;
