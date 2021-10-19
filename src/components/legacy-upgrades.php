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
	 *
	 * @param string $prefix Instance prefix.
	 */
	public function __construct( $prefix ) {
		$this->set_prefix( $prefix );
	}

	/**
	 * Upgrades legacy settings to new schema.
	 *
	 * @since 1.0.0
	 */
	public function upgrade_legacy_settings() {
		$prefix = $this->get_prefix();

		$general_mappings = array(
			'modules'        => array( $prefix . 'settings_general_modules', 'modules' ),
			'slug'           => array( $prefix . 'settings_general_slug', 'string' ),
			'frontend_css'   => array( $prefix . 'settings_general_frontend_css', 'bool' ),
			'hard_uninstall' => array( $prefix . 'settings_general_hard_uninstall', 'bool' ),
		);

		$general = get_option( $prefix . 'general_settings', array() );

		foreach ( $general_mappings as $option => $mapping_data ) {
			// New values already set take precedence.
			if ( isset( $general[ $option ] ) ) {
				continue;
			}

			$old = get_option( $mapping_data[0] );
			if ( false === $old ) {
				continue;
			}

			switch ( $mapping_data[1] ) {
				case 'bool':
					$new = ! empty( $old ) && 'no' !== strtolower( $old ) ? true : false;
					break;
				case 'modules':
					$old = (array) $old;
					$new = array();
					if ( in_array( 'actions', $old, true ) ) {
						$new[] = 'actions';
					}
					if ( in_array( 'results', $old, true ) ) {
						$new[] = 'evaluators';
					}
					if ( in_array( 'form-settings', $old, true ) ) {
						$new[] = 'form_settings';
						$new[] = 'access_controls';
						$new[] = 'protectors';
					}
					break;
				case 'string':
				default:
					$new = $old;
			}

			$general[ $option ] = $new;
		}

		if ( ! empty( $general ) ) {
			update_option( $prefix . 'general_settings', $general );
		}

		$mappings = array(
			'access_controls' => array(
				'members' => array(
					'invitation_from_name'      => array( $prefix . 'settings_visitors_selectedmembers_invite_from_name', 'string' ),
					'invitation_from_email'     => array( $prefix . 'settings_visitors_selectedmembers_invite_from', 'string' ),
					'invitation_from_subject'   => array( $prefix . 'settings_visitors_selectedmembers_invite_from_subject', 'string' ),
					'invitation_from_message'   => array( $prefix . 'settings_visitors_selectedmembers_invite_from_text', 'string' ),
					'reinvitation_from_name'    => array( $prefix . 'settings_visitors_selectedmembers_reinvite_from_name', 'string' ),
					'reinvitation_from_email'   => array( $prefix . 'settings_visitors_selectedmembers_reinvite_from', 'string' ),
					'reinvitation_from_subject' => array( $prefix . 'settings_visitors_selectedmembers_reinvite_from_subject', 'string' ),
					'reinvitation_from_message' => array( $prefix . 'settings_visitors_selectedmembers_reinvite_from_text', 'string' ),
				),
			),
			'protectors'      => array(
				'recaptcha' => array(
					'site_key'   => array( $prefix . 'settings_form_settings_spam_protection_recaptcha_sitekey', 'string' ),
					'secret_key' => array( $prefix . 'settings_form_settings_spam_protection_recaptcha_secret', 'string' ),
				),
			),
		);

		foreach ( $mappings as $module => $module_mappings ) {
			$settings = get_option( $prefix . 'module_' . $module, array() );

			foreach ( $module_mappings as $submodule => $submodule_mappings ) {
				$option_prefix = ! empty( $submodule ) ? $submodule . '__' : '';

				foreach ( $submodule_mappings as $option => $mapping_data ) {
					// New values already set take precedence.
					if ( isset( $settings[ $option_prefix . $option ] ) ) {
						continue;
					}

					$old = get_option( $mapping_data[0] );
					if ( false === $old ) {
						continue;
					}

					switch ( $mapping_data[1] ) {
						case 'string':
						default:
							$new = $old;
					}

					$settings[ $option_prefix . $option ] = $new;
				}
			}

			if ( ! empty( $settings ) ) {
				update_option( $prefix . 'module_' . $module, $settings );
			}
		}
	}

	/**
	 * Upgrades legacy form metadata to new schema.
	 *
	 * @since 1.0.0
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param int $form_id ID of the form for which to migrate data.
	 */
	public function upgrade_legacy_form_meta( $form_id ) {
		global $wpdb;

		$prefix = $this->get_prefix();

		$participants = $this->get_full_table_name( 'participants' );
		$email_notifications = $this->get_full_table_name( 'email_notifications' );

		$mappings = array(
			'access_controls' => array(
				'user_identification' => array(
					'prevent_multiple_submissions' => array( array( 'form_access_controls_allmembers_same_users', 'form_access_controls_selectedmembers_same_users' ), 'bool' ),
					'identification_modes'         => array(
						'ip_address' => 'form_access_controls_check_ip',
						'cookie'     => 'form_access_controls_check_cookie',
					),
					'already_submitted_message'    => array( 'already_entered_text', 'string' ),
				),
				'members'               => array(
					'allowed_users'          => 'PARTICIPANTS',
					'login_required_message' => array( 'to_be_logged_in_text', 'string' ),
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
					'type' => array( 'redirect_type', 'string' ),
					'page' => array( 'redirect_page', 'string' ),
					'url'  => array( 'redirect_url', 'string' ),
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

		$skip_enabled = array(
			'access_controls-user_identification',
			'access_controls-timerange',
			'actions-email_notifications',
			'actions-redirection',
			'protectors-honeypot',
			'protectors-linkcount',
			'protectors-recaptcha',
			'protectors-timetrap',
		);

		foreach ( $mappings as $module => $module_mappings ) {
			$metadata = get_post_meta( $form_id, $prefix . 'module_' . $module, true );
			if ( ! is_array( $metadata ) ) {
				$metadata = array();
			}

			foreach ( $module_mappings as $submodule => $submodule_mappings ) {
				$submodule_data_found = false;

				$form_option_prefix = ! empty( $submodule ) ? $submodule . '__' : '';

				foreach ( $submodule_mappings as $form_option => $mapping_data ) {
					if ( 'PARTICIPANTS' === $mapping_data ) {
						if ( get_option( $prefix . 'legacy_participants_table_installed' ) === 'true' ) {
							$user_ids = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT user_id FROM $participants WHERE form_id = %d", $form_id ) );
							if ( ! empty( $user_ids ) ) {
								$submodule_data_found = true;

								// New values already set take precedence.
								if ( ! isset( $metadata[ $form_option_prefix . $form_option ] ) ) {
									$metadata[ $form_option_prefix . $form_option ] = $user_ids;
								}
							}
						}
						continue;
					}

					if ( 'EMAIL_NOTIFICATIONS' === $mapping_data ) {
						if ( get_option( $prefix . 'legacy_email_notifications_table_installed' ) === 'true' ) {
							$notifications = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $email_notifications WHERE form_id = %d", $form_id ) );
							if ( ! empty( $notifications ) ) {
								$submodule_data_found = true;

								// New values already set take precedence.
								if ( ! isset( $metadata[ $form_option_prefix . $form_option ] ) ) {
									$metadata[ $form_option_prefix . $form_option ] = array();
									foreach ( $notifications as $notification ) {
										$metadata[ $form_option_prefix . $form_option ][] = array(
											'from_name'   => $notification->from_name,
											'from_email'  => $notification->from_email,
											'reply_email' => $notification->reply_email,
											'to_email'    => $notification->to_email,
											'subject'     => $notification->subject,
											'message'     => wpautop( $notification->message ),
										);
									}
								}
							}
						}
						continue;
					}

					if ( ! isset( $mapping_data[0] ) ) {
						$new = array();
						foreach ( $mapping_data as $group_value => $old_form_option ) {
							$old = get_post_meta( $form_id, $old_form_option, true );
							if ( ! empty( $old ) && 'no' !== strtolower( $old ) ) {
								$new[] = $group_value;
							}
						}

						$metadata[ $form_option_prefix . $form_option ] = $new;
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
					if ( isset( $metadata[ $form_option_prefix . $form_option ] ) ) {
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

					$metadata[ $form_option_prefix . $form_option ] = $new;
				}

				if ( ! empty( $form_option_prefix ) && ! in_array( $module . '-' . $submodule, $skip_enabled, true ) && $submodule_data_found ) {
					$metadata[ $form_option_prefix . 'enabled' ] = true;
				}
			}

			if ( ! empty( $metadata ) ) {
				update_post_meta( $form_id, $prefix . 'module_' . $module, $metadata );
			}
		}
	}

	/**
	 * Upgrades legacy form metadata to new schema if necessary.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id ID of the form for which to migrate data.
	 * @return bool True if form metadata was migrated, false if it had already been
	 *              migrated before.
	 */
	public function maybe_upgrade_legacy_form_meta( $form_id ) {
		if ( get_post_meta( $form_id, $this->get_prefix() . 'legacy_needs_migration', true ) !== 'true' ) {
			return false;
		}

		$this->upgrade_legacy_form_meta( $form_id );

		delete_post_meta( $form_id, $this->get_prefix() . 'legacy_needs_migration' );

		return true;
	}

	/**
	 * Upgrades legacy form attachment statuses to new attachment taxonomy term if necessary.
	 *
	 * This method will migrate a maximum of 50 attachments at a time, so it may not necessarily
	 * perform the full migration. It will only delete the flag once the last attachment has been
	 * migrated.
	 *
	 * @since 1.0.0
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return bool True if form attachments were migrated, false if they had already been
	 *              migrated before.
	 */
	public function maybe_upgrade_legacy_form_attachment_statuses() {
		global $wpdb;

		if ( get_option( $this->get_prefix() . 'legacy_attachments_need_migration' ) !== 'true' ) {
			return false;
		}

		$general = get_option( $this->get_prefix() . 'general_settings', array() );

		// If no term is set, migration is not possible.
		if ( empty( $general['attachment_taxonomy_term_id'] ) ) {
			return true;
		}

		$taxonomy_slug = torro()->taxonomies()->get_attachment_taxonomy_slug();

		// If no taxonomy is available, migration is not possible.
		if ( empty( $taxonomy_slug ) ) {
			return true;
		}

		$form_attachment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s AND post_status = %s LIMIT 50", 'attachment', 'torro-forms-upload' ) );
		foreach ( $form_attachment_ids as $form_attachment_id ) {
			$result = wp_set_post_terms( $form_attachment_id, array( $general['attachment_taxonomy_term_id'] ), $taxonomy_slug, true );
			if ( is_array( $result ) ) {
				wp_update_post( array(
					'ID'          => $form_attachment_id,
					'post_status' => 'inherit',
				) );
			}
		}

		$form_attachment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s AND post_status = %s LIMIT 1", 'attachment', 'torro-forms-upload' ) );
		if ( empty( $form_attachment_ids ) ) {
			delete_option( $this->get_prefix() . 'legacy_attachments_need_migration' );
		}

		return true;
	}

	/**
	 * Runs the upgrade from a legacy version.
	 *
	 * @since 1.0.0
	 *
	 * @param string $legacy_db_version The legacy version number.
	 *
	 * @codeCoverageIgnore
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
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @codeCoverageIgnore
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
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @codeCoverageIgnore
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
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @codeCoverageIgnore
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
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @codeCoverageIgnore
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
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @codeCoverageIgnore
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
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @codeCoverageIgnore
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
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @codeCoverageIgnore
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
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 */
	protected function upgrade_to_1_0_10() {
		global $wpdb;

		$containers          = $this->get_full_table_name( 'containers' );
		$elements            = $this->get_full_table_name( 'elements' );
		$element_answers     = $this->get_full_table_name( 'element_answers' );
		$element_choices     = $this->get_full_table_name( 'element_choices' );
		$element_settings    = $this->get_full_table_name( 'element_settings' );
		$results             = $this->get_full_table_name( 'results' );
		$submissions         = $this->get_full_table_name( 'submissions' );
		$result_values       = $this->get_full_table_name( 'result_values' );
		$submission_values   = $this->get_full_table_name( 'submission_values' );
		$participants        = $this->get_full_table_name( 'participants' );
		$email_notifications = $this->get_full_table_name( 'email_notifications' );

		$wpdb->query( "ALTER TABLE $containers CHANGE sort sort int(11) unsigned NOT NULL default '0'" );
		$wpdb->query( "ALTER TABLE $containers ADD KEY form_id (form_id)" );
		$wpdb->query( "ALTER TABLE $elements CHANGE sort sort int(11) unsigned NOT NULL default '0'" );
		$wpdb->query( "ALTER TABLE $elements ADD KEY container_id (container_id)" );
		$wpdb->query( "ALTER TABLE $elements ADD KEY type (type)" );
		$wpdb->query( "ALTER TABLE $elements ADD KEY type_container_id (type,container_id)" );
		$wpdb->query( "ALTER TABLE $element_answers RENAME TO $element_choices" );
		$wpdb->query( "ALTER TABLE $element_choices CHANGE answer value text NOT NULL" );
		$wpdb->query( "ALTER TABLE $element_choices CHANGE sort sort int(11) unsigned NOT NULL default '0'" );
		$wpdb->query( "ALTER TABLE $element_choices ADD field char(100) NOT NULL default '_main' AFTER element_id" );
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

		$this->upgrade_legacy_settings();

		$form_attachment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s AND post_status = %s LIMIT 1", 'attachment', 'torro-forms-upload' ) );
		if ( ! empty( $form_attachment_ids ) ) {
			// Set a flag to indicate that some form attachments need to have their old status migrated to a taxonomy term.
			update_option( $this->get_prefix() . 'legacy_attachments_need_migration', 'true' );
		}

		// Set a flag that the old participants table still exists.
		update_option( $this->get_prefix() . 'legacy_participants_table_installed', 'true' );

		// Set a flag that the old email notifications table still exists.
		update_option( $this->get_prefix() . 'legacy_email_notifications_table_installed', 'true' );

		// If forms exist, their data needs to be migrated on-the-fly later.
		$form_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", $this->get_prefix() . 'form' ) );
		if ( empty( $form_ids ) ) {
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
	}

	/**
	 * Updates the legacy version number.
	 *
	 * @since 1.0.0
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
