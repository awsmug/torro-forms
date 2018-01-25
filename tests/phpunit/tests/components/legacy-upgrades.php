<?php

namespace awsmug\Torro_Forms\Tests;

use awsmug\Torro_Forms\Components\Legacy_Upgrades;

class Tests_Legacy_Upgrades extends Unit_Test_Case {

	protected static $legacy;

	protected static $upgraded = false;

	public static function wpSetUpBeforeClass() {
		global $wpdb;

		self::$legacy = new Legacy_Upgrades( 'torrotest_' );

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

		$this->assertEquals( $value, isset( $new_settings[ $key ] ) ? $new_settings[ $key ] : null );
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
