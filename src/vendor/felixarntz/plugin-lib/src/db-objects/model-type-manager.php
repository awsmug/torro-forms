<?php
/**
 * Model type manager class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

use Leaves_And_Love\Plugin_Lib\Components\Item_Registry;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Manager' ) ) :

	/**
	 * Base class for a model type manager
	 *
	 * This class represents a general model type manager.
	 *
	 * @since 1.0.0
	 */
	abstract class Model_Type_Manager extends Item_Registry {
		/**
		 * The model type class name.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $item_class_name = Model_Type::class;

		/**
		 * Returns the slug of the default type.
		 *
		 * @since 1.0.0
		 *
		 * @return string Default type.
		 */
		public function get_default() {
			$results = $this->query( array( 'default' => true ) );
			if ( empty( $results ) ) {
				return '';
			}

			return key( $results );
		}

		/**
		 * Returns the slugs of all public types.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of public types.
		 */
		public function get_public() {
			$results = $this->query( array( 'public' => true ) );
			if ( empty( $results ) ) {
				return array();
			}

			return array_keys( $results );
		}
	}

endif;
