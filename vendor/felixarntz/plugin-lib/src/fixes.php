<?php
/**
 * Fixes class.
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.5
 */

namespace Leaves_And_Love\Plugin_Lib;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\Traits\Fixes_Trait' ) ) :

	/**
	 * Class for Fixes.
	 *
	 * This is a class which contains fixes for known bugs of 3rd party code.
	 *
	 * @since 1.0.5
	 */
	class Fixes {
		/**
		 * PHP filter_input function fix.
		 * 
		 * Wrapper for filter_input INPUT_SERVER/INPUT_ENV bug in PHP in fast-cgi configured php installations.
		 * @see https://php.net/manual/de/function.filter-input.php#77307
		 * 
		 * @since 1.0.5
		 * 
		 * @param int    $type          One of INPUT_SERVER or INPUT_ENV.
		 * @param string $variable_name Name of a variable to get.
		 * @param int    $filter        The ID of the filter to apply.
		 * @param mixed  $options       Associative array of options or bitwise disjunction of flags. 
		 * 
		 * @return mixed $value         Value of the requested variable on success, FALSE if the filter fails, or NULL if the variable_name variable is not set.
		 */
		public static function php_filter_input( $type, $variable_name, $filter = FILTER_DEFAULT, $options = null  ) {
			$value = filter_input( $type, $variable_name, $filter, $options );

			if ( null !== $value ) {
				return $value;
			}

			if ( INPUT_SERVER === $type && array_key_exists( $variable_name, $_SERVER ) ) {
				return filter_var( wp_unslash( $_SERVER[ $variable_name ] ), $filter, $options );
			}

			if ( INPUT_ENV === $type && array_key_exists( $variable_name, $_SERVER ) ) {
				return filter_var( wp_unslash( $_ENV[ $variable_name ] ), $filter, $options );
			}

			return $value;
		}
	}

endif;
