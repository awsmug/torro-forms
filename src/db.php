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
	 * @access public
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
	 * @access public
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
	 * @access public
	 */
	public function uninstall() {
		parent::uninstall();

		$this->options()->flush( 'rewrite_rules' );
	}

	/**
	 * Uninstalls the database tables for the current site.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function uninstall_single() {
		// Only drop database tables if a hard uninstall should be run.
		$options = $this->options()->get( 'general_settings', array() );
		if ( empty( $options['hard_uninstall'] ) ) {
			return;
		}

		parent::uninstall_single();

		// TODO: Delete form posts and form category terms.
		$this->options->delete( 'general_settings' );
		$this->options->delete( 'extension_settings' );
	}

	/**
	 * Flushes rewrite rules if necessary.
	 *
	 * This ensures the post type and taxonomy rewrites work properly.
	 *
	 * @since 1.0.0
	 * @access protected
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
	 * @access protected
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
