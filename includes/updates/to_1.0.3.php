<?php

function torro_forms_to_1_0_3() {
	global $wpdb;

	$sql = "UPDATE $wpdb->torro_elements SET type='textfield' WHERE type='Text'";
	$wpdb->query( $sql );
}
