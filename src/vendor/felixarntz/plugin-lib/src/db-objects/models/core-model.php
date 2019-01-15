<?php
/**
 * Model class for a Core object
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Models;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Core_Model' ) ) :

	/**
	 * Base class for a core model
	 *
	 * This class represents a general core model.
	 *
	 * @since 1.0.0
	 */
	abstract class Core_Model extends Model {
		/**
		 * The original Core object for this model.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		protected $original;

		/**
		 * Core uses several redundant prefixes for property names of its objects.
		 * This property can be used to specify the prefix and thus make access easier.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $redundant_prefix = '';

		/**
		 * Constructor.
		 *
		 * Sets the ID and fetches relevant data.
		 *
		 * @since 1.0.0
		 *
		 * @param Manager     $manager The manager instance for the model.
		 * @param object|null $db_obj  Optional. The database object or null for a new instance.
		 */
		public function __construct( $manager, $db_obj = null ) {
			parent::__construct( $manager, $db_obj );

			if ( ! $db_obj ) {
				$this->set_default_object();
			}
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
			$db_fields = $this->get_db_fields();

			if ( in_array( $property, $db_fields, true ) && isset( $this->original->$property ) ) {
				return true;
			}

			if ( ! empty( $this->redundant_prefix ) && 0 !== strpos( $property, $this->redundant_prefix ) ) {
				$prefixed_property = $this->redundant_prefix . $property;
				if ( in_array( $prefixed_property, $db_fields, true ) && isset( $this->original->$prefixed_property ) ) {
					return true;
				}
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
			$db_fields = $this->get_db_fields();

			if ( in_array( $property, $db_fields, true ) && isset( $this->original->$property ) ) {
				return $this->original->$property;
			}

			if ( ! empty( $this->redundant_prefix ) && 0 !== strpos( $property, $this->redundant_prefix ) ) {
				$prefixed_property = $this->redundant_prefix . $property;
				if ( in_array( $prefixed_property, $db_fields, true ) && isset( $this->original->$prefixed_property ) ) {
					return $this->original->$prefixed_property;
				}
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
			if ( $property === $this->manager->get_primary_property() ) {
				return;
			}

			$db_fields = $this->get_db_fields();

			if ( in_array( $property, $db_fields, true ) && isset( $this->original->$property ) ) {
				$old = $this->original->$property;

				$this->set_value_type_safe( $property, $value );

				if ( $old !== $this->original->$property && ! in_array( $property, $this->pending_properties, true ) ) {
					$this->pending_properties[] = $property;
				}
				return;
			}

			if ( ! empty( $this->redundant_prefix ) && 0 !== strpos( $property, $this->redundant_prefix ) ) {
				$prefixed_property = $this->redundant_prefix . $property;
				if ( $prefixed_property === $this->manager->get_primary_property() ) {
					return;
				}

				if ( in_array( $prefixed_property, $db_fields, true ) && isset( $this->original->$prefixed_property ) ) {
					$old = $this->original->$prefixed_property;

					$this->set_value_type_safe( $prefixed_property, $value );

					if ( $old !== $this->original->$prefixed_property && ! in_array( $prefixed_property, $this->pending_properties, true ) ) {
						$this->pending_properties[] = $prefixed_property;
					}
					return;
				}
			}

			parent::__set( $property, $value );
		}

		/**
		 * Returns the original Core object for this model.
		 *
		 * @since 1.0.0
		 *
		 * @return object WordPress Core object.
		 */
		public function get_original() {
			return $this->original;
		}

		/**
		 * Sets the properties of the model to those of a database row object.
		 *
		 * @since 1.0.0
		 *
		 * @param object $db_obj The database object.
		 */
		protected function set( $db_obj ) {
			$this->original = $db_obj;
		}

		/**
		 * Sets the value of an existing property in a type-safe way.
		 *
		 * @since 1.0.0
		 *
		 * @param string $property Property to set.
		 * @param mixed  $value    Property value.
		 */
		protected function set_value_type_safe( $property, $value ) {
			if ( $property === $this->manager->get_primary_property() ) {
				$this->original->{$property} = intval( $value );
			} elseif ( is_int( $this->original->$property ) ) {
				$this->original->{$property} = intval( $value );
			} elseif ( is_float( $this->original->$property ) ) {
				$this->original->{$property} = floatval( $value );
			} elseif ( is_string( $this->original->$property ) ) {
				$this->original->{$property} = strval( $value );
			} elseif ( is_bool( $this->original->$property ) ) {
				$this->original->{$property} = (bool) $value;
			} else {
				$this->original->{$property} = $value;
			}
		}

		/**
		 * Sets or gets the value of the primary property.
		 *
		 * @since 1.0.0
		 *
		 * @param int|null $value Integer to set the value, null to retrieve it. Default null.
		 * @return return int Current value of the primary property.
		 */
		protected function primary_property_value( $value = null ) {
			$primary_property = $this->manager->get_primary_property();

			if ( is_int( $value ) ) {
				if ( $value !== (int) $this->original->$primary_property ) {
					$this->original = $this->manager->fetch( $value );
				}
			}

			return $this->original->$primary_property;
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
			if ( $pending_only ) {
				$args = array();
				foreach ( $this->pending_properties as $property ) {
					$args[ $property ] = $this->original->$property;
				}

				return $args;
			}

			$object_vars = is_callable( array( $this->original, 'to_array' ) ) ? call_user_func( array( $this->original, 'to_array' ) ) : get_object_vars( $this->original );

			return array_intersect_key( $object_vars, array_flip( $this->get_db_fields() ) );
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

			$blacklist[] = 'original';

			return $blacklist;
		}

		/**
		 * Fills the $original property with a default object.
		 *
		 * This method is called if a new object has been instantiated.
		 *
		 * @since 1.0.0
		 */
		abstract protected function set_default_object();

		/**
		 * Returns the names of all properties that are part of the database object.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of property names.
		 */
		abstract protected function get_db_fields();
	}

endif;
