<?php
/**
 * API-API Config interface
 *
 * @package APIAPI\Core
 * @since 1.0.0
 */

namespace APIAPI\Core;

if ( ! interface_exists( 'APIAPI\Core\Config_Interface' ) ) {

	/**
	 * Config interface for the API-API.
	 *
	 * @since 1.0.0
	 */
	interface Config_Interface {
		/**
		 * Checks whether a specific parameter is set.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param    Name of the parameter.
		 * @param string $subparam Optional. Name of a sub parameter. Default null.
		 * @return bool True if the parameter is set, false otherwise.
		 */
		public function exists( $param, $subparam = null );

		/**
		 * Returns the value for a specific parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param    Name of the parameter.
		 * @param string $subparam Optional. Name of a sub parameter. Default null.
		 * @return mixed Value of the parameter, or null if it is not set.
		 */
		public function get( $param, $subparam = null );

		/**
		 * Sets a specific parameter to a given value.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param             Name of the parameter.
		 * @param string $value_or_subparam Either new value for the parameter or, when
		 *                                  setting a sub parameter, the name of the sub
		 *                                  parameter.
		 * @param mixed  $value             Optional. The value when setting a sub
		 *                                  parameter. Default null.
		 */
		public function set( $param, $value_or_subparam, $value = null );

		/**
		 * Deletes a specific parameter.
		 *
		 * It is not possible to delete default parameters.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param Name of the parameter.
		 * @param string $subparam Optional. Name of a sub parameter. Default null.
		 */
		public function delete( $param, $subparam = null );

		/**
		 * Sets multiple parameters with their values.
		 *
		 * If the parameters are set for the first time or if the $reset parameter is set to true,
		 * unprovided parameters will be filled with their default values.
		 *
		 * @since 1.0.0
		 *
		 * @param array $params Associative array of config parameters with their values.
		 * @param bool  $reset  Optional. Whether to reset all parameters to the specified ones. Default false.
		 */
		public function set_params( array $params, $reset = false );

		/**
		 * Returns all parameters with their values as an associative array.
		 *
		 * @since 1.0.0
		 *
		 * @return array Associative array of config parameters with their values.
		 */
		public function get_params();
	}

}
