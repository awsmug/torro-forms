<?php
/**
 * Legacy upgrades class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms;

use Leaves_And_Love\Plugin_Lib\Service;

/**
 * Class for upgrading from legacy versions.
 *
 * @since 1.0.0
 */
class Legacy_Upgrades extends Service {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $prefix Instance prefix.
	 */
	public function __construct( $prefix ) {
		$this->set_prefix( $prefix );
	}

	/**
	 * Runs the upgrade from a legacy version.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $legacy_db_version The legacy version number.
	 */
	public function upgrade( $legacy_db_version ) {
		$last_legacy_db_version = '1.0.10';

		if ( $legacy_db_version === $last_legacy_db_version ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		if ( version_compare( $legacy_db_version, '1.0.3', '<' ) ) {
			$this->upgrade_to_1_0_3();
			$this->update_db_version( '1.0.3' );
		}

		if ( version_compare( $legacy_db_version, '1.0.4', '<' ) ) {
			$this->upgrade_to_1_0_4();
			$this->update_db_version( '1.0.4' );
		}

		if ( version_compare( $legacy_db_version, '1.0.5', '<' ) ) {
			$this->upgrade_to_1_0_5();
			$this->update_db_version( '1.0.5' );
		}

		if ( version_compare( $legacy_db_version, '1.0.6', '<' ) ) {
			$this->upgrade_to_1_0_6();
			$this->update_db_version( '1.0.6' );
		}

		if ( version_compare( $legacy_db_version, '1.0.7', '<' ) ) {
			$this->upgrade_to_1_0_7();
			$this->update_db_version( '1.0.7' );
		}

		if ( version_compare( $legacy_db_version, '1.0.8', '<' ) ) {
			$this->upgrade_to_1_0_8();
			$this->update_db_version( '1.0.8' );
		}

		if ( version_compare( $legacy_db_version, '1.0.9', '<' ) ) {
			$this->upgrade_to_1_0_9();
			$this->update_db_version( '1.0.9' );
		}

		if ( version_compare( $legacy_db_version, '1.0.10', '<' ) ) {
			$this->upgrade_to_1_0_10();
			$this->update_db_version( '1.0.10' );
		}
	}

	/**
	 * Upgrades to legacy version 1.0.3.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	protected function upgrade_to_1_0_3() {
		global $wpdb;

		$elements = $this->get_full_table_name( 'elements' );

		$wpdb->query( "UPDATE $elements SET type='textfield' WHERE type='Text'" );
	}

	/**
	 * Upgrades to legacy version 1.0.4.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	protected function upgrade_to_1_0_4() {
		global $wpdb;

		$containers = $this->get_full_table_name( 'containers' );
		$elements = $this->get_full_table_name( 'elements' );
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $containers (
			id int(11) NOT NULL AUTO_INCREMENT,
			form_id int(11) NOT NULL,
			label text NOT NULL,
			sort int(11) NOT NULL,
			UNIQUE KEY id (id)
			) ENGINE = INNODB " . $charset_collate . ";";

		dbDelta( $sql );

		$wpdb->query( "ALTER TABLE $elements ADD container_id INT(11) NOT NULL AFTER form_id" );
	}

	/**
	 * Upgrades to legacy version 1.0.5.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	protected function upgrade_to_1_0_5() {
		global $wpdb;

		$element_settings = $this->get_full_table_name( 'element_settings' );
		$settings = $this->get_full_table_name( 'settings' );

		$wpdb->query( "ALTER TABLE $settings RENAME TO $element_settings" );
	}

	/**
	 * Upgrades to legacy version 1.0.6.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	protected function upgrade_to_1_0_6() {
		global $wpdb;

		$wpdb->update( $wpdb->posts, array(
			'post_type'	=> $this->get_prefix() . 'form',
		), array(
			'post_type'	=> 'torro-forms',
		), array( '%s' ), array( '%s' ) );

		$wpdb->update( $wpdb->term_taxonomy, array(
			'taxonomy'	=> $this->get_prefix() . 'form_category',
		), array(
			'taxonomy'	=> 'torro-forms-categories',
		), array( '%s' ), array( '%s' ) );
	}

	/**
	 * Upgrades to legacy version 1.0.7.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	protected function upgrade_to_1_0_7() {
		global $wpdb;

		$elements = $this->get_full_table_name( 'elements' );

		$wpdb->query( "ALTER TABLE $elements DROP form_id" );
	}

	/**
	 * Upgrades to legacy version 1.0.8.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	protected function upgrade_to_1_0_8() {
		global $wpdb;

		$email_notifications = $this->get_full_table_name( 'email_notifications' );

		$wpdb->query( "ALTER TABLE $email_notifications ADD reply_email TEXT NOT NULL AFTER from_email" );
	}

	/**
	 * Upgrades to legacy version 1.0.9.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	protected function upgrade_to_1_0_9() {
		global $wpdb;

		$elements = $this->get_full_table_name( 'elements' );

		$wpdb->update(
			$elements,
			array(
				'label' => '<hr />',
				'type'  => 'content'
			),
			array(
				'type'  => 'separator'
			)
		);
	}

	/**
	 * Upgrades to legacy version 1.0.10.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	protected function upgrade_to_1_0_10() {
		global $wpdb;

		$containers = $this->get_full_table_name( 'containers' );
		$elements = $this->get_full_table_name( 'elements' );
		$element_answers = $this->get_full_table_name( 'element_answers' );
		$element_choices = $this->get_full_table_name( 'element_choices' );
		$element_settings = $this->get_full_table_name( 'element_settings' );
		$results = $this->get_full_table_name( 'results' );
		$submissions = $this->get_full_table_name( 'submissions' );
		$result_values = $this->get_full_table_name( 'result_values' );
		$submission_values = $this->get_full_table_name( 'submission_values' );
		$participants = $this->get_full_table_name( 'participants' );

		$wpdb->query( "ALTER TABLE $containers CHANGE sort sort int(11) unsigned NOT NULL default '0'" );
		$wpdb->query( "ALTER TABLE $containers ADD KEY form_id (form_id)" );
		$wpdb->query( "ALTER TABLE $elements CHANGE sort sort int(11) unsigned NOT NULL default '0'" );
		$wpdb->query( "ALTER TABLE $elements ADD KEY container_id (container_id)" );
		$wpdb->query( "ALTER TABLE $elements ADD KEY type (type)" );
		$wpdb->query( "ALTER TABLE $elements ADD KEY type_container_id (type,container_id)" );
		$wpdb->query( "ALTER TABLE $element_answers RENAME TO $element_choices" );
		$wpdb->query( "ALTER TABLE $element_choices CHANGE section field char(100) NOT NULL default ''" );
		$wpdb->query( "ALTER TABLE $element_choices CHANGE sort sort int(11) unsigned NOT NULL default '0'" );
		$wpdb->query( "ALTER TABLE $element_choices ADD KEY element_id (element_id)" );
		$wpdb->query( "ALTER TABLE $element_settings ADD KEY element_id (element_id)" );
		$wpdb->query( "ALTER TABLE $results RENAME TO $submissions" );
		$wpdb->query( "ALTER TABLE $submissions ADD status char(50) NOT NULL default 'completed' AFTER cookie_key" );
		$wpdb->query( "ALTER TABLE $submissions ADD KEY form_id (form_id)" );
		$wpdb->query( "ALTER TABLE $submissions ADD KEY user_id (user_id)" );
		$wpdb->query( "ALTER TABLE $submissions ADD KEY status (status)" );
		$wpdb->query( "ALTER TABLE $submissions ADD KEY status_form_id (status,form_id)" );
		$wpdb->query( "ALTER TABLE $result_values RENAME TO $submission_values" );
		$wpdb->query( "ALTER TABLE $submission_values CHANGE result_id submission_id int(11) unsigned NOT NULL" );
		$wpdb->query( "ALTER TABLE $submission_values ADD name text NOT NULL default '' AFTER element_id" );
		$wpdb->query( "ALTER TABLE $submission_values ADD KEY submission_id (submission_id)" );
		$wpdb->query( "ALTER TABLE $submission_values ADD KEY element_id (element_id)" );
		$wpdb->query( "ALTER TABLE $participants ADD KEY form_id (form_id)" );
		$wpdb->query( "ALTER TABLE $participants ADD KEY user_id (user_id)" );
	}

	/**
	 * Updates the legacy version number.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $legacy_db_version The legacy version number to set.
	 */
	protected function update_db_version( $legacy_db_version ) {
		update_option( $this->get_prefix() . 'db_version', $legacy_db_version );
	}

	/**
	 * Creates a full database table name for an unprefixed table name.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $table_name Unprefixed table name.
	 * @return string Full table name.
	 */
	protected function get_full_table_name( $table_name ) {
		global $wpdb;

		return $wpdb->prefix . $this->get_prefix() . $table_name;
	}
}
