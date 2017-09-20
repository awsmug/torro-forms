<?php
/**
 * Legacy upgrades class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Components;

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
	 * Upgrades legacy form metadata to new schema if necessary.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $form_id ID of the form for which to migrate data.
	 * @return bool True if form metadata was migrated, false if it had already been
	 *              migrated before.
	 */
	public function maybe_upgrade_legacy_form_meta( $form_id ) {
		if ( get_post_meta( $form_id, $this->get_prefix() . 'legacy_needs_migration', true ) !== 'true' ) {
			return false;
		}

		$mappings = array(
			'access_controls' => array(
				'author_identification' => array(
					'use_ip_check'                 => array( 'form_access_controls_check_ip', 'bool' ),
					'use_cookie_check'             => array( 'form_access_controls_check_cookie', 'bool' ),
					'prevent_multiple_submissions' => array( array( 'form_access_controls_allmembers_same_users', 'form_access_controls_selectedmembers_same_users' ), 'bool' ),
					'already_submitted_message'    => array( 'already_entered_text', 'string' ),
				),
				'members'               => array(
					'login_required_message' => array( 'to_be_logged_in_text', 'string' ),
					'allowed_users'          => 'PARTICIPANTS',
				),
				'timerange'             => array(
					'start' => array( 'start_date', 'datetime' ),
					'end'   => array( 'end_date', 'datetime' ),
				),
			),
			'actions'         => array(
				'email_notifications' => array(
					'notifications' => 'EMAIL_NOTIFICATIONS',
				),
				'redirection'         => array(
					'redirect_type' => array( 'redirect_type', 'string' ),
					'redirect_page' => array( 'redirect_page', 'string' ),
					'redirect_url'  => array( 'redirect_url', 'string' ),
				),
			),
			'evaluators'      => array(),
			'form_settings'   => array(
				'' => array(
					'show_container_title'  => array( 'show_page_title', 'bool' ),
					'previous_button_label' => array( 'previous_button_text', 'string' ),
					'next_button_label'     => array( 'next_button_text', 'string' ),
					'submit_button_label'   => array( 'send_button_text', 'string' ),
					'success_message'       => array( 'redirect_text_content', 'string' ),
					'allow_get_params'      => array( 'allow_get_param', 'bool' ),
				),
			),
			'protectors'      => array(
				'honeypot'  => array(
					'enabled' => array( 'honeypot_enabled', 'bool' ),
				),
				'linkcount' => array(
					'enabled' => array( 'linkcount_enabled', 'bool' ),
				),
				'recaptcha' => array(
					'enabled' => array( 'recaptcha_enabled', 'bool' ),
					'type'    => array( 'recaptcha_type', 'string' ),
					'size'    => array( 'recaptcha_size', 'string' ),
					'theme'   => array( 'recaptcha_theme', 'string' ),
				),
				'timetrap'  => array(
					'enabled' => array( 'timetrap_enabled', 'bool' ),
				),
			),
		);

		$metadata = array();
		foreach ( $mappings as $module => $module_mappings ) {
			$metadata[ $module ] = get_post_meta( $form_id, 'torro_module_' . $module, true );
			if ( ! is_array( $metadata[ $module ] ) ) {
				$metadata[ $module ] = array();
			}

			foreach ( $module_mappings as $submodule => $submodule_mappings ) {
				$submodule_data_found = false;

				$form_option_prefix = ! empty( $submodule ) ? $submodule . '__' : '';

				foreach ( $submodule_mappings as $form_option => $mapping_data ) {
					if ( 'PARTICIPANTS' === $mapping_data ) {
						if ( get_option( $this->get_prefix() . 'legacy_participants_table_installed' ) === 'true' ) {
							// TODO: Migrate participants over into new form option and set $submodule_data_found accordingly.
						}
						continue;
					}

					if ( 'EMAIL_NOTIFICATIONS' === $mapping_data ) {
						if ( get_option( $this->get_prefix() . 'legacy_email_notifications_table_installed' ) === 'true' ) {
							// TODO: Migrate email notifications over into new form option and set $submodule_data_found accordingly.
						}
						continue;
					}

					$old = array();
					if ( is_array( $mapping_data[0] ) ) {
						foreach ( $mapping_data[0] as $old_form_option ) {
							$old = get_post_meta( $form_id, $old_form_option );
							if ( ! empty( $old ) ) {
								break;
							}
						}
					} else {
						$old = get_post_meta( $form_id, $mapping_data[0] );
					}

					if ( empty( $old ) ) {
						continue;
					}

					$submodule_data_found = true;

					// New values already set take precedence.
					if ( isset( $metadata[ $module ][ $form_option_prefix . $form_option ] ) ) {
						continue;
					}

					$old = $old[0];

					switch ( $mapping_data[1] ) {
						case 'bool':
							$new = ! empty( $old ) && 'no' !== strtolower( $old ) ? true : false;
							break;
						case 'datetime':
							if ( ! is_numeric( $old ) ) {
								$old = strtotime( $old );
							}
							$new = date( 'Y-m-d H:i:s', $old );
							break;
						case 'string':
						default:
							$new = $old;
					}

					$metadata[ $module ][ $form_option_prefix . $form_option ] = $new;
				}

				if ( ! empty( $form_option_prefix ) && 'protectors' !== $module && $submodule_data_found ) {
					// This is ugly, but who cares here...?
					if ( isset( $metadata[ $module ][ $form_option_prefix . 'redirect_type' ] ) && 'none' === $metadata[ $module ][ $form_option_prefix . 'redirect_type' ] ) {
						continue;
					}

					$metadata[ $module ][ $form_option_prefix . 'enabled' ] = true;
				}
			}
		}

		foreach ( $metadata as $module => $module_metadata ) {
			if ( empty( $module_metadata ) ) {
				continue;
			}

			update_post_meta( $form_id, 'torro_module_' . $module, $module_metadata );
		}

		delete_post_meta( $form_id, $this->get_prefix() . 'legacy_needs_migration' );

		return true;
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
	 * @global \wpdb $wpdb WordPress database abstraction object.
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
	 * @global \wpdb $wpdb WordPress database abstraction object.
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
	 * @global \wpdb $wpdb WordPress database abstraction object.
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
	 * @global \wpdb $wpdb WordPress database abstraction object.
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
	 * @global \wpdb $wpdb WordPress database abstraction object.
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
	 * @global \wpdb $wpdb WordPress database abstraction object.
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
	 * @global \wpdb $wpdb WordPress database abstraction object.
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
	 * @global \wpdb $wpdb WordPress database abstraction object.
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
		$email_notifications = $this->get_full_table_name( 'email_notifications' );

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
		$wpdb->query( "ALTER TABLE $submissions CHANGE remote_addr remote_addr char(50) NOT NULL" );
		$wpdb->query( "ALTER TABLE $submissions CHANGE cookie_key user_key char(50) NOT NULL" );
		$wpdb->query( "ALTER TABLE $submissions ADD status char(50) NOT NULL default 'completed' AFTER user_key" );
		$wpdb->query( "ALTER TABLE $submissions ADD KEY form_id (form_id)" );
		$wpdb->query( "ALTER TABLE $submissions ADD KEY user_id (user_id)" );
		$wpdb->query( "ALTER TABLE $submissions ADD KEY status (status)" );
		$wpdb->query( "ALTER TABLE $submissions ADD KEY status_form_id (status,form_id)" );
		$wpdb->query( "ALTER TABLE $result_values RENAME TO $submission_values" );
		$wpdb->query( "ALTER TABLE $submission_values CHANGE result_id submission_id int(11) unsigned NOT NULL" );
		$wpdb->query( "ALTER TABLE $submission_values ADD field char(100) NOT NULL default '' AFTER element_id" );
		$wpdb->query( "ALTER TABLE $submission_values ADD KEY submission_id (submission_id)" );
		$wpdb->query( "ALTER TABLE $submission_values ADD KEY element_id (element_id)" );

		$general_settings = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name LIKE %s", $wpdb->esc_like( $this->get_prefix() . 'settings_general_' ) . '%' ) );
		if ( ! empty( $general_settings ) ) {
			$general_offset = strlen( $this->get_prefix() . 'settings_general_' );

			$general_settings_array = array();
			foreach ( $general_settings as $general_setting ) {
				$name = substr( $general_setting->option_name, $general_offset );
				$value = $general_setting->option_value;

				if ( 'modules' === $name && is_array( $value ) ) {
					if ( false !== ( $key = array_search( 'form-settings', $value, true ) ) ) {
						$value[ $key ] = 'form_settings';
					}

					if ( false !== ( $key = array_search( 'results', $value, true ) ) ) {
						$value[ $key ] = 'submission_handlers';
					}
				}

				$general_settings_array[ $name ] = $value;

				delete_option( $general_setting->option_name );
			}

			update_option( $this->get_prefix() . 'general_settings', $general_settings_array );
		}

		$extension_settings = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name LIKE %s", $wpdb->esc_like( $this->get_prefix() . 'settings_extensions_' ) . '%' ) );
		if ( ! empty( $extension_settings ) ) {
			$extension_offset = strlen( $this->get_prefix() . 'settings_extensions_' );

			$extension_settings_array = array();
			foreach ( $extension_settings as $extension_setting ) {
				$name = substr( $extension_setting->option_name, $extension_offset );
				$value = $extension_setting->option_value;

				$extension_settings_array[ $name ] = $value;

				delete_option( $extension_setting->option_name );
			}

			update_option( $this->get_prefix() . 'extension_settings', $extension_settings_array );
		}

		$spam_protection_settings = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name LIKE %s", $wpdb->esc_like( $this->get_prefix() . 'settings_spam_protection_' ) . '%' ) );
		$selectedmembers_settings = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name LIKE %s", $wpdb->esc_like( $this->get_prefix() . 'settings_selectedmembers_' ) . '%' ) );
		if ( ! empty( $spam_protection_settings ) || ! empty( $selectedmembers_settings ) ) {
			$form_settings_array = array();

			if ( ! empty( $spam_protection_settings ) ) {
				$spam_protection_offset = strlen( $this->get_prefix() . 'settings_spam_protection_' );

				foreach ( $spam_protection_settings as $spam_protection_setting ) {
					$name = substr( $spam_protection_setting->option_name, $spam_protection_offset );
					$value = $spam_protection_setting->option_value;

					$form_settings_array[ $name ] = $value;

					delete_option( $spam_protection_setting->option_name );
				}
			}

			if ( ! empty( $selectedmembers_settings ) ) {
				$selectedmembers_offset = strlen( $this->get_prefix() . 'settings_selectedmembers_' );

				foreach ( $selectedmembers_settings as $selectedmembers_setting ) {
					$name = substr( $selectedmembers_setting->option_name, $selectedmembers_offset );
					$value = $selectedmembers_setting->option_value;

					$form_settings_array[ $name ] = $value;

					delete_option( $selectedmembers_setting->option_name );
				}
			}

			update_option( $this->get_prefix() . 'module_form_settings', $form_settings_array );
		}

		// TODO: Migrate attachments with 'torro-forms-upload' status to the new attachment taxonomy term

		// If forms exist, their data needs to be migrated on-the-fly later.
		$form_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", $this->get_prefix() . 'form' ) );
		if ( empty( $form_ids ) ) {
			$wpdb->query( "DROP TABLE IF EXISTS `$participants`" );
			$wpdb->query( "DROP TABLE IF EXISTS `$email_notifications`" );
			return;
		}

		// Set flags to indicate that form meta still need to be migrated.
		$insert_flags = array();
		foreach ( $form_ids as $form_id ) {
			$insert_flags[] = $wpdb->prepare( "( %d, %s, %s)", (int) $form_id, $this->get_prefix() . 'legacy_needs_migration', 'true' );
		}
		$wpdb->query( "INSERT INTO $wpdb->postmeta ( post_id, meta_key, meta_value ) VALUES " . implode( ', ', $insert_flags ) );
		foreach ( $form_ids as $form_id ) {
			wp_cache_delete( (int) $form_id, 'post_meta' );
		}

		// If participants exist as well, this data will be needed for form meta migration.
		$participant_ids = $wpdb->get_col( "SELECT ID FROM $participants WHERE 1=1 LIMIT 1" );
		if ( empty( $participant_ids ) ) {
			$wpdb->query( "DROP TABLE IF EXISTS `$participants`" );
		} else {
			// Set a flag that the old participants table still exists.
			update_option( $this->get_prefix() . 'legacy_participants_table_installed', 'true' );
		}

		// If email notifications exist as well, this data will be needed for form meta migration.
		$email_notification_ids = $wpdb->get_col( "SELECT ID FROM $email_notifications WHERE 1=1 LIMIT 1" );
		if ( empty( $email_notification_ids ) ) {
			$wpdb->query( "DROP TABLE IF EXISTS `$email_notifications`" );
		} else {
			// Set a flag that the old email notifications table still exists.
			update_option( $this->get_prefix() . 'legacy_email_notifications_table_installed', 'true' );
		}

		// TODO: Migrate form metadata.
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
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $table_name Unprefixed table name.
	 * @return string Full table name.
	 */
	protected function get_full_table_name( $table_name ) {
		global $wpdb;

		return $wpdb->prefix . $this->get_prefix() . $table_name;
	}
}
