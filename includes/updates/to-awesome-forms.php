<?php

function torro_questions_to_awesome_forms() {
	global $wpdb;

	$table_elements_old = $wpdb->prefix . 'questions_questions';
	$table_answers_old = $wpdb->prefix . 'questions_answers';
	$table_settings_old = $wpdb->prefix . 'questions_settings';
	$table_responds_old = $wpdb->prefix . 'questions_responds';
	$table_respond_answers_old = $wpdb->prefix . 'questions_respond_answers';
	$table_participiants_old = $wpdb->prefix . 'questions_participiants';
	$table_email_notifications_old = $wpdb->prefix . 'questions_email_notifications';

	$table_tmp_elements_old = $wpdb->prefix . 'tmp_questions_questions';
	$table_tmp_answers_old = $wpdb->prefix . 'tmp_questions_answers';
	$table_tmp_settings_old = $wpdb->prefix . 'tmp_questions_settings';
	$table_tmp_responds_old = $wpdb->prefix . 'tmp_questions_responds';
	$table_tmp_respond_answers_old = $wpdb->prefix . 'tmp_questions_respond_answers';
	$table_tmp_participiants_old = $wpdb->prefix . 'tmp_questions_participiants';
	$table_tmp_email_notifications_old = $wpdb->prefix . 'tmp_questions_email_notifications';

	$sql = "CREATE TABLE {$table_tmp_elements_old} LIKE {$table_elements_old}; INSERT {$table_tmp_elements_old} SELECT * FROM {$table_elements_old};";
	$wpdb->query( $sql );

	$sql = "CREATE TABLE {$table_tmp_answers_old} LIKE {$table_answers_old}; INSERT {$table_tmp_answers_old} SELECT * FROM {$table_answers_old};";
	$wpdb->query( $sql );

	$sql = "CREATE TABLE {$table_tmp_settings_old} LIKE {$table_settings_old}; INSERT {$table_tmp_settings_old} SELECT * FROM {$table_settings_old};";
	$wpdb->query( $sql );

	$sql = "CREATE TABLE {$table_tmp_responds_old} LIKE {$table_responds_old}; INSERT {$table_tmp_responds_old} SELECT * FROM {$table_responds_old};";
	$wpdb->query( $sql );

	$sql = "CREATE TABLE {$table_tmp_respond_answers_old} LIKE {$table_respond_answers_old}; INSERT {$table_tmp_respond_answers_old} SELECT * FROM {$table_respond_answers_old};";
	$wpdb->query( $sql );

	$sql = "CREATE TABLE {$table_tmp_participiants_old} LIKE {$table_participiants_old}; INSERT {$table_tmp_participiants_old} SELECT * FROM {$table_participiants_old};";
	$wpdb->query( $sql );

	$sql = "CREATE TABLE {$table_tmp_email_notifications_old} LIKE {$table_email_notifications_old}; INSERT {$table_tmp_email_notifications_old} SELECT * FROM {$table_email_notifications_old};";
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

	$sql = "ALTER TABLE {$wpdb->torro_elements} CHANGE questions_id form_id int(11)";
	$wpdb->query( $sql );

	$sql = "ALTER TABLE {$wpdb->torro_elements} CHANGE question label text";
	$wpdb->query( $sql );

	$sql = "ALTER TABLE {$wpdb->torro_element_answers} CHANGE question_id element_id int(11)";
	$wpdb->query( $sql );

	$sql = "ALTER TABLE {$wpdb->torro_results} CHANGE questions_id form_id int(11)";
	$wpdb->query( $sql );

	$sql = "ALTER TABLE {$wpdb->torro_result_values} CHANGE respond_id result_id int(11)";
	$wpdb->query( $sql );

	$sql = "ALTER TABLE {$wpdb->torro_result_values} CHANGE question_id element_id int(11)";
	$wpdb->query( $sql );

	$sql = "ALTER TABLE {$wpdb->torro_element_settings} CHANGE question_id element_id int(11)";
	$wpdb->query( $sql );

	$sql = "ALTER TABLE {$wpdb->torro_participants} CHANGE survey_id form_id int(11)";
	$wpdb->query( $sql );

	$sql = "UPDATE $wpdb->posts SET post_type = 'torro-forms' WHERE post_type = 'questions'";
	$wpdb->query( $sql );

	$sql = "UPDATE $wpdb->term_taxonomy SET taxonomy = 'torro-forms-categories' WHERE taxonomy = 'questions-categories'";
	$wpdb->query( $sql );

	$sql = "UPDATE $wpdb->posts SET post_type = 'torro-forms' WHERE post_type = 'questions'";
	$wpdb->query( $sql );

	$sql = "UPDATE $wpdb->torro_elements SET type='textfield' WHERE type='Text'";
	$wpdb->query( $sql );

	$sql = "UPDATE $wpdb->torro_elements SET type='textarea' WHERE type='Textarea'";
	$wpdb->query( $sql );

	$sql = "UPDATE $wpdb->torro_elements SET type='dropdown' WHERE type='Dropdown'";
	$wpdb->query( $sql );

	$sql = "UPDATE $wpdb->torro_elements SET type='onechoice' WHERE type='OneChoice'";
	$wpdb->query( $sql );

	$sql = "UPDATE $wpdb->torro_elements SET type='multiplechoice' WHERE type='MultipleChoice'";
	$wpdb->query( $sql );

	$sql = "UPDATE $wpdb->torro_elements SET type='content' WHERE type='Description'";
	$wpdb->query( $sql );

	$sql = "UPDATE $wpdb->torro_elements SET type='splitter' WHERE type='Splitter'";
	$wpdb->query( $sql );

	$sql = "UPDATE $wpdb->torro_elements SET type='separator' WHERE type='Separator'";
	$wpdb->query( $sql );

	$sql = "UPDATE $wpdb->postmeta SET meta_key = 'restrictions_option' WHERE meta_key = 'participiant_restrictions'";
	$wpdb->query( $sql );

	$sql = "UPDATE $wpdb->postmeta SET meta_value = 'allvisitors' WHERE meta_key = 'restrictions_option' AND meta_value = 'all_visitors'";
	$wpdb->query( $sql );

	$sql = "UPDATE $wpdb->postmeta SET meta_value = 'allmembers' WHERE meta_key = 'restrictions_option' AND meta_value = 'all_members'";
	$wpdb->query( $sql );

	$sql = "UPDATE $wpdb->postmeta SET meta_value = 'allvisitors' WHERE meta_key = 'restrictions_option' AND meta_value = 'no_restrictions'";
	$wpdb->query( $sql );

	$sql = "UPDATE $wpdb->postmeta SET meta_value = 'selectedmembers' WHERE meta_key = 'restrictions_option' AND meta_value = 'selected_members'";
	$wpdb->query( $sql );

	update_option( 'torro_settings_restrictions_selectedmembers_invite_from_name', get_option( 'questions_mail_from_name' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_invite_from', get_option( 'questions_mail_from_email' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_invite_subject', get_option( 'questions_invitation_subject_template' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_invite_text', get_option( 'questions_invitation_text_template' ) );

	update_option( 'torro_settings_restrictions_selectedmembers_reinvite_from_name', get_option( 'questions_mail_from_name' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_reinvite_from', get_option( 'questions_mail_from_email' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_reinvite_subject', get_option( 'questions_reinvitation_subject_template' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_reinvite_text', get_option( 'questions_reinvitation_text_template' ) );
}
