<?php
/**
 * API-API Storage class
 *
 * @package APIAPI\Core\Storages
 * @since 1.0.0
 */

namespace APIAPI\Core\Storages;

use APIAPI\Core\Name_Trait;

if ( ! class_exists( 'APIAPI\Core\Storages\Storage' ) ) {

	/**
	 * Storage class for the API-API.
	 *
	 * Represents a specific storage.
	 *
	 * @since 1.0.0
	 */
	abstract class Storage implements Storage_Interface {
		use Name_Trait;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Slug of the instance.
		 */
		public function __construct( $name ) {
			$this->set_name( $name );
		}
	}

}
