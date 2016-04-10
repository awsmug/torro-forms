<?php

function torro_forms_to_1_0_6() {
	global $wpdb;

	$wpdb->update( $wpdb->posts, array(
		'post_type'	=> 'torro_form',
	), array(
		'post_type'	=> 'torro-forms',
	), array( '%s' ), array( '%s' ) );

	$wpdb->update( $wpdb->term_taxonomy, array(
		'taxonomy'	=> 'torro_form_category',
	), array(
		'taxonomy'	=> 'torro-forms-categories',
	), array( '%s' ), array( '%s' ) );
}
