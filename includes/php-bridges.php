<?php
/**
 * PHP bridges to older version of PHP which can be replaced if minimum requirements gettig higher
 *
 * @package TorroForms
 * @version 1.0.0-beta.4
 * @since   1.0.0-beta.4
 */

/*****************************************
 * PHP 5.2
 */

/**
 * array_replace_recursive
 * Copied from php.net docs http://php.net/manual/de/function.array-replace-recursive.php
 */
if ( ! function_exists( 'array_replace_recursive' ) ) {
	function array_replace_recursive( $array, $array1 ) {
		function recurse( $array, $array1 ) {
			foreach ( $array1 as $key => $value ) {
				// create new key in $array, if it is empty or not an array
				if ( ! isset( $array[ $key ] ) || ( isset( $array[ $key ] ) && ! is_array( $array[ $key ] ) ) ) {
					$array[ $key ] = array();
				}

				// overwrite the value in the base array
				if ( is_array( $value ) ) {
					$value = recurse( $array[ $key ], $value );
				}
				$array[ $key ] = $value;
			}

			return $array;
		}

		// handle the arguments, merge one by one
		$args  = func_get_args();
		$array = $args[ 0 ];
		if ( ! is_array( $array ) ) {
			return $array;
		}
		for ( $i = 1; $i < count( $args ); $i ++ ) {
			if ( is_array( $args[ $i ] ) ) {
				$array = recurse( $array, $args[ $i ] );
			}
		}

		return $array;
	}
}

/**
 * ucwords function with delimiter exists at least in 5.5.16
 *
 * @param $string
 * @param $delimiter
 *
 * @return mixed
 */
function torro_ucwords( $string, $delimiter = '' ) {
	if ( version_compare( phpversion(), '5.5.16' ) >= 0 ) {
		return ucwords( $string, $delimiter );
	}

	if ( empty( $delimiter ) ) {
		return ucwords( $string );
	}

	$str_arr = explode( $delimiter, $string );

	$str_arr_new = array();
	foreach ( $str_arr as $string ) {
		$str_arr_new[] = ucwords( $string );
	}

	return implode( $delimiter, $str_arr_new );
}
