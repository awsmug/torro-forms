<?php
/**
 * Item class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Components;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Components\Item' ) ) :

	/**
	 * Base class for an item
	 *
	 * This class represents a general item.
	 *
	 * @since 1.0.0
	 */
	abstract class Item {
		/**
		 * Parent registry.
		 *
		 * @since 1.0.0
		 * @var Item_Registry
		 */
		protected $owner;

		/**
		 * Item slug.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $slug;

		/**
		 * Item arguments.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $args = array();

		/**
		 * Constructor.
		 *
		 * Sets the item slug and additional arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param Item_Registry $owner Parent registry.
		 * @param string        $slug  Item slug.
		 * @param array|object  $args  Optional. Item arguments. Default empty.
		 */
		public function __construct( $owner, $slug, $args = array() ) {
			$this->owner = $owner;

			$this->slug = $slug;

			if ( is_object( $args ) ) {
				$args = get_object_vars( $args );
			}

			$this->set_args( $args );
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
			if ( 'slug' === $property ) {
				return true;
			}

			return isset( $this->args[ $property ] );
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
			if ( 'slug' === $property ) {
				return $this->slug;
			}

			if ( isset( $this->args[ $property ] ) ) {
				return $this->args[ $property ];
			}

			return null;
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
			if ( 'slug' === $property ) {
				$this->slug = $value;
				return;
			}

			$this->args[ $property ] = $value;
		}

		/**
		 * Returns an array representation of the item.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array including all item information.
		 */
		public function to_json() {
			return array_merge( array( 'slug' => $this->slug ), $this->args );
		}

		/**
		 * Sets the item arguments and fills it with defaults.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args Item arguments.
		 */
		protected function set_args( $args ) {
			$this->args = wp_parse_args( $args, $this->get_defaults() );
		}

		/**
		 * Returns the default item arguments.
		 *
		 * @since 1.0.0
		 *
		 * @return array Default item arguments.
		 */
		abstract protected function get_defaults();
	}

endif;
