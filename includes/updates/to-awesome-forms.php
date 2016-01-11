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

	$sql = "ALTER TABLE {$table_elements_new} CHANGE questions_id form_id int(11)";
	$wpdb->query( $sql );

	$sql = "ALTER TABLE {$table_elements_new} CHANGE question label text";
	$wpdb->query( $sql );

	$sql = "ALTER TABLE {$table_answers_new} CHANGE question_id element_id int(11)";
	$wpdb->query( $sql );

	$sql = "ALTER TABLE {$table_responds_new} CHANGE questions_id form_id int(11)";
	$wpdb->query( $sql );

	$sql = "ALTER TABLE {$table_respond_answers_new} CHANGE respond_id result_id int(11)";
	$wpdb->query( $sql );

	$sql = "ALTER TABLE {$table_respond_answers_new} CHANGE question_id element_id int(11)";
	$wpdb->query( $sql );

	$sql = "ALTER TABLE {$table_settings_new} CHANGE question_id element_id int(11)";
	$wpdb->query( $sql );

	$sql = "ALTER TABLE {$table_participiants_new} CHANGE survey_id form_id int(11)";
	$wpdb->query( $sql );

	$sql = "UPDATE {$wpdb->prefix}posts SET post_type='torro-forms' WHERE post_type='questions'";
	$wpdb->query( $sql );

	$sql = "UPDATE {$wpdb->prefix}term_taxonomy SET taxonomy='torro-forms-categories' WHERE taxonomy='questions-categories'";
	$wpdb->query( $sql );

	$sql = "UPDATE {$wpdb->prefix}posts SET post_type='torro-forms' WHERE post_type='questions'";
	$wpdb->query( $sql );

	$sql = "UPDATE {$table_elements_new} SET type='textfield' WHERE type='Text'";
	$wpdb->query( $sql );

	$sql = "UPDATE {$table_elements_new} SET type='textarea' WHERE type='Textarea'";
	$wpdb->query( $sql );

	$sql = "UPDATE {$table_elements_new} SET type='dropdown' WHERE type='Dropdown'";
	$wpdb->query( $sql );

	$sql = "UPDATE {$table_elements_new} SET type='onechoice' WHERE type='OneChoice'";
	$wpdb->query( $sql );

	$sql = "UPDATE {$table_elements_new} SET type='multiplechoice' WHERE type='MultipleChoice'";
	$wpdb->query( $sql );

	$sql = "UPDATE {$table_elements_new} SET type='content' WHERE type='Description'";
	$wpdb->query( $sql );

	$sql = "UPDATE {$table_elements_new} SET type='splitter' WHERE type='Splitter'";
	$wpdb->query( $sql );

	$sql = "UPDATE {$table_elements_new} SET type='separator' WHERE type='Separator'";
	$wpdb->query( $sql );

	$sql = "UPDATE {$wpdb->prefix}postmeta SET meta_key='restrictions_option' WHERE meta_key='participiant_restrictions'";
	$wpdb->query( $sql );

	$sql = "UPDATE {$wpdb->prefix}postmeta SET meta_value='allvisitors' WHERE meta_key='restrictions_option' AND meta_value='all_visitors'";
	$wpdb->query( $sql );

	$sql = "UPDATE {$wpdb->prefix}postmeta SET meta_value='allmembers' WHERE meta_key='restrictions_option' AND meta_value='all_members'";
	$wpdb->query( $sql );

	$sql = "UPDATE {$wpdb->prefix}postmeta SET meta_value='allvisitors' WHERE meta_key='restrictions_option' AND meta_value='no_restrictions'";
	$wpdb->query( $sql );

	$sql = "UPDATE {$wpdb->prefix}postmeta SET meta_value='selectedmembers' WHERE meta_key='restrictions_option' AND meta_value='selected_members'";
	$wpdb->query( $sql );

	update_option( 'torro_settings_restrictions_selectedmembers_invite_from_name', get_option( 'questions_mail_from_name' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_invite_from', get_option( 'questions_mail_from_email' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_invite_subject', get_option( 'questions_invitation_subject_template' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_invite_text', get_option( 'questions_invitation_text_template' ) );

	update_option( 'torro_settings_restrictions_selectedmembers_reinvite_from_name', get_option( 'questions_mail_from_name' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_reinvite_from', get_option( 'questions_mail_from_email' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_reinvite_subject', get_option( 'questions_reinvitation_subject_template' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_reinvite_text', get_option( 'questions_reinvitation_text_template' ) );

	delete_option( 'questions_mail_from_name' );
	delete_option( 'questions_mail_from_email' );
	delete_option( 'questions_invitation_subject_template' );
	delete_option( 'questions_invitation_text_template' );
	delete_option( 'questions_reinvitation_subject_template' );
	delete_option( 'questions_reinvitation_text_template' );

	update_option( 'torro_db_version', '1.0.1' );
}
