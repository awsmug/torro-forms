<?php

function torro_import_from_awesome_forms() {
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	global $wpdb;

	$table_elements_old = $wpdb->prefix . 'af_elements';
	$table_answers_old = $wpdb->prefix . 'af_element_answers';
	$table_settings_old = $wpdb->prefix . 'af_settings';
	$table_responds_old = $wpdb->prefix . 'af_results';
	$table_respond_answers_old= $wpdb->prefix . 'af_result_values';
	$table_participiants_old= $wpdb->prefix . 'af_participiants';
	$table_email_notifications_old = $wpdb->prefix . 'af_email_notifications';

	$table_tmp_elements_old = $wpdb->prefix . 'tmp_af_elements';
	$table_tmp_answers_old = $wpdb->prefix . 'tmp_af_element_answers';
	$table_tmp_settings_old = $wpdb->prefix . 'tmp_af_settings';
	$table_tmp_responds_old = $wpdb->prefix . 'tmp_af_results';
	$table_tmp_respond_answers_old= $wpdb->prefix . 'tmp_af_result_values';
	$table_tmp_participiants_old= $wpdb->prefix . 'tmp_af_participiants';
	$table_tmp_email_notifications_old = $wpdb->prefix . 'tmp_af_email_notifications';

	$sql = "CREATE TABLE IF NOT EXISTS {$table_tmp_elements_old} LIKE {$table_elements_old};";
	dbDelta( $sql );

	$sql = "INSERT {$table_tmp_elements_old} SELECT * FROM {$table_elements_old};";
	$wpdb->query( $sql );

	$sql = "CREATE TABLE IF NOT EXISTS {$table_tmp_answers_old} LIKE {$table_answers_old};";
	dbDelta( $sql );

	$sql = "INSERT {$table_tmp_answers_old} SELECT * FROM {$table_answers_old};";
	$wpdb->query( $sql );

	$sql = "CREATE TABLE IF NOT EXISTS {$table_tmp_settings_old} LIKE {$table_settings_old};";
	dbDelta( $sql );

	$sql = "INSERT {$table_tmp_settings_old} SELECT * FROM {$table_settings_old};";
	$wpdb->query( $sql );

	$sql .= "CREATE TABLE IF NOT EXISTS {$table_tmp_responds_old} LIKE {$table_responds_old};";
	dbDelta( $sql );

	$sql = "INSERT {$table_tmp_responds_old} SELECT * FROM {$table_responds_old};";
	$wpdb->query( $sql );

	$sql = "CREATE TABLE IF NOT EXISTS {$table_tmp_respond_answers_old} LIKE {$table_respond_answers_old};";
	dbDelta( $sql );

	$sql = "INSERT {$table_tmp_respond_answers_old} SELECT * FROM {$table_respond_answers_old};";
	$wpdb->query( $sql );

	$sql = "CREATE TABLE IF NOT EXISTS {$table_tmp_participiants_old} LIKE {$table_participiants_old};";
	dbDelta( $sql );

	$sql = "INSERT {$table_tmp_participiants_old} SELECT * FROM {$table_participiants_old};";
	$wpdb->query( $sql );

	$sql = "CREATE TABLE IF NOT EXISTS {$table_tmp_email_notifications_old} LIKE {$table_email_notifications_old};";
	dbDelta( $sql );

	$sql = "INSERT {$table_tmp_email_notifications_old} SELECT * FROM {$table_email_notifications_old};";
	$wpdb->query( $sql );

	$sql = "RENAME TABLE {$table_tmp_elements_old} TO {$wpdb->torro_elements}";
	$wpdb->query( $sql );

	$sql = "RENAME TABLE {$table_tmp_answers_old} TO {$wpdb->torro_element_answers}";
	$wpdb->query( $sql );

	$sql = "RENAME TABLE {$table_tmp_settings_old} TO {$wpdb->torro_element_settings}";
	$wpdb->query( $sql );

	$sql = "RENAME TABLE {$table_tmp_responds_old} TO {$wpdb->torro_results}";
	$wpdb->query( $sql );

	$sql = "RENAME TABLE {$table_tmp_respond_answers_old} TO {$wpdb->torro_result_values}";
	$wpdb->query( $sql );

	$sql = "RENAME TABLE {$table_tmp_participiants_old} TO {$wpdb->torro_participants}";
	$wpdb->query( $sql );

	$sql = "RENAME TABLE {$table_tmp_email_notifications_old} TO {$wpdb->torro_email_notifications}";
	$wpdb->query( $sql );

	$sql = "UPDATE $wpdb->posts SET post_type = 'torro-forms' WHERE post_type = 'awesome-forms'";
	$wpdb->query( $sql );

	$sql = "UPDATE $wpdb->term_taxonomy SET taxonomy = 'torro-forms-categories' WHERE taxonomy = 'awesome-forms-categories'";
	$wpdb->query( $sql );

	update_option( 'torro_settings_restrictions_selectedmembers_invite_from_name', get_option( 'af_settings_restrictions_selectedmembers_invite_from_name' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_invite_from', get_option( 'af_settings_restrictions_selectedmembers_invite_from' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_invite_subject', get_option( 'af_settings_restrictions_selectedmembers_invite_subject' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_invite_text', get_option( 'af_settings_restrictions_selectedmembers_invite_text' ) );

	update_option( 'torro_settings_restrictions_selectedmembers_reinvite_from_name', get_option( 'af_settings_restrictions_selectedmembers_reinvite_from_name' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_reinvite_from', get_option( 'af_settings_restrictions_selectedmembers_reinvite_from' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_reinvite_subject', get_option( 'af_settings_restrictions_selectedmembers_reinvite_subject' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_reinvite_text', get_option( 'af_settings_restrictions_selectedmembers_reinvite_text' ) );
}
