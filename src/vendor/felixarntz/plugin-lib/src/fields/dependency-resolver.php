<?php
/**
 * Dependency_Resolver class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Dependency_Resolver' ) ) :

	/**
	 * Dependency resolver class for fields
	 *
	 * @since 1.0.0
	 */
	class Dependency_Resolver {
		/**
		 * Dependencies definition.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $dependencies = array();

		/**
		 * Whether the dependencies have been resolved.
		 *
		 * @since 1.0.0
		 * @var bool
		 */
		protected $resolved = false;

		/**
		 * Callbacks used for resolving dependencies.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $callbacks = array();

		/**
		 * Field instance.
		 *
		 * @since 1.0.0
		 * @var Field
		 */
		protected $field;

		/**
		 * Field manager instance.
		 *
		 * @since 1.0.0
		 * @var Field_Manager
		 */
		protected $field_manager;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param array         $dependencies  Dependencies definition.
		 * @param Field         $field         The field instance.
		 * @param Field_Manager $field_manager The field manager instance.
		 */
		public function __construct( $dependencies, $field, $field_manager ) {
			$this->field         = $field;
			$this->field_manager = $field_manager;

			$this->dependencies = $this->parse_dependencies( $dependencies );
		}

		/**
		 * Resolves all dependencies.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of field properties and their resolved values.
		 */
		public function resolve_dependencies() {
			$results = array();

			$callbacks   = $this->get_callbacks();
			$instance_id = $this->field_manager->get_instance_id();
			$values      = $this->field_manager->get_values();

			foreach ( $this->dependencies as $dependency ) {
				if ( ! isset( $callbacks[ $dependency['callback'] ] ) ) {
					continue;
				}

				$callback = $callbacks[ $dependency['callback'] ];

				$field_values = array();
				foreach ( $dependency['fieldNames'] as $slug ) {
					if ( ! isset( $values[ $slug ] ) ) {
						$values[ $slug ] = null;
					}

					$field_values[ $slug ] = $values[ $slug ];
				}

				$result = call_user_func( $callback, $dependency['prop'], $field_values, $dependency['args'] );

				if ( null === $result ) {
					continue;
				}

				$results[ $dependency['prop'] ] = $result;
			}

			$this->resolved = true;

			return $results;
		}

		/**
		 * Returns whether the dependencies have been resolved at least once.
		 *
		 * @since 1.0.0
		 *
		 * @return bool True if dependencies have been resolved, false otherwise.
		 */
		public function resolved() {
			return $this->resolved;
		}

		/**
		 * Returns the field dependency definition.
		 *
		 * @since 1.0.0
		 *
		 * @return array Dependency definition.
		 */
		public function get_dependencies() {
			return $this->dependencies;
		}

		/**
		 * Returns the field properties that depend on other fields.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of properties.
		 */
		public function get_dependency_props() {
			$props = array();

			foreach ( $this->dependencies as $dependency ) {
				$props[] = $dependency['prop'];
			}

			return array_unique( $props );
		}

		/**
		 * Returns the identifiers of the field that this field depends on.
		 *
		 * @since 1.0.0
		 *
		 * @param string|array $props Optional. One or more properties to only return field identifiers
		 *                            that affect those. Default empty.
		 * @return array Array of field identifiers.
		 */
		public function get_dependency_field_identifiers( $props = array() ) {
			$field_identifiers = array();

			$props = (array) $props;

			foreach ( $this->dependencies as $dependency ) {
				if ( ! empty( $props ) && ! in_array( $dependency['prop'], $props, true ) ) {
					continue;
				}

				foreach ( $dependency['fieldNames'] as $identifier ) {
					$field_identifiers[] = $identifier;
				}
			}

			return array_unique( $field_identifiers );
		}

		/**
		 * Parses the dependencies for the field.
		 *
		 * @since 1.0.0
		 *
		 * @param array $dependencies Array of dependency arrays.
		 * @return array Parsed dependencies array.
		 */
		public function parse_dependencies( $dependencies ) {
			return array_filter( array_map( array( $this, 'parse_dependency' ), $dependencies ) );
		}

		/**
		 * Parses a single dependency array.
		 *
		 * @since 1.0.0
		 *
		 * @param array $dependency Dependency array.
		 * @return array|bool Parsed dependency array, or false if invalid.
		 */
		protected function parse_dependency( $dependency ) {
			if ( ! is_array( $dependency ) ) {
				return false;
			}

			$required_args = array( 'prop', 'callback', 'fields' );

			foreach ( $required_args as $required_arg ) {
				if ( empty( $dependency[ $required_arg ] ) ) {
					return false;
				}
			}

			$dependency['fieldNames'] = $dependency['fields'];

			$fields = array();
			foreach ( $dependency['fields'] as $field ) {
				$fields[] = $this->field_manager->make_id( $field );
			}
			$dependency['fields'] = $fields;

			$dependency_prop_whitelist = $this->get_dependency_prop_whitelist();
			if ( ! in_array( $dependency['prop'], $dependency_prop_whitelist, true ) ) {
				return false;
			}

			if ( empty( $dependency['args'] ) ) {
				$dependency['args'] = array();
			}

			return $dependency;
		}

		/**
		 * Gets the whitelist for properties to be handled by dependencies.
		 *
		 * @since 1.0.0
		 *
		 * @return array Key whitelist.
		 */
		protected function get_dependency_prop_whitelist() {
			$whitelist = array( 'label', 'description', 'display' );

			if ( ! $this->field->repeatable ) {
				$whitelist = array_merge( $whitelist, array( 'default', 'choices', 'optgroups', 'unit' ) );
			}

			return array_filter( $whitelist, array( $this, 'field_property_exists' ) );
		}

		/**
		 * Checks whether a property exists on the field.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prop Key to check for.
		 * @return bool True if the property exists on the field, otherwise false.
		 */
		protected function field_property_exists( $prop ) {
			return isset( $this->field->$prop );
		}

		/**
		 * Returns the available callbacks for resolving field dependencies.
		 *
		 * @since 1.0.0
		 *
		 * @return array Associative array of callback identifiers and their functions.
		 */
		protected function get_callbacks() {
			$callbacks = array(
				'get_data_by_condition_true'         => array( $this, 'get_data_by_condition_true' ),
				'get_data_by_condition_false'        => array( $this, 'get_data_by_condition_false' ),
				'get_data_by_condition_greater_than' => array( $this, 'get_data_by_condition_greater_than' ),
				'get_data_by_condition_lower_than'   => array( $this, 'get_data_by_condition_lower_than' ),
				'get_data_by_map'                    => array( $this, 'get_data_by_map' ),
				'get_data_by_named_map'              => array( $this, 'get_data_by_named_map' ),
			);

			/**
			 * Filters the available callbacks for resolving field dependencies.
			 *
			 * @since 1.0.0
			 *
			 * @param array $callbacks Associative array of callback identifiers and their functions.
			 */
			return apply_filters( 'plugin_lib_dependency_resolver_callbacks', $callbacks );
		}

		/**
		 * Callback depending on whether the depending fields' values are true-ish.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prop         Property that is modified.
		 * @param array  $field_values Array of depending field identifiers and their current values.
		 * @param array  $args         {
		 *
		 *     Additional arguments.
		 *
		 *     @type mixed  $result_true  Result to return in case the conditions are met. Default true.
		 *     @type mixed  $result_false Result to return in case the conditions are not met. Default false.
		 *     @type string $operator     Operator for checking the conditions when passing multiple fields.
		 *                                Either 'AND' or 'OR'. Default 'AND'.
		 * }
		 * @return mixed Content of the $result_true argument if conditions are met, otherwise content of the
		 *               $result_false argument.
		 */
		protected function get_data_by_condition_true( $prop, $field_values, $args ) {
			return $this->get_data_by_condition_bool_helper( $prop, $field_values, $args, false );
		}

		/**
		 * Callback depending on whether the depending fields' values are false-ish.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prop         Property that is modified.
		 * @param array  $field_values Array of depending field identifiers and their current values.
		 * @param array  $args         {
		 *
		 *     Additional arguments.
		 *
		 *     @type mixed  $result_true  Result to return in case the conditions are met. Default true.
		 *     @type mixed  $result_false Result to return in case the conditions are not met. Default false.
		 *     @type string $operator     Operator for checking the conditions when passing multiple fields.
		 *                                Either 'AND' or 'OR'. Default 'AND'.
		 * }
		 * @return mixed Content of the $result_true argument if conditions are met, otherwise content of the
		 *               $result_false argument.
		 */
		protected function get_data_by_condition_false( $prop, $field_values, $args ) {
			return $this->get_data_by_condition_bool_helper( $prop, $field_values, $args, true );
		}

		/**
		 * Callback helper for `get_data_by_condition_true` and `get_data_by_condition_false`.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prop         Property that is modified.
		 * @param array  $field_values Array of depending field identifiers and their current values.
		 * @param array  $args         {
		 *
		 *     Additional arguments.
		 *
		 *     @type mixed  $result_true  Result to return in case the conditions are met. Default true.
		 *     @type mixed  $result_false Result to return in case the conditions are not met. Default false.
		 *     @type string $operator     Operator for checking the conditions when passing multiple fields.
		 *                                Either 'AND' or 'OR'. Default 'AND'.
		 * }
		 * @param bool   $reverse      Optional. Whether to reverse the checks. Default false.
		 * @return mixed Content of the $result_true argument if conditions are met, otherwise content of the
		 *               $result_false argument.
		 */
		protected function get_data_by_condition_bool_helper( $prop, $field_values, $args, $reverse = false ) {
			$operator = ( isset( $args['operator'] ) && strtoupper( $args['operator'] ) === 'OR' ) ? 'OR' : 'AND';

			if ( $reverse ) {
				$result_false = isset( $args['result_true'] ) ? $args['result_true'] : true;
				$result_true  = isset( $args['result_false'] ) ? $args['result_false'] : false;
			} else {
				$result_false = isset( $args['result_false'] ) ? $args['result_false'] : false;
				$result_true  = isset( $args['result_true'] ) ? $args['result_true'] : true;
			}

			if ( 'OR' === $operator ) {
				foreach ( $field_values as $identifier => $value ) {
					if ( $value ) {
						return $result_true;
					}
				}

				return $result_false;
			}

			foreach ( $field_values as $identifier => $value ) {
				if ( ! $value ) {
					return $result_false;
				}
			}

			return $result_true;
		}

		/**
		 * Callback depending on whether the depending fields' values are greater than a given breakpoint.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prop         Property that is modified.
		 * @param array  $field_values Array of depending field identifiers and their current values.
		 * @param array  $args         {
		 *
		 *     Additional arguments.
		 *
		 *     @type int|float $breakpoint   Breakpoint to check. Default float 0.
		 *     @type bool      $inclusive    Whether the check should be inclusive. Default false.
		 *     @type mixed     $result_true  Result to return in case the conditions are met. Default true.
		 *     @type mixed     $result_false Result to return in case the conditions are not met. Default false.
		 *     @type string    $operator     Operator for checking the conditions when passing multiple fields.
		 *                                   Either 'AND' or 'OR'. Default 'AND'.
		 * }
		 * @return mixed Content of the $result_true argument if conditions are met, otherwise content of the
		 *               $result_false argument.
		 */
		protected function get_data_by_condition_greater_than( $prop, $field_values, $args ) {
			return $this->get_data_by_condition_numeric_comparison_helper( $prop, $field_values, $args, false );
		}

		/**
		 * Callback depending on whether the depending fields' values are lower than a given breakpoint.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prop         Property that is modified.
		 * @param array  $field_values Array of depending field identifiers and their current values.
		 * @param array  $args         {
		 *
		 *     Additional arguments.
		 *
		 *     @type int|float $breakpoint   Breakpoint to check. Default float 0.
		 *     @type bool      $inclusive    Whether the check should be inclusive. Default false.
		 *     @type mixed     $result_true  Result to return in case the conditions are met. Default true.
		 *     @type mixed     $result_false Result to return in case the conditions are not met. Default false.
		 *     @type string    $operator     Operator for checking the conditions when passing multiple fields.
		 *                                   Either 'AND' or 'OR'. Default 'AND'.
		 * }
		 * @return mixed Content of the $result_true argument if conditions are met, otherwise content of the
		 *               $result_false argument.
		 */
		protected function get_data_by_condition_lower_than( $prop, $field_values, $args ) {
			return $this->get_data_by_condition_numeric_comparison_helper( $prop, $field_values, $args, true );
		}

		/**
		 * Callback helper for `get_data_by_condition_greater_than` and `get_data_by_condition_lower_than`.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prop         Property that is modified.
		 * @param array  $field_values Array of depending field identifiers and their current values.
		 * @param array  $args         {
		 *
		 *     Additional arguments.
		 *
		 *     @type int|float $breakpoint   Breakpoint to check. Default float 0.
		 *     @type bool      $inclusive    Whether the check should be inclusive. Default false.
		 *     @type mixed     $result_true  Result to return in case the conditions are met. Default true.
		 *     @type mixed     $result_false Result to return in case the conditions are not met. Default false.
		 *     @type string    $operator     Operator for checking the conditions when passing multiple fields.
		 *                                   Either 'AND' or 'OR'. Default 'AND'.
		 * }
		 * @param bool   $reverse      Optional. Whether to reverse the checks. Default false.
		 * @return mixed Content of the $result_true argument if conditions are met, otherwise content of the
		 *               $result_false argument.
		 */
		protected function get_data_by_condition_numeric_comparison_helper( $prop, $field_values, $args, $reverse = false ) {
			$operator = ( isset( $args['operator'] ) && strtoupper( $args['operator'] ) === 'OR' ) ? 'OR' : 'AND';

			if ( $reverse ) {
				$result_false = isset( $args['result_true'] ) ? $args['result_true'] : true;
				$result_true  = isset( $args['result_false'] ) ? $args['result_false'] : false;
			} else {
				$result_false = isset( $args['result_false'] ) ? $args['result_false'] : false;
				$result_true  = isset( $args['result_true'] ) ? $args['result_true'] : true;
			}

			$breakpoint = 0.0;
			$sanitize   = 'floatval';
			if ( isset( $args['breakpoint'] ) ) {
				if ( is_int( $args['breakpoint'] ) ) {
					$sanitize = 'intval';
				} elseif ( is_string( $args['breakpoint'] ) && preg_match( '/^((\d{4}-\d{2}-\d{2})|(\d{2}:\d{2}:\d{2})|(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}))$/', $args['breakpoint'] ) ) {
					$sanitize = 'strtotime';
				}

				$breakpoint = call_user_func( $sanitize, $args['breakpoint'] );
			}

			$inclusive = isset( $args['inclusive'] ) ? (bool) $args['inclusive'] : false;
			if ( $reverse ) {
				$inclusive = ! $inclusive;
			}

			if ( 'OR' === $operator ) {
				foreach ( $field_values as $identifier => $value ) {
					$value = call_user_func( $sanitize, $value );

					if ( $value > $breakpoint || $value === $breakpoint && $inclusive ) {
						return $result_true;
					}
				}

				return $result_false;
			}

			foreach ( $field_values as $identifier => $value ) {
				$value = call_user_func( $sanitize, $value );

				if ( $value < $breakpoint || $value === $breakpoint && ! $inclusive ) {
					return $result_false;
				}
			}

			return $result_true;
		}

		/**
		 * Callback depending on a map of depending field values and what they should trigger.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prop         Property that is modified.
		 * @param array  $field_values Array of depending field identifiers and their current values.
		 * @param array  $args         {
		 *
		 *     Additional arguments.
		 *
		 *     @type array  $map      Map array of `$value => $result` pairs.
		 *     @type mixed  $default  Default result to use if nothing is matched.
		 *     @type bool   $merge    When multiple fields are provided, this flag determines whether all
		 *                            their value results should be combined or whether only one value should
		 *                            be treated. Only works for array and bool results.
		 *     @type string $operator Operator for merging the results when passing multiple fields. Only has
		 *                            an effect if $merge is true. Either 'AND' or 'OR'. Default 'AND'.
		 * }
		 * @return mixed Result, or null if nothing matched and no default has been provided.
		 */
		protected function get_data_by_map( $prop, $field_values, $args ) {
			$default = isset( $args['default'] ) ? $args['default'] : null;

			if ( empty( $args['map'] ) ) {
				return $default;
			}

			$map      = $args['map'];
			$merge    = isset( $args['merge'] ) ? (bool) $args['merge'] : false;
			$operator = ( isset( $args['operator'] ) && strtoupper( $args['operator'] ) === 'OR' ) ? 'OR' : 'AND';

			$result = null;

			$used_values = array();
			foreach ( $field_values as $identifier => $value ) {
				if ( ! isset( $map[ $value ] ) ) {
					continue;
				}

				if ( $merge && ! in_array( $value, $used_values, true ) ) {
					$used_values[] = $value;
					$result        = $this->merge_into_result( $result, $map[ $value ], $operator );
				} else {
					$used_values[] = $value;
					$result        = $map[ $value ];
				}
			}

			if ( null === $result ) {
				return $default;
			}

			return $result;
		}

		/**
		 * Callback depending on a named map of depending field values and what they should trigger.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prop         Property that is modified.
		 * @param array  $field_values Array of depending field identifiers and their current values.
		 * @param array  $args         {
		 *
		 *     Additional arguments.
		 *
		 *     @type array  $named_map Named map array of `$identifier => $map` pairs, where each $map is an array of
		 *                             `$value => $result` pairs.
		 *     @type mixed  $default   Default result to use if nothing is matched.
		 *     @type bool   $merge     When multiple fields are provided, this flag determines whether all
		 *                             their value results should be combined or whether only one value should
		 *                             be treated. Only works for array and bool results.
		 *     @type string $operator  Operator for merging the results when passing multiple fields. Only has
		 *                             an effect if $merge is true. Either 'AND' or 'OR'. Default 'AND'.
		 * }
		 * @return mixed Result, or null if nothing matched and no default has been provided.
		 */
		protected function get_data_by_named_map( $prop, $field_values, $args ) {
			$default = isset( $args['default'] ) ? $args['default'] : null;

			if ( empty( $args['named_map'] ) ) {
				return $default;
			}

			$named_map = $args['named_map'];
			$merge     = isset( $args['merge'] ) ? (bool) $args['merge'] : false;
			$operator  = ( isset( $args['operator'] ) && strtoupper( $args['operator'] ) === 'OR' ) ? 'OR' : 'AND';

			$result = null;

			$used_values = array();
			foreach ( $field_values as $identifier => $value ) {
				if ( ! isset( $named_map[ $identifier ] ) ) {
					continue;
				}

				$map = $named_map[ $identifier ];

				$used_values[ $identifier ] = array();
				if ( ! isset( $map[ $value ] ) ) {
					continue;
				}

				if ( $merge && ! in_array( $value, $used_values[ $identifier ], true ) ) {
					$used_values[ $identifier ][] = $value;
					$result                       = $this->merge_into_result( $result, $map[ $value ], $operator );
				} else {
					$used_values[ $identifier ][] = $value;
					$result                       = $map[ $value ];
				}
			}

			if ( null === $result ) {
				return $default;
			}

			return $result;
		}

		/**
		 * Merges a value into an existing result.
		 *
		 * Only merges if both values have the same data types.
		 *
		 * If the values cannot be merged, $value will simply override $result.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed  $result   Result to merge into.
		 * @param mixed  $value    Value to merge into $result.
		 * @param string $operator Either 'OR' or 'AND'.
		 * @return mixed Merged result.
		 */
		protected function merge_into_result( $result, $value, $operator ) {
			if ( is_array( $result ) && isset( $result[0] ) && is_array( $value ) && isset( $value[0] ) ) {
				if ( 'OR' === $operator ) {
					$result = array_unique( array_merge( $result, $value ) );
				} else {
					$result = array_unique( array_merge( array_intersect( $result, $value ), array_intersect( $value, $result ) ) );
				}

				return $result;
			}

			if ( is_array( $result ) && is_array( $value ) ) {
				if ( 'OR' === $operator ) {
					$result = array_merge( $result, $value );
				} else {
					$result = array_merge( array_intersect_key( $result, $value ), array_intersect_key( $value, $result ) );
				}

				return $result;
			}

			if ( is_bool( $result ) && is_bool( $value ) ) {
				if ( 'OR' === $operator ) {
					$result = $result || $value;
				} else {
					$result = $result && $value;
				}

				return $result;
			}

			return $value;
		}
	}

endif;
