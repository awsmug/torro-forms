<?php
/**
 * Array_Storage class
 *
 * @package APIAPI\Core\Storages
 * @since 1.0.0
 */

namespace APIAPI\Core\Storages;

use APIAPI\Core\Storages\Storage;

if ( ! class_exists( 'APIAPI\Core\Storages\Array_Storage' ) ) {

	/**
	 * Storage base class for an array storage.
	 *
	 * @since 1.0.0
	 */
	abstract class Array_Storage extends Storage {
		/**
		 * Stores a single value.
		 *
		 * @since 1.0.0
		 *
		 * @param string $basename The basename under which to store.
		 * @param string $group    The group identifier of the group in which to store.
		 * @param string $key      The key to store a value for.
		 * @param scalar $value    The value to store.
		 */
		public function store( $basename, $group, $key, $value ) {
			$data = $this->get_array( $basename );

			$data = $this->multidimensional_set( $data, $group, $key, $value );

			$this->update_array( $basename, $data );
		}

		/**
		 * Stores multiple values.
		 *
		 * @since 1.0.0
		 *
		 * @param string $basename        The basename under which to store.
		 * @param string $group           The group identifier of the group in which to store.
		 * @param array  $keys_and_values Associative array of `$key => $value` pairs.
		 */
		public function store_multi( $basename, $group, array $keys_and_values ) {
			$data = $this->get_array( $basename );

			$data = $this->multidimensional_set_multi( $data, $group, $keys_and_values );

			$this->update_array( $basename, $data );
		}

		/**
		 * Retrieves a single value.
		 *
		 * @since 1.0.0
		 *
		 * @param string $basename The basename under which to store.
		 * @param string $group    The group identifier of the group in which to store.
		 * @param string $key      The key to retrieve its value.
		 * @return scalar|null The value, or null if not stored.
		 */
		public function retrieve( $basename, $group, $key ) {
			$data = $this->get_array( $basename );

			return $this->multidimensional_get( $data, $group, $key );
		}

		/**
		 * Retrieves multiple values.
		 *
		 * @since 1.0.0
		 *
		 * @param string $basename The basename under which to store.
		 * @param string $group    The group identifier of the group in which to store.
		 * @param array  $keys     The keys to retrieve their values.
		 * @return array Associative array of `$key => $value`. The $value might is null, if
		 *               none is stored.
		 */
		public function retrieve_multi( $basename, $group, array $keys ) {
			$data = $this->get_array( $basename );

			return $this->multidimensional_get_multi( $data, $group, $keys );
		}

		/**
		 * Deletes a single value.
		 *
		 * @since 1.0.0
		 *
		 * @param string $basename The basename under which to store.
		 * @param string $group    The group identifier of the group in which to store.
		 * @param string $key      The key to delete its value.
		 */
		public function delete( $basename, $group, $key ) {
			$data = $this->get_array( $basename );

			$data = $this->multidimensional_delete( $data, $group, $key );

			if ( empty( $data ) ) {
				$this->delete_array( $basename );
			} else {
				$this->update_array( $basename, $data );
			}
		}

		/**
		 * Deletes multiple values.
		 *
		 * @since 1.0.0
		 *
		 * @param string $basename The basename under which to store.
		 * @param string $group    The group identifier of the group in which to store.
		 * @param array  $keys     The keys to delete their values.
		 */
		public function delete_multi( $basename, $group, array $keys ) {
			$data = $this->get_array( $basename );

			$data = $this->multidimensional_delete_multi( $data, $group, $keys );

			if ( empty( $data ) ) {
				$this->delete_array( $basename );
			} else {
				$this->update_array( $basename, $data );
			}
		}

		/**
		 * Sets a single value in a multidimensional array.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $data  The array to modify.
		 * @param string $group The group to modify.
		 * @param string $key   The key to set its value.
		 * @param scalar $value The value to set.
		 * @return array The modified array.
		 */
		protected function multidimensional_set( $data, $group, $key, $value ) {
			if ( ! isset( $data[ $group ] ) ) {
				$data[ $group ] = array();
			}

			$data[ $group ][ $key ] = $value;

			return $data;
		}

		/**
		 * Sets multiple values in a multidimensional array.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $data            The array to modify.
		 * @param string $group           The group to modify.
		 * @param array  $keys_and_values Associative array of `$key => $value` pairs.
		 * @return array The modified array.
		 */
		protected function multidimensional_set_multi( $data, $group, array $keys_and_values ) {
			if ( ! isset( $data[ $group ] ) ) {
				$data[ $group ] = array();
			}

			$data[ $group ] = array_merge( $data[ $group ], $keys_and_values );

			return $data;
		}

		/**
		 * Gets a single value from a multidimensional array.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $data  The array to get the value from.
		 * @param string $group The group to get the value from.
		 * @param string $key   The key to get its value.
		 * @return scalar|null The value, or null if not stored.
		 */
		protected function multidimensional_get( $data, $group, $key ) {
			if ( ! isset( $data[ $group ] ) ) {
				return null;
			}

			if ( ! isset( $data[ $group ][ $key ] ) ) {
				return null;
			}

			return $data[ $group ][ $key ];
		}

		/**
		 * Gets multiple values from a multidimensional array.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $data  The array to get the value from.
		 * @param string $group The group to get the value from.
		 * @param array  $keys  The keys to get their values.
		 * @return array Associative array of `$key => $value`. The $value might is null, if
		 *               none is stored.
		 */
		protected function multidimensional_get_multi( $data, $group, array $keys ) {
			if ( ! isset( $data[ $group ] ) ) {
				return array_fill_keys( $keys, null );
			}

			$values = array();
			foreach ( $keys as $key ) {
				if ( ! isset( $data[ $group ][ $key ] ) ) {
					$values[ $key ] = null;
					continue;
				}

				$values[ $key ] = $data[ $group ][ $key ];
			}

			return $values;
		}

		/**
		 * Deletes a single value from a multidimensional array.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $data  The array to modify.
		 * @param string $group The group to modify.
		 * @param string $key   The key to delete its value.
		 * @return array The modified array.
		 */
		protected function multidimensional_delete( $data, $group, $key ) {
			if ( ! isset( $data[ $group ] ) ) {
				return $data;
			}

			if ( ! isset( $data[ $group ][ $key ] ) ) {
				return $data;
			}

			unset( $data[ $group ][ $key ] );

			if ( empty( $data[ $group ] ) ) {
				unset( $data[ $group ] );
			}

			return $data;
		}

		/**
		 * Deletes multiple values from a multidimensional array.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $data  The array to modify.
		 * @param string $group The group to modify.
		 * @param array  $keys  The keys to delete their values.
		 * @return array The modified array.
		 */
		protected function multidimensional_delete_multi( $data, $group, array $keys ) {
			if ( ! isset( $data[ $group ] ) ) {
				return $data;
			}

			$data[ $group ] = array_diff_key( $data[ $group ], array_flip( $keys ) );

			if ( empty( $data[ $group ] ) ) {
				unset( $data[ $group ] );
			}

			return $data;
		}

		/**
		 * Gets the array values are stored in.
		 *
		 * @since 1.0.0
		 *
		 * @param string $basename The basename under which to store.
		 * @return array Array with stored data.
		 */
		protected abstract function get_array( $basename );

		/**
		 * Updates the array values are stored in.
		 *
		 * @since 1.0.0
		 *
		 * @param string $basename The basename under which to store.
		 * @param array  $data     Array with updated data.
		 */
		protected abstract function update_array( $basename, array $data );

		/**
		 * Deletes the array values are stored in.
		 *
		 * @since 1.0.0
		 *
		 * @param string $basename The basename under which to store.
		 */
		protected abstract function delete_array( $basename );
	}

}
