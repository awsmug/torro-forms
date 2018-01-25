<?php

namespace awsmug\Torro_Forms\Tests;

use awsmug\Torro_Forms\Components\Legacy_Upgrades;

class Tests_Legacy_Upgrades extends Unit_Test_Case {

	protected static $legacy;

	protected static $form_id;

	protected static $upgraded = false;

	public static function wpSetUpBeforeClass() {
		global $wpdb;

		self::$form_id = self::factory()->form->create();
		self::$legacy  = new Legacy_Upgrades( 'torrotest_' );

		$charset_collate = $wpdb->get_charset_collate();

		$containers          = self::get_full_table_name( 'containers' );
		$elements            = self::get_full_table_name( 'elements' );
		$element_answers     = self::get_full_table_name( 'element_answers' );
		$element_settings    = self::get_full_table_name( 'element_settings' );
		$results             = self::get_full_table_name( 'results' );
		$result_values       = self::get_full_table_name( 'result_values' );
		$participants        = self::get_full_table_name( 'participants' );
		$email_notifications = self::get_full_table_name( 'email_notifications' );

		$sql = "CREATE TABLE $containers (
	id int(11) unsigned NOT NULL auto_increment,
	form_id bigint(20) unsigned NOT NULL,
	label text NOT NULL,
	sort int(11) NOT NULL,
	PRIMARY KEY (id)
) $charset_collate;
CREATE TABLE $elements (
	id int(11) unsigned NOT NULL auto_increment,
	container_id int(11) unsigned NOT NULL,
	label text NOT NULL,
	sort int(11) NOT NULL,
	type char(50) NOT NULL,
	PRIMARY KEY (id)
) $charset_collate;
CREATE TABLE $element_answers (
	id int(11) unsigned NOT NULL auto_increment,
	element_id int(11) unsigned NOT NULL,
	section char(100) NOT NULL,
	answer text NOT NULL,
	sort int(11) NOT NULL,
	PRIMARY KEY (id)
) $charset_collate;
CREATE TABLE $element_settings (
	id int(11) unsigned NOT NULL auto_increment,
	element_id int(11) unsigned NOT NULL,
	name text NOT NULL,
	value text NOT NULL,
	PRIMARY KEY (id)
) $charset_collate;
CREATE TABLE $results (
	id int(11) unsigned NOT NULL auto_increment,
	form_id bigint(20) unsigned NOT NULL,
	user_id bigint(20) unsigned NOT NULL,
	timestamp int(11) unsigned NOT NULL,
	remote_addr char(15) NOT NULL,
	cookie_key char(50) NOT NULL,
	PRIMARY KEY (id)
) $charset_collate;
CREATE TABLE $result_values (
	id int(11) unsigned NOT NULL auto_increment,
	result_id int(11) unsigned NOT NULL,
	element_id int(11) unsigned NOT NULL,
	value text NOT NULL,
	PRIMARY KEY (id)
) $charset_collate;
CREATE TABLE $participants (
	id int(11) unsigned NOT NULL auto_increment,
	form_id bigint(20) unsigned NOT NULL,
	user_id bigint(20) unsigned NOT NULL,
	PRIMARY KEY (id)
) $charset_collate;
CREATE TABLE $email_notifications (
	id int(11) unsigned NOT NULL auto_increment,
	form_id bigint(20) unsigned NOT NULL,
	notification_name text NOT NULL,
	from_name text NOT NULL,
	from_email text NOT NULL,
	reply_email text NOT NULL,
	to_name text NOT NULL,
	to_email text NOT NULL,
	subject text NOT NULL,
	message text NOT NULL,
	PRIMARY KEY (id)
) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql );
	}

	public static function wpTearDownAfterClass() {
		global $wpdb;

		$containers          = self::get_full_table_name( 'containers' );
		$elements            = self::get_full_table_name( 'elements' );
		$element_answers     = self::get_full_table_name( 'element_answers' );
		$element_settings    = self::get_full_table_name( 'element_settings' );
		$results             = self::get_full_table_name( 'results' );
		$result_values       = self::get_full_table_name( 'result_values' );
		$participants        = self::get_full_table_name( 'participants' );
		$email_notifications = self::get_full_table_name( 'email_notifications' );

		$wpdb->query( "DROP TABLE IF EXISTS `$containers`" );
		$wpdb->query( "DROP TABLE IF EXISTS `$elements`" );
		$wpdb->query( "DROP TABLE IF EXISTS `$element_answers`" );
		$wpdb->query( "DROP TABLE IF EXISTS `$element_settings`" );
		$wpdb->query( "DROP TABLE IF EXISTS `$results`" );
		$wpdb->query( "DROP TABLE IF EXISTS `$result_values`" );
		$wpdb->query( "DROP TABLE IF EXISTS `$participants`" );
		$wpdb->query( "DROP TABLE IF EXISTS `$email_notifications`" );

		$element_choices   = self::get_full_table_name( 'element_choices' );
		$submissions       = self::get_full_table_name( 'submissions' );
		$submission_values = self::get_full_table_name( 'submission_values' );

		$wpdb->query( "DROP TABLE IF EXISTS `$element_choices`" );
		$wpdb->query( "DROP TABLE IF EXISTS `$submissions`" );
		$wpdb->query( "DROP TABLE IF EXISTS `$submission_values`" );

		wp_delete_post( self::$form_id, true );
	}

	/**
	 * @dataProvider data_upgrade_legacy_settings
	 */
	public function test_upgrade_legacy_settings( $setting, $old_setting_prefix, $key, $value, $old_key = null, $old_value = null ) {
		if ( null === $old_key ) {
			$old_key = $key;
		}

		if ( null === $old_value ) {
			$old_value = $value;
		}

		update_option( self::$legacy->get_prefix() . $old_setting_prefix . $old_key, $old_value );

		self::$legacy->upgrade_legacy_settings();

		$new_settings = get_option( self::$legacy->get_prefix() . $setting );

		$this->assertEquals( $value, ( is_array( $new_settings ) && isset( $new_settings[ $key ] ) ) ? $new_settings[ $key ] : null );
	}

	public function data_upgrade_legacy_settings() {
		return array(
			array(
				'general_settings',
				'settings_general_',
				'modules',
				array( 'actions', 'evaluators', 'form_settings', 'access_controls', 'protectors' ),
				'modules',
				array( 'actions', 'results', 'form-settings' ),
			),
			array(
				'general_settings',
				'settings_general_',
				'modules',
				array( 'actions', 'evaluators' ),
				'modules',
				array( 'actions', 'results' ),
			),
			array(
				'general_settings',
				'settings_general_',
				'modules',
				array( 'actions' ),
			),
			array(
				'general_settings',
				'settings_general_',
				'modules',
				array( 'evaluators' ),
				'modules',
				array( 'results' ),
			),
			array(
				'general_settings',
				'settings_general_',
				'modules',
				array( 'form_settings', 'access_controls', 'protectors' ),
				'modules',
				array( 'form-settings' ),
			),
			array(
				'general_settings',
				'settings_general_',
				'modules',
				array(),
			),
			array(
				'general_settings',
				'settings_general_',
				'slug',
				'my-forms',
			),
			array(
				'general_settings',
				'settings_general_',
				'frontend_css',
				true,
				'frontend_css',
				'yes',
			),
			array(
				'general_settings',
				'settings_general_',
				'frontend_css',
				false,
				'frontend_css',
				'no',
			),
			array(
				'general_settings',
				'settings_general_',
				'frontend_css',
				true,
			),
			array(
				'general_settings',
				'settings_general_',
				'frontend_css',
				false,
			),
			array(
				'general_settings',
				'settings_general_',
				'hard_uninstall',
				true,
				'hard_uninstall',
				'yes',
			),
			array(
				'general_settings',
				'settings_general_',
				'hard_uninstall',
				false,
				'hard_uninstall',
				'no',
			),
			array(
				'general_settings',
				'settings_general_',
				'hard_uninstall',
				true,
			),
			array(
				'general_settings',
				'settings_general_',
				'hard_uninstall',
				false,
			),
			array(
				'module_access_controls',
				'settings_visitors_',
				'members__invitation_from_name',
				'My Name',
				'selectedmembers_invite_from_name',
			),
			array(
				'module_access_controls',
				'settings_visitors_',
				'members__invitation_from_email',
				'myemail@example.com',
				'selectedmembers_invite_from',
			),
			array(
				'module_access_controls',
				'settings_visitors_',
				'members__invitation_from_subject',
				'My Subject',
				'selectedmembers_invite_from_subject',
			),
			array(
				'module_access_controls',
				'settings_visitors_',
				'members__invitation_from_message',
				'Hello,' . "\n" . 'this is my message!',
				'selectedmembers_invite_from_text',
			),
			array(
				'module_access_controls',
				'settings_visitors_',
				'members__reinvitation_from_name',
				'My Name',
				'selectedmembers_reinvite_from_name',
			),
			array(
				'module_access_controls',
				'settings_visitors_',
				'members__reinvitation_from_email',
				'myemail@example.com',
				'selectedmembers_reinvite_from',
			),
			array(
				'module_access_controls',
				'settings_visitors_',
				'members__reinvitation_from_subject',
				'My Subject',
				'selectedmembers_reinvite_from_subject',
			),
			array(
				'module_access_controls',
				'settings_visitors_',
				'members__reinvitation_from_message',
				'Hello,' . "\n" . 'this is my message!',
				'selectedmembers_reinvite_from_text',
			),
			array(
				'module_protectors',
				'settings_form_settings_',
				'recaptcha__site_key',
				'ahsudiahsdgauzsgauzsdga',
				'spam_protection_recaptcha_sitekey',
			),
			array(
				'module_protectors',
				'settings_form_settings_',
				'recaptcha__secret_key',
				'hhugzudzasdzasudzagzhuuf',
				'spam_protection_recaptcha_secret',
			),
		);
	}

	/**
	 * @dataProvider data_upgrade_legacy_form_meta
	 */
	public function test_upgrade_legacy_form_meta( $meta, $key, $value, $old_values ) {
		foreach ( $old_values as $old_key => $old_value ) {
			update_post_meta( self::$form_id, $old_key, $old_value );
		}

		self::$legacy->upgrade_legacy_form_meta( self::$form_id );

		$new_meta = get_post_meta( self::$form_id, self::$legacy->get_prefix() . $meta, true );

		$this->assertEquals( $value, ( is_array( $new_meta ) && isset( $new_meta[ $key ] ) ) ? $new_meta[ $key ] : null );
	}

	public function data_upgrade_legacy_form_meta() {
		return array(
			array(
				'module_access_controls',
				'user_identification__prevent_multiple_submissions',
				true,
				array(
					'form_access_controls_allmembers_same_users'      => true,
					'form_access_controls_selectedmembers_same_users' => true,
				),
			),
			array(
				'module_access_controls',
				'user_identification__prevent_multiple_submissions',
				true,
				array(
					'form_access_controls_allmembers_same_users'      => true,
					'form_access_controls_selectedmembers_same_users' => false,
				),
			),
			array(
				'module_access_controls',
				'user_identification__prevent_multiple_submissions',
				false,
				array(
					'form_access_controls_allmembers_same_users'      => false,
					'form_access_controls_selectedmembers_same_users' => true,
				),
			),
			array(
				'module_access_controls',
				'user_identification__prevent_multiple_submissions',
				true,
				array(
					'form_access_controls_allmembers_same_users' => 'yes',
				),
			),
			array(
				'module_access_controls',
				'user_identification__prevent_multiple_submissions',
				false,
				array(
					'form_access_controls_allmembers_same_users' => 'no',
				),
			),
			array(
				'module_access_controls',
				'user_identification__prevent_multiple_submissions',
				true,
				array(
					'form_access_controls_allmembers_same_users' => true,
				),
			),
			array(
				'module_access_controls',
				'user_identification__prevent_multiple_submissions',
				true,
				array(
					'form_access_controls_selectedmembers_same_users' => true,
				),
			),
			array(
				'module_access_controls',
				'user_identification__prevent_multiple_submissions',
				false,
				array(
					'form_access_controls_allmembers_same_users' => false,
				),
			),
			array(
				'module_access_controls',
				'user_identification__prevent_multiple_submissions',
				false,
				array(
					'form_access_controls_selectedmembers_same_users' => false,
				),
			),
			array(
				'module_access_controls',
				'user_identification__prevent_multiple_submissions',
				null,
				array(),
			),
			array(
				'module_access_controls',
				'user_identification__identification_modes',
				array( 'ip_address', 'cookie' ),
				array(
					'form_access_controls_check_ip'     => true,
					'form_access_controls_check_cookie' => true,
				),
			),
			array(
				'module_access_controls',
				'user_identification__identification_modes',
				array( 'ip_address' ),
				array(
					'form_access_controls_check_ip'     => true,
					'form_access_controls_check_cookie' => false,
				),
			),
			array(
				'module_access_controls',
				'user_identification__identification_modes',
				array( 'ip_address' ),
				array(
					'form_access_controls_check_ip' => true,
				),
			),
			array(
				'module_access_controls',
				'user_identification__identification_modes',
				array(),
				array(
					'form_access_controls_check_ip'     => false,
					'form_access_controls_check_cookie' => false,
				),
			),
			array(
				'module_access_controls',
				'user_identification__identification_modes',
				array( 'ip_address', 'cookie' ),
				array(
					'form_access_controls_check_ip'     => 'yes',
					'form_access_controls_check_cookie' => 'yes',
				),
			),
			array(
				'module_access_controls',
				'user_identification__identification_modes',
				array(),
				array(
					'form_access_controls_check_ip'     => 'no',
					'form_access_controls_check_cookie' => 'no',
				),
			),
			array(
				'module_access_controls',
				'user_identification__already_submitted_message',
				'You have already submitted this!',
				array(
					'already_entered_text' => 'You have already submitted this!',
				),
			),
			array(
				'module_access_controls',
				'members__login_required_message',
				'You must be logged in!',
				array(
					'to_be_logged_in_text' => 'You must be logged in!',
				),
			),
			array(
				'module_access_controls',
				'members__enabled',
				true,
				array(
					'to_be_logged_in_text' => 'You must be logged in!',
				),
			),
			array(
				'module_access_controls',
				'members__enabled',
				false,
				array(),
			),
			array(
				'module_access_controls',
				'timerange__start',
				'2020-08-04 22:00:00',
				array(
					'start_date' => '2020-08-04 22:00:00',
				),
			),
			array(
				'module_access_controls',
				'timerange__start',
				'2020-08-04 22:00:00',
				array(
					'start_date' => '1596578400',
				),
			),
			array(
				'module_access_controls',
				'timerange__end',
				'2020-08-04 22:00:00',
				array(
					'end_date' => '2020-08-04 22:00:00',
				),
			),
			array(
				'module_access_controls',
				'timerange__end',
				'2020-08-04 22:00:00',
				array(
					'end_date' => '1596578400',
				),
			),
			array(
				'module_actions',
				'redirection__redirect_type',
				'redirect_url',
				array(
					'redirect_type' => 'redirect_url',
				),
			),
			array(
				'module_form_settings',
				'show_container_title',
				true,
				array(
					'show_page_title' => true,
				),
			),
			array(
				'module_form_settings',
				'show_container_title',
				false,
				array(
					'show_page_title' => false,
				),
			),
			array(
				'module_form_settings',
				'show_container_title',
				true,
				array(
					'show_page_title' => 'yes',
				),
			),
			array(
				'module_form_settings',
				'show_container_title',
				false,
				array(
					'show_page_title' => 'no',
				),
			),
			array(
				'module_form_settings',
				'previous_button_label',
				'Previous',
				array(
					'previous_button_text' => 'Previous',
				),
			),
			array(
				'module_form_settings',
				'next_button_label',
				'Next',
				array(
					'next_button_text' => 'Next',
				),
			),
			array(
				'module_form_settings',
				'submit_button_label',
				'Submit',
				array(
					'send_button_text' => 'Submit',
				),
			),
			array(
				'module_form_settings',
				'success_message',
				'Form was successfully submitted!',
				array(
					'redirect_text_content' => 'Form was successfully submitted!',
				),
			),
			array(
				'module_form_settings',
				'allow_get_params',
				true,
				array(
					'allow_get_param' => true,
				),
			),
			array(
				'module_form_settings',
				'allow_get_params',
				false,
				array(
					'allow_get_param' => false,
				),
			),
			array(
				'module_form_settings',
				'allow_get_params',
				true,
				array(
					'allow_get_param' => 'yes',
				),
			),
			array(
				'module_form_settings',
				'allow_get_params',
				false,
				array(
					'allow_get_param' => 'no',
				),
			),
			array(
				'module_protectors',
				'honeypot__enabled',
				true,
				array(
					'honeypot_enabled' => true,
				),
			),
			array(
				'module_protectors',
				'honeypot__enabled',
				false,
				array(
					'honeypot_enabled' => false,
				),
			),
			array(
				'module_protectors',
				'honeypot__enabled',
				true,
				array(
					'honeypot_enabled' => 'yes',
				),
			),
			array(
				'module_protectors',
				'honeypot__enabled',
				false,
				array(
					'honeypot_enabled' => 'no',
				),
			),
			array(
				'module_protectors',
				'linkcount__enabled',
				true,
				array(
					'linkcount_enabled' => true,
				),
			),
			array(
				'module_protectors',
				'linkcount__enabled',
				false,
				array(
					'linkcount_enabled' => false,
				),
			),
			array(
				'module_protectors',
				'linkcount__enabled',
				true,
				array(
					'linkcount_enabled' => 'yes',
				),
			),
			array(
				'module_protectors',
				'linkcount__enabled',
				false,
				array(
					'linkcount_enabled' => 'no',
				),
			),
			array(
				'module_protectors',
				'recaptcha__enabled',
				true,
				array(
					'recaptcha_enabled' => true,
				),
			),
			array(
				'module_protectors',
				'recaptcha__enabled',
				false,
				array(
					'recaptcha_enabled' => false,
				),
			),
			array(
				'module_protectors',
				'recaptcha__enabled',
				true,
				array(
					'recaptcha_enabled' => 'yes',
				),
			),
			array(
				'module_protectors',
				'recaptcha__enabled',
				false,
				array(
					'recaptcha_enabled' => 'no',
				),
			),
			array(
				'module_protectors',
				'recaptcha__type',
				'image',
				array(
					'recaptcha_type' => 'image',
				),
			),
			array(
				'module_protectors',
				'recaptcha__size',
				'normal',
				array(
					'recaptcha_size' => 'normal',
				),
			),
			array(
				'module_protectors',
				'recaptcha__theme',
				'dark',
				array(
					'recaptcha_theme' => 'dark',
				),
			),
			array(
				'module_protectors',
				'timetrap__enabled',
				true,
				array(
					'timetrap_enabled' => true,
				),
			),
			array(
				'module_protectors',
				'timetrap__enabled',
				false,
				array(
					'timetrap_enabled' => false,
				),
			),
			array(
				'module_protectors',
				'timetrap__enabled',
				true,
				array(
					'timetrap_enabled' => 'yes',
				),
			),
			array(
				'module_protectors',
				'timetrap__enabled',
				false,
				array(
					'timetrap_enabled' => 'no',
				),
			),
		);
	}

	public function test_upgrade_legacy_form_meta_participants() {
		global $wpdb;

		$participants = self::get_full_table_name( 'participants' );

		$user_ids = array( 3, 8, 7, 2 );

		foreach ( $user_ids as $user_id ) {
			$wpdb->insert( $participants, array(
				'form_id' => self::$form_id,
				'user_id' => $user_id,
			), array( '%d', '%d' ) );
		}

		update_option( self::$legacy->get_prefix() . 'legacy_participants_table_installed', 'true' );

		self::$legacy->upgrade_legacy_form_meta( self::$form_id );

		$new_meta = get_post_meta( self::$form_id, self::$legacy->get_prefix() . 'module_access_controls', true );

		$this->assertEquals( $user_ids, ( is_array( $new_meta ) && isset( $new_meta['members__allowed_users'] ) ) ? $new_meta['members__allowed_users'] : null );
	}

	public function test_upgrade_legacy_form_meta_email_notifications() {
		global $wpdb;

		$email_notifications = self::get_full_table_name( 'email_notifications' );

		$notifications = array(
			array(
				'from_name'   => 'John Doe',
				'from_email'  => 'johndoe@example.com',
				'reply_email' => '',
				'to_email'    => 'connect@service.com',
				'subject'     => 'Hello',
				'message'     => 'Hello!' . "\n\n" . 'This is my message for you.',
			),
			array(
				'from_name'   => 'Jane Doe',
				'from_email'  => 'janedoe@example.com',
				'reply_email' => '',
				'to_email'    => 'connect@service.com',
				'subject'     => 'Hello',
				'message'     => 'Hello!' . "\n\n" . 'This is another message for you.',
			),
		);

		foreach ( $notifications as $notification ) {
			$notification = array_merge( array(
				'form_id'           => self::$form_id,
				'notification_name' => 'A Notification',
			), $notification );

			$wpdb->insert( $email_notifications, $notification, array_fill( 0, count( $notification ), '%s' ) );
		}

		update_option( self::$legacy->get_prefix() . 'legacy_email_notifications_table_installed', 'true' );

		self::$legacy->upgrade_legacy_form_meta( self::$form_id );

		$new_meta = get_post_meta( self::$form_id, self::$legacy->get_prefix() . 'module_actions', true );

		$notifications = array_map( function( $notification ) {
			$notification['message'] = wpautop( $notification['message'] );

			return $notification;
		}, $notifications );

		$this->assertEquals( $notifications, ( is_array( $new_meta ) && isset( $new_meta['email_notifications__notifications'] ) ) ? $new_meta['email_notifications__notifications'] : null );
	}

	public function test_maybe_upgrade_legacy_form_meta_yes() {
		update_post_meta( self::$form_id, self::$legacy->get_prefix() . 'legacy_needs_migration', 'true' );

		$this->assertTrue( self::$legacy->maybe_upgrade_legacy_form_meta( self::$form_id ) );
		$this->assertEmpty( get_post_meta( self::$form_id, self::$legacy->get_prefix() . 'legacy_needs_migration', true ) );
	}

	public function test_maybe_upgrade_legacy_form_meta_no() {
		$this->assertFalse( self::$legacy->maybe_upgrade_legacy_form_meta( self::$form_id ) );
	}

	public function test_maybe_upgrade_legacy_form_attachment_statuses() {
		global $wpdb;

		// The method processes 50 attachments maximum at a time.
		$limit = 50;

		$taxonomy = torro()->taxonomies()->get_attachment_taxonomy_slug();
		$term_id  = self::factory()->term->create( array(
			'taxonomy' => $taxonomy,
		) );

		add_filter( 'wp_insert_attachment_data', array( $this, 'filter_attachment_data_for_category' ) );
		$attachment_ids = self::factory()->attachment->create_many( $limit + 1 );
		remove_filter( 'wp_insert_attachment_data', array( $this, 'filter_attachment_data_for_category' ) );

		update_option( self::$legacy->get_prefix() . 'legacy_attachments_need_migration', 'true' );
		update_option( self::$legacy->get_prefix() . 'general_settings', array(
			'attachment_taxonomy_term_id' => $term_id,
		) );

		// Process all attachments but the last one (see above why).
		$this->assertTrue( self::$legacy->maybe_upgrade_legacy_form_attachment_statuses() );

		// Process the one remaining attachment.
		$this->assertTrue( self::$legacy->maybe_upgrade_legacy_form_attachment_statuses() );

		// No more attachments to process.
		$this->assertFalse( self::$legacy->maybe_upgrade_legacy_form_attachment_statuses() );

		$results = $wpdb->get_col( $wpdb->prepare( "SELECT object_id FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term_id ) );
		$this->assertEqualSets( $attachment_ids, $results );
	}

	public function filter_attachment_data_for_category( $data ) {
		$data['post_status'] = 'torro-forms-upload';

		return $data;
	}

	/**
	 * @dataProvider data_upgrade_to_1_0_10_database
	 */
	public function test_upgrade_to_1_0_10_database( $table, $field_definitions ) {
		global $wpdb;

		self::maybe_upgrade_database();

		$table_name = self::get_full_table_name( $table );

		$results = $wpdb->get_results( "DESCRIBE $table_name" );

		$expected_data = array();
		$actual_data   = array();

		foreach ( $field_definitions as $field_slug => $field_definition ) {
			foreach ( $field_definition as $key => $value ) {
				$expected_data[ $field_slug . ':' . $key ] = $value;
			}
		}

		foreach ( $results as $column ) {
			$field_slug = $column->Field;

			if ( ! isset( $field_definitions[ $field_slug ] ) ) {
				continue;
			}

			foreach ( (array) $column as $key => $value ) {
				if ( ! isset( $field_definitions[ $field_slug ][ $key ] ) ) {
					continue;
				}

				$actual_data[ $field_slug . ':' . $key ] = $value;
			}
		}

		$this->assertEqualSetsWithIndex( $expected_data, $actual_data );
	}

	public function data_upgrade_to_1_0_10_database() {
		return array(
			array(
				'containers',
				array(
					'form_id' => array(
						'Key' => 'MUL',
					),
					'sort'    => array(
						'Type'    => 'int(11) unsigned',
						'Default' => '0',
					),
				),
			),
			array(
				'elements',
				array(
					'container_id' => array(
						'Key' => 'MUL',
					),
					'type'         => array(
						'Key' => 'MUL',
					),
					'sort'         => array(
						'Type'    => 'int(11) unsigned',
						'Default' => '0',
					),
				),
			),
			array(
				'element_choices',
				array(
					'element_id' => array(
						'Key' => 'MUL',
					),
					'field'      => array(
						'Type'    => 'char(100)',
						'Default' => '',
					),
					'value'      => array(
						'Type' => 'text',
					),
					'sort'       => array(
						'Type'    => 'int(11) unsigned',
						'Default' => '0',
					),
				),
			),
			array(
				'element_settings',
				array(
					'element_id' => array(
						'Key' => 'MUL',
					),
				),
			),
			array(
				'submissions',
				array(
					'form_id'     => array(
						'Key' => 'MUL',
					),
					'user_id'     => array(
						'Key' => 'MUL',
					),
					'remote_addr' => array(
						'Type' => 'char(50)',
					),
					'user_key'    => array(
						'Type' => 'char(50)',
					),
					'status'      => array(
						'Key'     => 'MUL',
						'Type'    => 'char(50)',
						'Default' => 'completed',
					),
				),
			),
			array(
				'submission_values',
				array(
					'submission_id' => array(
						'Key'  => 'MUL',
						'Type' => 'int(11) unsigned',
					),
					'element_id'    => array(
						'Key' => 'MUL',
					),
					'field'         => array(
						'Type'    => 'char(100)',
						'Default' => '',
					),
				),
			),
		);
	}

	protected static function maybe_upgrade_database() {
		if ( self::$upgraded ) {
			return;
		}

		self::$legacy->upgrade( '1.0.9' );
		self::$upgraded = true;
	}

	protected static function get_full_table_name( $table_name ) {
		global $wpdb;

		return $wpdb->prefix . self::$legacy->get_prefix() . $table_name;
	}
}
