<?php
/**
 * Bootstrap for unit tests
 *
 * @package APIAPI
 * @subpackage Tests
 */

define( 'VENDOR_DIR', dirname( dirname( __FILE__ ) ) . '/vendor' );

echo VENDOR_DIR;

require_once VENDOR_DIR . '/autoload.php';