<?php
/**
 * Trait for managers that support types
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Traits;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Manager;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Type_Manager_Trait' ) ) :

	/**
	 * Trait for managers.
	 *
	 * Include this trait for managers that support types.
	 *
	 * @since 1.0.0
	 */
	trait Type_Manager_Trait {
		/**
		 * The type property of the model.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $type_property = 'type';

		/**
		 * Internal storage for pending type changes.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $pending_type_changes = array();

		/**
		 * The type manager service definition.
		 *
		 * @since 1.0.0
		 * @static
		 * @var string
		 */
		protected static $service_types = Model_Type_Manager::class;

		/**
		 * Registers a new type.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug for the type.
		 * @param array  $args Optional. Array of type arguments. Default empty.
		 * @return bool True on success, false on failure.
		 */
		public function register_type( $slug, $args = array() ) {
			return $this->types()->register( $slug, $args );
		}

		/**
		 * Retrieves a type object.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug of the type.
		 * @return Model_Type|null Type object, or null it it does not exist.
		 */
		public function get_type( $slug ) {
			return $this->types()->get( $slug );
		}

		/**
		 * Queries for multiple type objects.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args Array of query arguments.
		 * @return array Array of type objects.
		 */
		public function query_types( $args ) {
			return $this->types()->query( $args );
		}

		/**
		 * Unregisters an existing type.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug Unique slug of the type.
		 * @return bool True on success, false on failure.
		 */
		public function unregister_type( $slug ) {
			return $this->types()->unregister( $slug );
		}

		/**
		 * Returns the name of the type property in a model.
		 *
		 * @since 1.0.0
		 *
		 * @return string Name of the type property.
		 */
		public function get_type_property() {
			return $this->type_property;
		}

		/**
		 * Prepares data for triggering a hook for transitioning the type property on a model.
		 *
		 * @since 1.0.0
		 *
		 * @param null  $pre   Null value from the pre-filter.
		 * @param Model $model The model to modify.
		 * @return null The unmodified pre-filter value.
		 */
		public function maybe_set_transition_type_property_data( $pre, $model ) {
			$type_property = $this->get_type_property();

			$primary_property = $this->get_primary_property();
			if ( empty( $model->$primary_property ) ) {
				return $pre;
			}

			$old_model_data = $this->fetch( $model->$primary_property );
			$this->pending_type_changes[ $model->$primary_property ] = $old_model_data->$type_property;

			return $pre;
		}

		/**
		 * Triggers a hook for transitioning the type property on a model, if necessary.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error $result Result of the sync process.
		 * @param Model         $model  The model to modify.
		 * @return null The unmodified post-filter value.
		 */
		public function maybe_transition_type_property( $result, $model ) {
			if ( is_wp_error( $result ) && in_array( $result->get_error_code(), array( 'db_insert_error', 'db_update_error' ), true ) ) {
				return $result;
			}

			$primary_property = $this->get_primary_property();
			$type_property    = $this->get_type_property();

			$old_type = '';
			if ( false !== strpos( current_filter(), '_add_' ) ) {
				$old_type = $this->types()->get_default();
			} elseif ( ! empty( $this->pending_type_changes[ $model->$primary_property ] ) ) {
				$old_type = $this->pending_type_changes[ $model->$primary_property ];
				unset( $this->pending_type_changes[ $model->$primary_property ] );
			}

			if ( ! empty( $old_type ) && ! empty( $model->$type_property ) && $old_type !== $model->$type_property ) {
				$prefix        = $this->get_prefix();
				$singular_slug = $this->get_singular_slug();

				/**
				 * Fires when the type property of a model has changed.
				 *
				 * @since 1.0.0
				 *
				 * @param string $new_type New type of the model.
				 * @param string $old_type Old type of the model.
				 * @param Model  $model    The model object.
				 */
				do_action( "{$prefix}transition_{$singular_slug}_{$type_property}", $model->$type_property, $old_type, $model );
			}

			return $result;
		}
	}

endif;
