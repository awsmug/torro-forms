<?php
/**
 * Service base class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Service' ) ) :

	/**
	 * Abstract class for any kind of service.
	 *
	 * @since 1.0.0
	 */
	abstract class Service {
		/**
		 * Prefix for class functionality.
		 *
		 * @since 1.0.0
		 * @var string|bool
		 */
		protected $prefix = false;

		/**
		 * Returns the instance prefix.
		 *
		 * @since 1.0.0
		 *
		 * @return string|bool Instance prefix, or false if no prefix is set.
		 */
		public function get_prefix() {
			return $this->prefix;
		}

		/**
		 * Sets the instance prefix.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prefix Instance prefix.
		 */
		protected function set_prefix( $prefix ) {
			$this->prefix = $prefix;
		}
	}

endif;
