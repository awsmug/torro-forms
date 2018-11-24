<?php
/**
 * Range field class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Range' ) ) :

	/**
	 * Class for a range field.
	 *
	 * @since 1.0.0
	 */
	class Range extends Number {
		/**
		 * Field type identifier.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $slug = 'range';

		/**
		 * Type attribute for the input.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $type = 'range';
	}

endif;
