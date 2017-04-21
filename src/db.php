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
	 * Installs the database tables for the current site.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function install_single() {
		//TODO: handle legacy upgrades

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$queries = $this->replace_table_placeholders( $this->schema );

		dbDelta( $queries );
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
