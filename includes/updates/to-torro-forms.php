<?php

function awesome_forms_to_torro_forms() {
	global $wpdb;

	$table_elements_old = $wpdb->prefix . 'af_elements';
	$table_answers_old = $wpdb->prefix . 'af_element_answers';
	$table_settings_old = $wpdb->prefix . 'af_settings';
	$table_responds_old = $wpdb->prefix . 'af_results';
	$table_respond_answers_old= $wpdb->prefix . 'af_result_values';
	$table_participiants_old= $wpdb->prefix . 'af_participiants';
	$table_email_notifications_old = $wpdb->prefix . 'af_email_notifications';

	$table_elements_new = $wpdb->prefix . 'torro_elements';
	$table_answers_new = $wpdb->prefix . 'torro_element_answers';
	$table_settings_new = $wpdb->prefix . 'torro_settings';
	$table_responds_new = $wpdb->prefix . 'torro_results';
	$table_respond_answers_new = $wpdb->prefix . 'torro_result_values';
	$table_participiants_new = $wpdb->prefix . 'torro_participiants';
	$table_email_notifications_new = $wpdb->prefix . 'torro_email_notifications';

	$sql = "RENAME TABLE {$table_elements_old} TO {$table_elements_new}";
	$wpdb->query( $sql );

	$sql = "RENAME TABLE {$table_answers_old} TO {$table_answers_new}";
	$wpdb->query( $sql );

	$sql = "RENAME TABLE {$table_settings_old} TO {$table_settings_new}";
	$wpdb->query( $sql );

	$sql = "RENAME TABLE {$table_responds_old} TO {$table_responds_new}";
	$wpdb->query( $sql );

	$sql = "RENAME TABLE {$table_respond_answers_old} TO {$table_respond_answers_new}";
	$wpdb->query( $sql );

	$sql = "RENAME TABLE {$table_participiants_old} TO {$table_participiants_new}";
	$wpdb->query( $sql );

	$sql = "RENAME TABLE {$table_email_notifications_old} TO {$table_email_notifications_new}";
	$wpdb->query( $sql );

	$sql = "UPDATE {$wpdb->prefix}posts SET post_type='torro-forms' WHERE post_type='torro-forms'";
	$wpdb->query( $sql );

	$sql = "UPDATE {$wpdb->prefix}term_taxonomy SET taxonomy='torro-forms-categories' WHERE taxonomy='torro-forms-categories'";
	$wpdb->query( $sql );

	update_option( 'torro_settings_restrictions_selectedmembers_invite_from_name', get_option( 'af_settings_restrictions_selectedmembers_invite_from_name' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_invite_from', get_option( 'af_settings_restrictions_selectedmembers_invite_from' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_invite_subject', get_option( 'af_settings_restrictions_selectedmembers_invite_subject' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_invite_text', get_option( 'af_settings_restrictions_selectedmembers_invite_text' ) );

	update_option( 'torro_settings_restrictions_selectedmembers_reinvite_from_name', get_option( 'af_settings_restrictions_selectedmembers_reinvite_from_name' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_reinvite_from', get_option( 'af_settings_restrictions_selectedmembers_reinvite_from' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_reinvite_subject', get_option( 'af_settings_restrictions_selectedmembers_reinvite_subject' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_reinvite_text', get_option( 'af_settings_restrictions_selectedmembers_reinvite_text' ) );

	delete_option( 'af_settings_restrictions_selectedmembers_invite_from_name' );
	delete_option( 'af_settings_restrictions_selectedmembers_invite_from' );
	delete_option( 'af_settings_restrictions_selectedmembers_invite_subject' );
	delete_option( 'af_settings_restrictions_selectedmembers_invite_text' );
	delete_option( 'af_settings_restrictions_selectedmembers_reinvite_from_name' );
	delete_option( 'af_settings_restrictions_selectedmembers_reinvite_from' );
	delete_option( 'af_settings_restrictions_selectedmembers_reinvite_subject' );
	delete_option( 'af_settings_restrictions_selectedmembers_reinvite_text' );

	update_option( 'torro_db_version', '1.0.2' );
	delete_option( 'af_db_version' );
}
