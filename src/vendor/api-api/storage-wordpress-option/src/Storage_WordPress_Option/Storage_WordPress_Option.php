<?php
/**
 * Storage_WordPress_Option class
 *
 * @package APIAPI\Storage_WordPress_Option
 * @since 1.0.0
 */

namespace APIAPI\Storage_WordPress_Option;

use APIAPI\Core\Storages\Array_Storage;

if ( ! class_exists( 'APIAPI\Storage_WordPress_Option\Storage_WordPress_Option' ) ) {

	/**
	 * Storage implementation using WordPress options.
	 *
	 * @since 1.0.0
	 */
	class Storage_WordPress_Option extends Array_Storage {
		/**
		 * Gets the array values are stored in.
		 *
		 * @since 1.0.0
		 *
		 * @param string $basename The basename under which to store.
		 * @return array Array with stored data.
		 */
		protected function get_array( $basename ) {
			return get_option( $basename, array() );
		}

		/**
		 * Updates the array values are stored in.
		 *
		 * @since 1.0.0
		 *
		 * @param string $basename The basename under which to store.
		 * @param array  $data     Array with updated data.
		 */
		protected function update_array( $basename, array $data ) {
			update_option( $basename, $data );
		}

		/**
		 * Deletes the array values are stored in.
		 *
		 * @since 1.0.0
		 *
		 * @param string $basename The basename under which to store.
		 */
		protected function delete_array( $basename ) {
			delete_option( $basename );
		}
	}

}
