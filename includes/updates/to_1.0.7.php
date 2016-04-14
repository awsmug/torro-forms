<?php

function torro_forms_to_1_0_7() {
	global $wpdb;

	$wpdb->query( "ALTER TABLE {$wpdb->torro_elements} DROP form_id" );
}
