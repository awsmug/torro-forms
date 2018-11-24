<?php
/**
 * Multiselect field class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Multiselect' ) ) :

	/**
	 * Class for a multiselect field.
	 *
	 * @since 1.0.0
	 */
	class Multiselect extends Select {
		/**
		 * Field type identifier.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $slug = 'multiselect';

		/**
		 * Whether this field accepts multiple values.
		 *
		 * @since 1.0.0
		 * @var bool
		 */
		protected $multi = true;
	}

endif;
