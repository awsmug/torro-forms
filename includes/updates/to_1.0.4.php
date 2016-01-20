<?php

function torro_forms_to_1_0_4() {
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

	add_action( 'admin_notices', 'torro_forms_rewrite_form_splitters_to_containers' );
}

function torro_forms_rewrite_form_splitters_to_containers() {
	global $wpdb;

	$sql   = $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE post_type=%s", 'torro-forms' );
	$forms = $wpdb->get_results( $sql );

	foreach ( $forms AS $form ) {
		$sql      = $wpdb->prepare( "SELECT * FROM {$wpdb->torro_elements} WHERE form_id=%d ORDER BY sort ASC", $form->ID );
		$elements = $wpdb->get_results( $sql );

		$container_num = 0;

		$sql = $wpdb->prepare( "INSERT INTO {$wpdb->torro_containers} (form_id,label,sort) VALUES (%d,%s,%d)", $form->ID, $label, $container_num );
		$wpdb->query( $sql );

		$container_id = $wpdb->insert_id;

		foreach ( $elements AS $element ) {
			if( 'splitter' === $element->type ){
				$container_num++;

				$sql = $wpdb->prepare( "INSERT INTO {$wpdb->torro_containers} (form_id,label,sort) VALUES (%d,%s,%d)", $form->ID, $label, $container_num );
				$wpdb->query( $sql );

				$container_id = $wpdb->insert_id;

				continue;
			}

			$label = __( 'Page', 'torro-forms' ) . ' ' . ( $container_num + 1 );

			$sql = $wpdb->prepare( "UPDATE {$wpdb->torro_elements} SET container_id=%d WHERE id=%d", $container_id, $element->id );

			$wpdb->query( $sql );
		}
	}
}