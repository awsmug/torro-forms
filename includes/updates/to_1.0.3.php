<?php

function awesome_forms_to_1_0_3() {
	global $wpdb;

	$table_elements = $wpdb->prefix . 'torro_elements';

	$sql = "UPDATE {$table_elements} SET type='textfield' WHERE type='Text'";
	$wpdb->query( $sql );
}