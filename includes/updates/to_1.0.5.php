<?php

function torro_forms_to_1_0_5() {
	global $wpdb;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$sql = "ALTER TABLE {$wpdb->prefix}torro_settings RENAME TO {$wpdb->torro_element_settings}";
	$wpdb->query( $sql );
}
