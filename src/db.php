<?php
/**
 * Database abstraction class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms;

use Leaves_And_Love\Plugin_Lib\DB as DB_Base;
use Leaves_And_Love\Plugin_Lib\Options;
use awsmug\Torro_Forms\Components\Legacy_Upgrades;
use awsmug\Torro_Forms\Translations\Translations_DB;

/**
 * Class for database handling.
 *
 * @since 1.0.0
 */
class DB extends DB_Base {

	/**
	 * Constructor.
	 *
	 * This sets the table prefix and adds the tables.
	 *
	 * @since 1.0.0
	 *
	 * @param string          $prefix       The prefix for all database tables.
	 * @param array           $services     {
	 *     Array of service instances.
	 *
	 *     @type Options       $options       The Option API class instance.
	 *     @type Error_Handler $error_handler The error handler instance.
	 * }
	 * @param Translations_DB $translations Translations instance.
	 */
	public function __construct( $prefix, $services, $translations ) {
		parent::__construct( $prefix, $services, $translations );

		$this->options()->store_in_network( 'rewrite_rules' );
	}

	/**
	 * Checks whether the database tables are up to date for the current site.
	 *
	 * If outdated, the tables will be refreshed.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $force Optional. Whether to force install regardless of the check. Default false.
	 */
	public function check( $force = false ) {
		// If the db_version option is set for the site and contains a semantic version number, it is a legacy version.
		$db_version = get_option( $this->get_prefix() . 'db_version' );
		if ( is_string( $db_version ) && false !== strpos( $db_version, '.' ) ) {
			$legacy_upgrades = new Legacy_Upgrades( $this->get_prefix() );
			$legacy_upgrades->upgrade( $db_version );

			delete_option( $this->get_prefix() . 'db_version' );
		}

		parent::check( $force );
	}

	/**
	 * Uninstalls the database tables.
	 *
	 * This method is called on plugin deletion.
	 *
	 * On a Multisite/Multinetwork installation, this method ensures that the database tables
	 * for all sites in the entire setup are wiped out.
	 *
	 * @since 1.0.0
	 */
	public function uninstall() {
		parent::uninstall();

		$this->options()->flush( 'rewrite_rules' );
	}

	/**
	 * Uninstalls the database tables for the current site.
	 *
	 * @since 1.0.0
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 */
	protected function uninstall_single() {
		global $wpdb;

		// Only drop database tables if a hard uninstall should be run.
		$options = $this->options()->get( 'general_settings', array() );
		if ( empty( $options['hard_uninstall'] ) ) {
			return;
		}

		$this->options()->delete( 'general_settings' );
		$this->options()->delete( 'extension_settings' );

		$modules = array_keys( torro()->modules()->get_all() );
		foreach ( $modules as $module ) {
			$this->options()->delete( 'module_' . $module );
		}

		$form_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", $this->get_prefix() . 'form' ) ); // WPCS: DB call OK. Cache OK.
		foreach ( $form_ids as $form_id ) {
			wp_delete_post( $form_id, true );
		}

		$form_category_ids = $wpdb->get_col( $wpdb->prepare( "SELECT term_id FROM $wpdb->term_taxonomy WHERE taxonomy = %s", $this->get_prefix() . 'form_category' ) ); // WPCS: DB call OK. Cache OK.
		foreach ( $form_category_ids as $form_category_id ) {
			wp_delete_term( $form_category_id, $this->get_prefix() . 'form_category' );
		}

		parent::uninstall_single();
	}

	/**
	 * Flushes rewrite rules if necessary.
	 *
	 * This ensures the post type and taxonomy rewrites work properly.
	 *
	 * @since 1.0.0
	 */
	protected function maybe_flush_rewrite_rules() {
		if ( $this->options()->get( 'rewrite_rules', false ) ) {
			return;
		}

		flush_rewrite_rules();

		$this->options()->update( 'rewrite_rules', true );
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * This method must be implemented and then be called from the constructor.
	 *
	 * @since 1.0.0
	 */
	protected function setup_hooks() {
		parent::setup_hooks();

		$this->actions[] = array(
			'name'     => 'admin_init',
			'callback' => array( $this, 'maybe_flush_rewrite_rules' ),
			'priority' => 10,
			'num_args' => 0,
		);
	}
}
