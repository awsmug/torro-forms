<?php
/**
 * Form CLI command class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Forms;

use Leaves_And_Love\Plugin_Lib\DB_Objects\CLI_Models_Command;

/**
 * Class to access forms via WP-CLI.
 *
 * @since 1.0.0
 */
class CLI_Forms_Command extends CLI_Models_Command {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Manager $manager The manager instance.
	 */
	public function __construct( $manager ) {
		parent::__construct( $manager );

		$this->obj_fields = array( 'id', 'title', 'slug', 'author', 'status', 'timestamp', 'timestamp_modified' );
	}
}
