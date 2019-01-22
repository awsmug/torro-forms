<?php
/**
 * Model status class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

use Leaves_And_Love\Plugin_Lib\Components\Item;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Status' ) ) :

	/**
	 * Base class for a model status
	 *
	 * This class represents a general model status.
	 *
	 * @since 1.0.0
	 */
	abstract class Model_Status extends Item {
		/**
		 * Sets the status arguments and fills it with defaults.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args Status arguments.
		 */
		protected function set_args( $args ) {
			parent::set_args( $args );

			if ( ! isset( $this->args['label'] ) ) {
				$this->args['label'] = '';
			}

			if ( ! isset( $this->args['public'] ) ) {
				$this->args['public'] = false;
			}

			if ( ! isset( $this->args['internal'] ) ) {
				$this->args['internal'] = false;
			}

			if ( ! isset( $this->args['default'] ) ) {
				$this->args['default'] = false;
			}

			if ( ! isset( $this->args['view_status_label'] ) ) {
				$this->args['view_status_label'] = '';
			}
		}
	}

endif;
