<?php
/**
 * PHP bridges to older version of PHP which can be replaced if minimum requirements gettig higher
 *
 * @package TorroForms
 * @version 1.0.0-beta.3
 * @since 1.0.0-beta.4
 */

/*****************************************
 * PHP 5.2
 */

/**
 * ucwords function with delimiter exists at least in 5.3
 *
 * @param $string
 * @param $delimiter
 *
 * @return mixed
 */
function torro_ucwords( $string, $delimiter ){
	$str_arr = explode( $delimiter, $string );

	$str_arr_new = array();
	foreach ( $str_arr AS $string ) {
		$str_arr_new[] = ucwords( $string );
	}

	return implode( $delimiter, $str_arr_new );
}
