<?php
/**
 * Trait for managers that support meta
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Traits;

use Leaves_And_Love\Plugin_Lib\Meta;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Meta_Manager_Trait' ) ) :

	/**
	 * Trait for managers.
	 *
	 * Include this trait for managers that support meta.
	 *
	 * @since 1.0.0
	 */
	trait Meta_Manager_Trait {
		/**
		 * The Metadata API service definition.
		 *
		 * @since 1.0.0
		 * @static
		 * @var string
		 */
		protected static $service_meta = Meta::class;

		/**
		 * The metadata type.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $meta_type = 'model';

		/**
		 * Adds metadata for the specified object.
		 *
		 * @since 1.0.0
		 *
		 * @param int    $model_id   ID of the model metadata is for.
		 * @param string $meta_key   Metadata key.
		 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
		 * @param bool   $unique     Optional, default is false.
		 *                           Whether the specified metadata key should be unique for the object.
		 *                           If true, and the object already has a value for the specified metadata key,
		 *                           no change will be made.
		 * @return int|false The meta ID on success, false on failure.
		 */
		public function add_meta( $model_id, $meta_key, $meta_value, $unique = false ) {
			return $this->meta()->add( $this->meta_type, $model_id, $meta_key, $meta_value, $unique );
		}

		/**
		 * Updates metadata for the specified object. If no value already exists for the specified object
		 * ID and metadata key, the metadata will be added.
		 *
		 * @since 1.0.0
		 *
		 * @param int    $model_id   ID of the model metadata is for.
		 * @param string $meta_key   Metadata key.
		 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
		 * @param mixed  $prev_value Optional. If specified, only update existing metadata entries with
		 *                           the specified value. Otherwise, update all entries.
		 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
		 */
		public function update_meta( $model_id, $meta_key, $meta_value, $prev_value = '' ) {
			return $this->meta()->update( $this->meta_type, $model_id, $meta_key, $meta_value, $prev_value );
		}

		/**
		 * Deletes metadata for the specified object.
		 *
		 * @since 1.0.0
		 *
		 * @param int    $model_id   ID of the model metadata is for.
		 * @param string $meta_key   Metadata key.
		 * @param mixed  $meta_value Optional. Metadata value. Must be serializable if non-scalar. If specified, only delete
		 *                           metadata entries with this value. Otherwise, delete all entries with the specified meta_key.
		 *                           Pass `null, `false`, or an empty string to skip this check. For backward compatibility,
		 *                           it is not possible to pass an empty string to delete those entries with an empty string
		 *                           for a value.
		 * @param bool   $delete_all Optional, default is false. If true, delete matching metadata entries for all objects,
		 *                           ignoring the specified object_id. Otherwise, only delete matching metadata entries for
		 *                           the specified object_id.
		 * @return bool True on successful delete, false on failure.
		 */
		public function delete_meta( $model_id, $meta_key, $meta_value = '', $delete_all = false ) {
			return $this->meta()->delete( $this->meta_type, $model_id, $meta_key, $meta_value, $delete_all );
		}

		/**
		 * Retrieves metadata for the specified object.
		 *
		 * @since 1.0.0
		 *
		 * @param int    $model_id  ID of the model metadata is for.
		 * @param string $meta_key  Optional. Metadata key. If not specified, retrieve all metadata for
		 *                          the specified object.
		 * @param bool   $single    Optional, default is false.
		 *                          If true, return only the first value of the specified meta_key.
		 *                          This parameter has no effect if meta_key is not specified.
		 * @return mixed Single metadata value, or array of values.
		 */
		public function get_meta( $model_id, $meta_key = '', $single = false ) {
			return $this->meta()->get( $this->meta_type, $model_id, $meta_key, $single );
		}

		/**
		 * Determines if a meta key is set for a given object
		 *
		 * @since 1.0.0
		 *
		 * @param int    $model_id  ID of the model metadata is for.
		 * @param string $meta_key  Metadata key.
		 * @return bool True of the key is set, false if not.
		 */
		public function meta_exists( $model_id, $meta_key ) {
			return $this->meta()->exists( $this->meta_type, $model_id, $meta_key );
		}

		/**
		 * Deletes all metadata for the specified object.
		 *
		 * @since 1.0.0
		 *
		 * @param int $model_id ID of the model metadata is for.
		 * @return bool True on successful delete, false on failure.
		 */
		public function delete_all_meta( $model_id ) {
			return $this->meta()->delete_all( $this->meta_type, $model_id );
		}

		/**
		 * Updates the metadata cache for the specified objects.
		 *
		 * @since 1.0.0
		 *
		 * @param array $model_ids Array of object IDs to update cache for.
		 * @return array|false Metadata cache for the specified objects, or false on failure.
		 */
		public function update_meta_cache( $model_ids ) {
			return $this->meta()->update_cache( $this->meta_type, $model_ids );
		}

		/**
		 * Returns the metadata type.
		 *
		 * @since 1.0.0
		 *
		 * @return string The metadata type.
		 */
		public function get_meta_type() {
			return $this->meta_type;
		}

		/**
		 * Adds the meta database table.
		 *
		 * @since 1.0.0
		 */
		protected function add_meta_database_table() {
			$prefix = $this->db()->get_prefix();

			$meta_table_name = $this->meta_type . 'meta';
			$id_field_name   = $this->meta_type . '_id';

			$max_index_length = 191;

			$this->db()->add_table(
				$meta_table_name,
				array(
					'meta_id bigint(20) unsigned NOT NULL auto_increment',
					"{$prefix}{$id_field_name} bigint(20) unsigned NOT NULL default '0'",
					'meta_key varchar(255) default NULL',
					'meta_value longtext',
					'PRIMARY KEY  (meta_id)',
					"KEY {$prefix}{$id_field_name} ({$prefix}{$id_field_name})",
					"KEY meta_key (meta_key($max_index_length))",
				)
			);
		}
	}

endif;
