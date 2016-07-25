<?php

function torro_forms_to_1_0_8() {
	global $wpdb;

	$wpdb->query( "ALTER TABLE {$wpdb->torro_email_notifications} ADD reply_email TEXT NOT NULL AFTER from_email" );
}
