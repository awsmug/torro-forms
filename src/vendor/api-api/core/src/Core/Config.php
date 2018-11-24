<?php
/**
 * API-API Config class
 *
 * @package APIAPI\Core
 * @since 1.0.0
 */

namespace APIAPI\Core;

if ( ! class_exists( 'APIAPI\Core\Config' ) ) {

	/**
	 * Config class for the API-API.
	 *
	 * @since 1.0.0
	 */
	class Config implements Config_Interface {
		/**
		 * Array of config parameters.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $params = array();

		/**
		 * Constructor.
		 *
		 * Allows to set the config parameters.
		 *
		 * @since 1.0.0
		 *
		 * @param array $params Optional. Associative array of config parameters with their values. Default empty.
		 */
		public function __construct( array $params = null ) {
			if ( is_array( $params ) ) {
				$this->set_params( $params );
			}
		}

		/**
		 * Checks whether a specific parameter is set.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param    Name of the parameter.
		 * @param string $subparam Optional. Name of a sub parameter. Default null.
		 * @return bool True if the parameter is set, false otherwise.
		 */
		public function exists( $param, $subparam = null ) {
			if ( ! isset( $this->params[ $param ] ) ) {
				return false;
			}

			if ( ! is_null( $subparam ) && ! isset( $this->params[ $param ][ $subparam ] ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Returns the value for a specific parameter.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param    Name of the parameter.
		 * @param string $subparam Optional. Name of a sub parameter. Default null.
		 * @return mixed Value of the parameter, or null if it is not set.
		 */
		public function get( $param, $subparam = null ) {
			if ( ! $this->exists( $param, $subparam ) ) {
				return null;
			}

			if ( ! is_null( $subparam ) ) {
				return $this->params[ $param ][ $subparam ];
			}

			return $this->params[ $param ];
		}

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
		public function set( $param, $value_or_subparam, $value = null ) {
			$subparam = ! is_null( $value ) ? $value_or_subparam : null;
			$value    = ! is_null( $value ) ? $value : $value_or_subparam;

			if ( ! is_null( $subparam ) ) {
				if ( ! $this->exists( $param ) ) {
					$this->params[ $param ] = array();
				}

				$this->params[ $param ][ $subparam ] = $value;
			} else {
				$this->params[ $param ] = $value;
			}
		}

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
		public function delete( $param, $subparam = null ) {
			if ( ! $this->exists( $param, $subparam ) ) {
				return;
			}

			$defaults = $this->get_defaults();

			if ( ! is_null( $subparam ) ) {
				// Do not allow removal of default parameters.
				if ( array_key_exists( $param, $defaults ) && array_key_exists( $subparam, $defaults[ $param ] ) ) {
					return;
				}

				unset( $this->params[ $param ][ $subparam ] );
			} else {
				// Do not allow removal of default parameters.
				if ( array_key_exists( $param, $defaults ) ) {
					return;
				}

				unset( $this->params[ $param ] );
			}
		}

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
		public function set_params( array $params, $reset = false ) {
			if ( empty( $this->params ) || $reset ) {
				$this->params = Util::parse_args( $params, $this->get_defaults() );
			} else {
				$this->params = Util::parse_args( $this->params, $params );
			}
		}

		/**
		 * Returns all parameters with their values as an associative array.
		 *
		 * @since 1.0.0
		 *
		 * @return array Associative array of config parameters with their values.
		 */
		public function get_params() {
			return $this->params;
		}

		/**
		 * Returns the default parameters with their values.
		 *
		 * @since 1.0.0
		 *
		 * @return array Associative array of default config parameters with their values.
		 */
		protected function get_defaults() {
			return array(
				'transporter'            => '',
				'config_updater'         => false,
				'config_updater_storage' => '',
			);
		}
	}

}
