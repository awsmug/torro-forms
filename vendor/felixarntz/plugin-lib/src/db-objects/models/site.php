<?php
/**
 * Site model class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Models;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;
use WP_Site;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Site' ) ) :

	/**
	 * Model class for a site
	 *
	 * This class represents a site. Must only be used in a multisite setup.
	 *
	 * @since 1.0.0
	 *
	 * @property string $domain
	 * @property string $path
	 * @property int    $network_id
	 * @property string $registered
	 * @property string $last_updated
	 * @property string $public
	 * @property string $archived
	 * @property string $mature
	 * @property string $spam
	 * @property string $deleted
	 * @property string $lang_id
	 *
	 * @property-read int $id
	 */
	class Site extends Core_Model {
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
			if ( 'id' === $property ) {
				return true;
			}

			if ( 'network_id' === $property ) {
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
			if ( 'id' === $property ) {
				return (int) $this->original->blog_id;
			}

			if ( 'network_id' === $property ) {
				return (int) $this->original->site_id;
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
			);

			if ( in_array( $property, $nowrite_properties, true ) ) {
				return;
			}

			if ( 'network_id' === $property ) {
				$this->set_value_type_safe( 'site_id', $value );
				return;
			}

			parent::__set( $property, $value );
		}

		/**
		 * Fills the $original property with a default object.
		 *
		 * This method is called if a new object has been instantiated.
		 *
		 * @since 1.0.0
		 */
		protected function set_default_object() {
			$this->original = new WP_Site( new \stdClass() );
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
				'blog_id',
				'domain',
				'path',
				'site_id',
				'registered',
				'last_updated',
				'public',
				'archived',
				'mature',
				'spam',
				'deleted',
				'lang_id',
			);
		}
	}

endif;
