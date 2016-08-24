<?php

function torro_forms_to_1_0_9() {
	global $wpdb;

	$wpdb->update(
		$wpdb->torro_elements,
		array(
			'label' => '<hr />',
			'type'  => 'content'
		),
		array(
			'type'  => 'separator'
		)
	);
}
