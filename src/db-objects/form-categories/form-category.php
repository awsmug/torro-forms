<?php
/**
 * Form category class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Form_Categories;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Core_Model;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Sitewide_Model_Trait;
use WP_Term;
use stdClass;

/**
 * Class representing a form category.
 *
 * @since 1.0.0
 *
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property int    $parent
 *
 * @property-read int $id
 * @property-read int $count
 */
class Form_Category extends Core_Model {
	use Sitewide_Model_Trait;

	/**
	 * Constructor.
	 *
	 * Sets the ID and fetches relevant data.
	 *
	 * @since 1.0.0
	 *
	 * @param Leaves_And_Love\Plugin_Lib\DB_Objects\Manager $manager The manager instance for the model.
	 * @param WP_Post|null                                  $db_obj  Optional. The database object or
	 *                                                               null for a new instance.
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
		switch ( $property ) {
			case 'id':
			case 'title':
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
		switch ( $property ) {
			case 'id':
				return $this->original->term_id;
			case 'title':
				return $this->original->name;
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
		$found   = false;
		$changed = false;

		switch ( $property ) {
			case 'id':
			case 'count':
				return;
			case 'title':
				$found = true;
				if ( $this->original->name !== $value ) {
					$this->original->name = $value;
					$changed              = true;
				}
				break;
		}

		if ( $found ) {
			if ( $changed && ! in_array( $property, $this->pending_properties, true ) ) {
				$this->pending_properties[] = $property;
			}

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
		$properties = array( 'id', 'title', 'slug', 'description', 'parent', 'count' );
		if ( $pending_only ) {
			$properties = $this->pending_properties;
		}

		$values = array();
		foreach ( $properties as $property ) {
			$values[ $property ] = $this->__get( $property );
		}

		return $values;
	}

	/**
	 * Fills the $original property with a default object.
	 *
	 * This method is called if a new object has been instantiated.
	 *
	 * @since 1.0.0
	 */
	protected function set_default_object() {
		$this->original = new WP_Term( new stdClass() );
	}

	/**
	 * Returns the names of all properties that should be accessible on the Core object.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of property names.
	 */
	protected function get_db_fields() {
		return array(
			'slug',
			'description',
			'parent',
			'count',
		);
	}
}
