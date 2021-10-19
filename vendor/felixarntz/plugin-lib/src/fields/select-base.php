<?php
/**
 * Select field base class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Select_Base' ) ) :

	/**
	 * Base class for any select field.
	 *
	 * @since 1.0.0
	 */
	abstract class Select_Base extends Field {
		/**
		 * Available choices to select from.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $choices = array();

		/**
		 * Whether this field accepts multiple values.
		 *
		 * @since 1.0.0
		 * @var bool
		 */
		protected $multi = false;

		/**
		 * Transforms single field data into an array to be passed to JavaScript applications.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $current_value Current value of the field.
		 * @return array Field data to be JSON-encoded.
		 */
		protected function single_to_json( $current_value ) {
			$data            = parent::single_to_json( $current_value );
			$data['choices'] = $this->choices;

			if ( $this->multi ) {
				$data['name'] .= '[]';
			}

			return $data;
		}

		/**
		 * Validates a single value for the field.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $value Value to validate. When null is passed, the method
		 *                     assumes no value was sent.
		 * @return mixed|WP_Error The validated value on success, or an error
		 *                        object on failure.
		 */
		protected function validate_single( $value = null ) {
			if ( empty( $value ) ) {
				if ( $this->multi ) {
					return array();
				}

				return '';
			}

			if ( $this->multi ) {
				$value = array_map( 'trim', (array) $value );

				$checked_value = array_intersect( $value, array_keys( $this->choices ) );
				if ( count( $value ) > count( $checked_value ) ) {
					$error_data = array();
					if ( ! empty( $checked_value ) ) {
						$error_data['validated'] = $checked_value;
					}

					return new WP_Error( 'field_select_invalid_multi', sprintf( $this->manager->get_message( 'field_select_invalid_multi' ), implode( ', ', $value ), $this->label ), $error_data );
				}

				return $value;
			}

			$value = trim( $value );

			if ( ! isset( $this->choices[ $value ] ) ) {
				return new WP_Error( 'field_select_invalid', sprintf( $this->manager->get_message( 'field_select_invalid' ), $value, $this->label ) );
			}

			return $value;
		}

		/**
		 * Returns the attributes for the field's input.
		 *
		 * @since 1.0.0
		 *
		 * @param array $input_attrs Array of custom input attributes.
		 * @param bool  $as_string   Optional. Whether to return them as an attribute
		 *                           string. Default true.
		 * @return array|string Either an array of `$key => $value` pairs, or an
		 *                      attribute string if `$as_string` is true.
		 */
		protected function get_input_attrs( $input_attrs = array(), $as_string = true ) {
			$all_input_attrs = parent::get_input_attrs( $input_attrs, false );

			if ( $this->multi ) {
				$all_input_attrs['name'] .= '[]';
			}

			if ( $as_string ) {
				return $this->attrs( $all_input_attrs );
			}

			return $all_input_attrs;
		}

		/**
		 * Returns names of the properties that must not be set through constructor arguments.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of forbidden properties.
		 */
		protected function get_forbidden_keys() {
			$keys   = parent::get_forbidden_keys();
			$keys[] = 'multi';

			return $keys;
		}
	}

endif;
