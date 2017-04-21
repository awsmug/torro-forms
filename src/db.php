<?php
/**
 * Database abstraction class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms;

use Leaves_And_Love\Plugin_Lib\DB as DB_Base;

/**
 * Class for database handling.
 *
 * @since 1.0.0
 */
class DB extends DB_Base {

	/**
	 * Checks whether the database tables are up to date for the current site.
	 *
	 * If outdated, the tables will be refreshed.
	 *
	 * @since 1.0.0
	 * @access public
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
	 * Uninstalls the database tables for the current site.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function uninstall_single() {
		//TODO: Only drop tables if hard_uninstall option is enabled

		foreach ( $this->tables as $prefixed_table_name ) {
			$db_table_name = $this->table_to_db_table( $prefixed_table_name );
			$this->wpdb->query( "DROP TABLE $db_table_name" );
		}
	}
}
