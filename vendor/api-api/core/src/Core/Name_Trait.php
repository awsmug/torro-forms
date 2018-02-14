<?php
/**
 * API-API Name trait
 *
 * @package APIAPI\Core
 * @since 1.0.0
 */

namespace APIAPI\Core;

if ( ! trait_exists( 'APIAPI\Core\Name_Trait' ) ) {

	/**
	 * Name trait for the API-API.
	 *
	 * @since 1.0.0
	 */
	trait Name_Trait {
		/**
		 * Slug of the instance.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $name;

		/**
		 * Sets the slug of the instance.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Slug of the instance.
		 */
		protected function set_name( $name ) {
			$this->name = $name;
		}

		/**
		 * Returns the slug of the instance.
		 *
		 * @since 1.0.0
		 *
		 * @return string Slug of the instance.
		 */
		public function get_name() {
			return $this->name;
		}
	}

}
