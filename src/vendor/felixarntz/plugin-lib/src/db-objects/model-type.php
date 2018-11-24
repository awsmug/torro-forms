<?php
/**
 * Model type class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

use Leaves_And_Love\Plugin_Lib\Components\Item;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type' ) ) :

	/**
	 * Base class for a model type
	 *
	 * This class represents a general model type.
	 *
	 * @since 1.0.0
	 */
	abstract class Model_Type extends Item {
		/**
		 * Sets the type arguments and fills it with defaults.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args Type arguments.
		 */
		protected function set_args( $args ) {
			parent::set_args( $args );

			if ( ! isset( $this->args['label'] ) ) {
				$this->args['label'] = '';
			}

			if ( ! isset( $this->args['public'] ) ) {
				$this->args['public'] = false;
			}

			if ( ! isset( $this->args['default'] ) ) {
				$this->args['default'] = false;
			}
		}
	}

endif;
