<?php
/**
 * Trait for managers that support dates
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Traits;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Date_Manager_Trait' ) ) :

	/**
	 * Trait for managers.
	 *
	 * Include this trait for managers that support dates.
	 *
	 * @since 1.0.0
	 */
	trait Date_Manager_Trait {
		/**
		 * The date property of the model.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $date_property = 'date';

		/**
		 * Array of secondary date properties in the model.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $secondary_date_properties = array();

		/**
		 * Returns the name of the date property in a model.
		 *
		 * @since 1.0.0
		 *
		 * @return string Name of the date property.
		 */
		public function get_date_property() {
			return $this->date_property;
		}

		/**
		 * Returns the names for any secondary date properties, if any.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of secondary date properties.
		 */
		public function get_secondary_date_properties() {
			return $this->secondary_date_properties;
		}

		/**
		 * Returns the names of both the primary date property and the secondary date properties.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of date properties.
		 */
		public function get_all_date_properties() {
			return array_merge( array( $this->date_property ), $this->secondary_date_properties );
		}

		/**
		 * Sets a date on a model.
		 *
		 * @since 1.0.0
		 *
		 * @param Model  $model    The model to set a date on.
		 * @param string $property Name of the date property.
		 * @param string $date     The date to set.
		 */
		public function set_date_with_gmt( $model, $property, $date ) {
			if ( ! in_array( $property, $this->get_all_date_properties(), true ) ) {
				return;
			}

			$model->$property = $date;

			$is_gmt = '_gmt' === substr( $property, -4 );

			$other_property = $is_gmt ? substr( $property, 0, -4 ) : $property . '_gmt';

			if ( ! in_array( $other_property, $this->get_all_date_properties(), true ) ) {
				return;
			}

			$other_date = $is_gmt ? get_date_from_gmt( $date ) : get_gmt_from_date( $date );

			$model->$other_property = $other_date;
		}

		/**
		 * Sets the date property on a model if it isn't set already.
		 *
		 * @since 1.0.0
		 *
		 * @param null  $ret   Return value from the filter.
		 * @param Model $model The model to modify.
		 * @return null The unmodified pre-filter value.
		 */
		public function maybe_set_date_property( $ret, $model ) {
			$date_property = $this->get_date_property();

			if ( empty( $model->$date_property ) || '0000-00-00 00:00:00' === $model->$date_property ) {
				$date = current_time( 'mysql', '_gmt' === substr( $date_property, -4 ) );

				$this->set_date_with_gmt( $model, $date_property, $date );
			}

			return $ret;
		}
	}

endif;
