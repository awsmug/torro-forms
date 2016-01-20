<?php

function awesome_forms_to_1_0_4() {
	global $wpdb;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$charset_collate = Torro_Init::get_charset_collate();

	$sql = "CREATE TABLE $wpdb->torro_containers (
		id int(11) NOT NULL AUTO_INCREMENT,
		form_id int(11) NOT NULL,
		label text NOT NULL,
		sort int(11) NOT NULL,
		UNIQUE KEY id (id)
		) ENGINE = INNODB " . $charset_collate . ";";

	dbDelta( $sql );

	$sql = "ALTER TABLE {$wpdb->torro_elements} ADD container_id INT(11) NOT NULL AFTER form_id";
	$wpdb->query( $sql );
}