<?php
/**
 * Item registry class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Components;

use Leaves_And_Love\Plugin_Lib\Service;
use WP_List_Util;
use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Components\Item_Registry' ) ) :

	/**
	 * Base class for an item registry.
	 *
	 * This class represents a general item registry.
	 *
	 * @since 1.0.0
	 */
	abstract class Item_Registry extends Service {
		/**
		 * Items container.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $items = array();

		/**
		 * The item class name.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $item_class_name = Item::class;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prefix The instance prefix.
		 */
		public function __construct( $prefix ) {
			$this->set_prefix( $prefix );

			$this->register_defaults();
		}

		/**
		 * Registers a new item.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug for the item.
		 * @param array  $args Optional. Array of item arguments. Default empty.
		 * @return bool True on success, false on failure.
		 */
		public function register( $slug, $args = array() ) {
			if ( isset( $this->items[ $slug ] ) ) {
				return false;
			}

			$class_name = $this->item_class_name;

			$this->items[ $slug ] = new $class_name( $this, $slug, $args );

			return true;
		}

		/**
		 * Retrieves an item object.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug of the item.
		 * @return Item|null Type object, or null it it does not exist.
		 */
		public function get( $slug ) {
			if ( ! isset( $this->items[ $slug ] ) ) {
				return null;
			}

			return $this->items[ $slug ];
		}

		/**
		 * Retrieves a list of item objects.
		 *
		 * By default, all registered item objects will be returned.
		 * However, the result can be modified by specifying arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args {
		 *     Array of arguments for querying items. Any field available on the item can be passed
		 *     as key with a value to filter the result. Furthermore the following arguments may be
		 *     provided for additional tweaks.
		 *
		 *     @type string       $operator The logical operation to perform the filter. Must be either
		 *                                  'AND', 'OR' or 'NOT'. Default 'AND'.
		 *     @type string|array $orderby  Either the field name to order by or an array of multiple
		 *                                  orderby fields as $orderby => $order. Default 'slug'.
		 *     @type string       $order    Either 'ASC' or 'DESC'. Only used if $orderby is a string.
		 *                                  Default 'ASC'.
		 *     @type string       $field    Field from the objects to return instead of the entire objects.
		 *                                  Default empty.
		 * }
		 * @return array A list of item objects or specific item object fields, depending on $args.
		 */
		public function query( $args = array() ) {
			if ( empty( $this->items ) ) {
				return array();
			}

			$operator = 'and';
			$orderby  = 'slug';
			$order    = 'ASC';
			$field    = '';

			foreach ( array( 'operator', 'orderby', 'order', 'field' ) as $arg ) {
				if ( isset( $args[ $arg ] ) ) {
					$$arg = $args[ $arg ];
					unset( $args[ $arg ] );
				}
			}

			if ( ! in_array( strtolower( $operator ), array( 'or', 'not' ), true ) ) {
				$operator = 'and';
			}

			$items                = $this->items;
			$transformed_to_array = false;
			if ( ! empty( $args ) || ! empty( $orderby ) || ! empty( $order ) ) {
				/* `WP_List_Util::filter()` and `WP_List_Util::sort()` can't handle objects with magic properties. */
				$items                = $this->objects_to_arrays( $items );
				$transformed_to_array = true;
			}

			$util = new WP_List_Util( $items );

			$util->filter( $args, $operator );

			if ( ! empty( $orderby ) || ! empty( $order ) ) {
				if ( empty( $order ) ) {
					$order = 'ASC';
				}
				$util->sort( $orderby, $order, true );
			}

			if ( ! empty( $field ) ) {
				$util->pluck( $field );
			} elseif ( $transformed_to_array ) {
				/* Objects transformed into arrays need to be transformed back. */
				return $this->arrays_to_objects( $util->get_output() );
			}

			return $util->get_output();
		}

		/**
		 * Unregisters an existing item.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug of the item.
		 * @return bool True on success, false on failure.
		 */
		public function unregister( $slug ) {
			if ( ! isset( $this->items[ $slug ] ) ) {
				return false;
			}

			unset( $this->items[ $slug ] );

			return true;
		}

		/**
		 * Transforms a list of item objects into a list of item arrays.
		 *
		 * @since 1.0.0
		 *
		 * @param array $items List of item objects.
		 * @return array List of item arrays.
		 */
		protected function objects_to_arrays( $items ) {
			foreach ( $items as $slug => $item ) {
				if ( is_array( $item ) ) {
					continue;
				}

				$items[ $slug ] = $item->to_json();
			}

			return $items;
		}

		/**
		 * Transforms a list of item arrays into a list of item objects.
		 *
		 * @since 1.0.0
		 *
		 * @param array $items List of item arrays.
		 * @return array List of item objects.
		 */
		protected function arrays_to_objects( $items ) {
			foreach ( $items as $slug => $item ) {
				if ( is_object( $item ) ) {
					continue;
				}

				$items[ $slug ] = $this->get( $slug );
			}

			return $items;
		}

		/**
		 * Registers default items.
		 *
		 * @since 1.0.0
		 */
		abstract protected function register_defaults();
	}

endif;
