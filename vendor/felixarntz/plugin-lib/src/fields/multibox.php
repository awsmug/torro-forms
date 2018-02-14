<?php
/**
 * Multibox field class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Multibox' ) ) :

	/**
	 * Class for a multibox field.
	 *
	 * @since 1.0.0
	 */
	class Multibox extends Radio {
		/**
		 * Field type identifier.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $slug = 'multibox';

		/**
		 * Whether this field accepts multiple values.
		 *
		 * @since 1.0.0
		 * @var bool
		 */
		protected $multi = true;
	}

endif;
