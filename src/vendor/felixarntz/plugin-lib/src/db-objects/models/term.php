<?php
/**
 * Term model class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Models;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Sitewide_Model_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;
use WP_Term;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Term' ) ) :

	/**
	 * Model class for a term
	 *
	 * This class represents a term.
	 *
	 * @since 1.0.0
	 *
	 * @property string $name
	 * @property string $slug
	 * @property string $term_group
	 * @property string $taxonomy
	 * @property string $description
	 * @property int    $parent
	 *
	 * @property-read int $id
	 * @property-read int $term_taxonomy_id
	 * @property-read int $count
	 * @property-read int $object_id
	 */
	class Term extends Core_Model {
		use Sitewide_Model_Trait;

		/**
		 * Constructor.
		 *
		 * Sets the ID and fetches relevant data.
		 *
		 * @since 1.0.0
		 *
		 * @param Manager      $manager The manager instance for the model.
		 * @param WP_Term|null $db_obj  Optional. The database object or null for a new instance.
		 */
		public function __construct( $manager, $db_obj = null ) {
			parent::__construct( $manager, $db_obj );

			$this->redundant_prefix = 'term_';
		}

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

			if ( 'object_id' === $property ) {
				return isset( $this->original->object_id );
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
				return $this->original->term_id;
			}

			if ( 'object_id' === $property ) {
				if ( ! isset( $this->original->object_id ) ) {
					return null;
				}

				return $this->original->object_id;
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
				'term_id',
				'id',
				'term_taxonomy_id',
				'taxonomy_id',
				'count',
				'object_id',
			);

			if ( in_array( $property, $nowrite_properties, true ) ) {
				return;
			}

			parent::__set( $property, $value );
		}

		/**
		 * Returns a list of internal properties that are not publicly accessible.
		 *
		 * When overriding this method, always make sure to merge with the parent result.
		 *
		 * @since 1.0.0
		 *
		 * @return array Property blacklist.
		 */
		protected function get_blacklist() {
			$blacklist = parent::get_blacklist();

			// Do not permit access to the $filter property of WP_Term.
			$blacklist[] = 'filter';

			return $blacklist;
		}

		/**
		 * Fills the $original property with a default object.
		 *
		 * This method is called if a new object has been instantiated.
		 *
		 * @since 1.0.0
		 */
		protected function set_default_object() {
			$this->original = new WP_Term( new \stdClass() );
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
				'term_id',
				'name',
				'slug',
				'term_group',
				'term_taxonomy_id',
				'taxonomy',
				'description',
				'parent',
				'count',
			);
		}
	}

endif;
